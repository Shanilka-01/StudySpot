<?php
/**
 * Profile dashboard - sidebar + favourites, reviews, recently viewed, settings.
 * ?view=overview | favorites | reviews | recent | settings
 */
require_once __DIR__ . '/includes/functions.php';
require_login();

$view   = $_GET['view'] ?? 'overview';
$userId = current_user_id();

// ---- account settings form ----
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_profile') {
    $name  = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($name === '')                               $errors[] = 'Your name cannot be empty.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';

    if (!$errors) {
        $taken = $pdo->prepare('SELECT 1 FROM users WHERE email = ? AND id <> ?');
        $taken->execute([$email, $userId]);
        if ($taken->fetchColumn()) {
            $errors[] = 'Another account already uses that email.';
        } else {
            $pdo->prepare('UPDATE users SET full_name = ?, email = ? WHERE id = ?')
                ->execute([$name, $email, $userId]);
            $_SESSION['full_name'] = $name;
            $_SESSION['email']     = $email;
            set_flash('success', 'Your details are saved.');
            header('Location: profile.php?view=settings');
            exit;
        }
    }
}

// ---- data for the panels ----
$fav = $pdo->prepare(
    'SELECT p.*, COALESCE(AVG(r.rating),0) AS avg_rating, COUNT(r.id) AS review_count
     FROM favorites f JOIN places p ON p.id = f.place_id
     LEFT JOIN reviews r ON r.place_id = p.id
     WHERE f.user_id = ? GROUP BY p.id ORDER BY f.created_at DESC'
);
$fav->execute([$userId]);
$favorites = $fav->fetchAll();

$rev = $pdo->prepare(
    'SELECT r.*, p.name, p.id AS place_id FROM reviews r
     JOIN places p ON p.id = r.place_id
     WHERE r.user_id = ? ORDER BY r.created_at DESC'
);
$rev->execute([$userId]);
$myReviews = $rev->fetchAll();

$rec = $pdo->prepare(
    'SELECT p.*, v.viewed_at FROM recently_viewed v
     JOIN places p ON p.id = v.place_id
     WHERE v.user_id = ? ORDER BY v.viewed_at DESC LIMIT 8'
);
$rec->execute([$userId]);
$recent = $rec->fetchAll();

$page_title = 'My Profile';
require __DIR__ . '/includes/header.php';

$menu = [
    'overview'  => ['Profile Overview', '&#128100;'],
    'favorites' => ['My Favorites', '&#9825;'],
    'reviews'   => ['My Reviews', '&#128221;'],
    'recent'    => ['Recently Viewed', '&#128336;'],
    'settings'  => ['Account Settings', '&#9881;'],
];
?>

<div class="profile">
    <aside class="side">
        <div class="side__user">
            <div class="avatar"><?= e(strtoupper(substr($_SESSION['full_name'], 0, 1))) ?></div>
            <h3 style="margin:0;"><?= e($_SESSION['full_name']) ?></h3>
            <p class="muted" style="margin:2px 0 0;"><?= e($_SESSION['email']) ?></p>
        </div>

        <?php foreach ($menu as $key => [$label, $icon]): ?>
            <a href="profile.php?view=<?= $key ?>" class="<?= $view === $key ? 'is-active' : '' ?>">
                <span><?= $icon ?></span> <?= $label ?>
            </a>
        <?php endforeach; ?>
        <a href="my-bookings.php"><span>&#128197;</span> My Bookings</a>
        <a href="logout.php"><span>&#8631;</span> Logout</a>
    </aside>

    <section class="profile__body">

        <?php if ($view === 'overview' || $view === 'favorites'): ?>
            <div class="section-head" style="margin-top:0;">
                <h2>My Favorites</h2>
                <a href="favorites.php">View All</a>
            </div>
            <p class="muted"><?= count($favorites) ?> saved places</p>

            <div class="grid-4" style="margin-top:14px;">
                <?php foreach (array_slice($favorites, 0, $view === 'favorites' ? 100 : 4) as $p): ?>
                    <a class="place-card" href="place.php?id=<?= (int)$p['id'] ?>">
                        <img src="<?= e(place_image($p['cover_image'])) ?>" alt="">
                        <div class="place-card__body">
                            <h3><?= e($p['name']) ?></h3>
                            <p class="place-card__city"><?= e($p['city']) ?></p>
                            <div class="place-card__meta">
                                <span class="rating"><span class="star">&#9733;</span>
                                    <b><?= number_format((float)$p['avg_rating'], 1) ?></b>
                                    <small>(<?= (int)$p['review_count'] ?>)</small></span>
                            </div>
                            <p style="color:var(--green-700);font-weight:700;margin:8px 0 0;"><?= e($p['cost_label']) ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php if (!$favorites): ?>
                <p class="muted">Nothing saved yet. <a href="explore.php" style="color:var(--green-700);">Find a study space</a>.</p>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($view === 'overview' || $view === 'reviews'): ?>
            <div class="section-head">
                <h2>My Reviews</h2>
                <a href="profile.php?view=reviews">View All</a>
            </div>

            <?php foreach (array_slice($myReviews, 0, $view === 'reviews' ? 100 : 3) as $r): ?>
                <article class="review">
                    <div class="review__head">
                        <span class="avatar" style="width:32px;height:32px;font-size:14px;"><?= e(strtoupper(substr($_SESSION['full_name'], 0, 1))) ?></span>
                        <b><a href="place.php?id=<?= (int)$r['place_id'] ?>"><?= e($r['name']) ?></a></b>
                        <span><?= stars((float)$r['rating']) ?></span>
                        <span class="muted"><?= e(time_ago($r['created_at'])) ?></span>
                    </div>
                    <p style="margin:0;"><?= e($r['comment']) ?></p>
                </article>
            <?php endforeach; ?>
            <?php if (!$myReviews): ?>
                <p class="muted">You have not reviewed a place yet.</p>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($view === 'recent'): ?>
            <h2>Recently Viewed</h2>
            <div class="grid-4" style="margin-top:14px;">
                <?php foreach ($recent as $p) { render_place_card($pdo, $p); } ?>
            </div>
            <?php if (!$recent): ?>
                <p class="muted">Pages you open will be listed here.</p>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($view === 'settings'): ?>
            <h2>Account Settings</h2>
            <?php foreach ($errors as $error): ?>
                <p class="error"><?= e($error) ?></p>
            <?php endforeach; ?>

            <form method="post" action="profile.php?view=settings" class="card card__pad" style="max-width:520px;">
                <input type="hidden" name="action" value="update_profile">
                <div class="field">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" value="<?= e($_SESSION['full_name']) ?>" required>
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= e($_SESSION['email']) ?>" required>
                </div>
                <button class="btn" type="submit">Save changes</button>
            </form>
        <?php endif; ?>

    </section>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
