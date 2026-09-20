<?php
/**
 * My Favourites
 */
require_once __DIR__ . '/includes/functions.php';
require_login();

$stmt = $pdo->prepare(
    'SELECT p.*, COALESCE(AVG(r.rating),0) AS avg_rating, COUNT(r.id) AS review_count
     FROM favorites f
     JOIN places p ON p.id = f.place_id
     LEFT JOIN reviews r ON r.place_id = p.id
     WHERE f.user_id = ?
     GROUP BY p.id
     ORDER BY f.created_at DESC'
);
$stmt->execute([current_user_id()]);
$places = $stmt->fetchAll();

$page_title = 'My Favourites';
require __DIR__ . '/includes/header.php';
?>

<div class="container page">
    <h1>My Favourites</h1>
    <p class="muted"><?= count($places) ?> saved <?= count($places) === 1 ? 'place' : 'places' ?></p>

    <?php if (!$places): ?>
        <div class="card card__pad" style="margin-top:20px;">
            <h3>No saved places yet</h3>
            <p class="muted">Tap the heart on any study space and it will be waiting for you here.</p>
            <a class="btn btn--sm" href="explore.php">Browse study spaces</a>
        </div>
    <?php endif; ?>

    <div style="margin-top:20px;">
        <?php foreach ($places as $p): ?>
            <article class="fav-row">
                <img src="<?= e(place_image($p['cover_image'])) ?>" alt="">
                <div style="flex:1;">
                    <h3><?= e($p['name']) ?></h3>
                    <p><b><?= e($p['city']) ?></b></p>
                    <p class="rating"><span class="star">&#9733;</span>
                        <b><?= number_format((float)$p['avg_rating'], 1) ?></b>
                        <small>(<?= (int)$p['review_count'] ?>)</small>
                        &nbsp; <span class="muted"><?= e($p['cost_label']) ?></span></p>
                </div>
                <form method="post" action="actions/toggle-favorite.php">
                    <input type="hidden" name="place_id" value="<?= (int)$p['id'] ?>">
                    <input type="hidden" name="back" value="favorites.php">
                    <button class="heart" type="submit" title="Remove from favourites">&#10084;</button>
                </form>
                <a class="btn btn--outline btn--sm" href="place.php?id=<?= (int)$p['id'] ?>">View</a>
            </article>
        <?php endforeach; ?>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
