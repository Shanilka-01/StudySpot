<?php
/**
 * Single study space - photos, facts, facilities, reviews.
 */
require_once __DIR__ . '/includes/functions.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM places WHERE id = ?');
$stmt->execute([$id]);
$place = $stmt->fetch();

if (!$place) {
    http_response_code(404);
    $page_title = 'Place not found';
    require __DIR__ . '/includes/header.php';
    echo '<div class="container page"><h1>That study space does not exist</h1>
          <p class="muted">It may have been removed. <a href="explore.php">Back to Explore</a></p></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$rating = place_rating($pdo, $id);

$rev = $pdo->prepare(
    'SELECT r.*, u.full_name FROM reviews r
     JOIN users u ON u.id = r.user_id
     WHERE r.place_id = ? ORDER BY r.created_at DESC'
);
$rev->execute([$id]);
$reviews = $rev->fetchAll();

$gal = $pdo->prepare('SELECT image FROM place_images WHERE place_id = ?');
$gal->execute([$id]);
$gallery = $gal->fetchAll(PDO::FETCH_COLUMN);

// remember what the user looked at
if (is_logged_in()) {
    $pdo->prepare('INSERT INTO recently_viewed (user_id, place_id) VALUES (?,?)
                   ON DUPLICATE KEY UPDATE viewed_at = NOW()')
        ->execute([current_user_id(), $id]);
}

$saved      = is_favorite($pdo, $id);
$facilities = array_filter(array_map('trim', explode(',', (string)$place['facilities'])));

$page_title = $place['name'];
$active     = 'explore';
require __DIR__ . '/includes/header.php';
?>

<div class="container page">
    <p class="breadcrumb"><a href="index.php">Home</a> &gt; <a href="explore.php">Explore</a> &gt; <?= e($place['name']) ?></p>

    <div class="detail">
        <div>
            <div class="gallery__main">
                <img src="<?= e(place_image($place['cover_image'])) ?>" alt="<?= e($place['name']) ?>">
            </div>
            <?php if ($gallery): ?>
                <div class="gallery__thumbs">
                    <?php foreach (array_slice($gallery, 0, 5) as $img): ?>
                        <img src="<?= e(place_image($img)) ?>" alt="">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <aside class="card card__pad">
            <h2><?= e($place['name']) ?></h2>
            <p>&#128205; <?= e($place['address']) ?></p>
            <p>&#127970; <?= e(type_label($place['type'])) ?></p>
            <p class="muted"><?= e($place['distance_km']) ?> km from you</p>

            <div style="display:flex;gap:12px;margin-top:18px;flex-wrap:wrap;">
                <?php if (is_logged_in()): ?>
                    <form method="post" action="actions/toggle-favorite.php">
                        <input type="hidden" name="place_id" value="<?= $id ?>">
                        <input type="hidden" name="back" value="place.php?id=<?= $id ?>">
                        <button class="btn btn--ghost" type="submit">
                            <?= $saved ? '&#10084; Saved' : '&#9825; Save' ?>
                        </button>
                    </form>
                <?php else: ?>
                    <a class="btn btn--ghost" href="login.php">&#9825; Save</a>
                <?php endif; ?>
                <a class="btn btn--ghost" href="map.php?id=<?= $id ?>">&#128506; Show on map</a>
            </div>

            <a class="btn btn--block" style="margin-top:16px;" href="booking.php?id=<?= $id ?>">Book your spot</a>
        </aside>
    </div>

    <div class="fact-row">
        <div class="fact">
            <span>&#128336; Opening Hours</span>
            <b><?= e(hours_label($place['open_time'], $place['close_time'])) ?></b>
            <span><?= e($place['open_days']) ?></span>
        </div>
        <div class="fact">
            <span>&#128246; Wi-Fi</span>
            <b><?= e(wifi_label($place['wifi'])) ?></b>
            <span><?= e($place['wifi_note']) ?></span>
        </div>
        <div class="fact">
            <span>&#128266; Noise Level</span>
            <b><?= e(noise_label($place['noise_level'])) ?></b>
            <span><?= e($place['noise_note']) ?></span>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:280px 1fr;gap:20px;margin-top:20px;" class="detail">
        <div class="card card__pad">
            <span class="muted">&#128176; Cost</span>
            <b style="display:block;font-size:17px;color:var(--ink);margin-top:6px;"><?= e($place['cost_label']) ?></b>
            <span class="muted"><?= $place['price'] > 0 ? 'Per session' : 'No entrance fee' ?></span>
        </div>
        <div class="card card__pad">
            <h3>Facilities</h3>
            <ul class="facilities" style="padding:0;margin:0;">
                <?php foreach ($facilities as $f): ?>
                    <li>&#9989; <?= e($f) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <h3 style="margin-top:30px;">About this place</h3>
    <p style="max-width:80ch;"><?= e($place['description']) ?></p>

    <div class="section-head">
        <h2>Reviews (<?= $rating['total'] ?>)</h2>
        <a href="review.php?id=<?= $id ?>">Write a review</a>
    </div>

    <?php if (!$reviews): ?>
        <div class="card card__pad">
            <p>No reviews yet. <a href="review.php?id=<?= $id ?>" style="color:var(--green-700);font-weight:600;">Be the first to review this place.</a></p>
        </div>
    <?php endif; ?>

    <?php foreach ($reviews as $r): ?>
        <article class="review">
            <div class="review__head">
                <span class="avatar" style="width:32px;height:32px;font-size:14px;"><?= e(strtoupper(substr($r['full_name'], 0, 1))) ?></span>
                <b><?= e($r['full_name']) ?></b>
                <span><?= stars((float)$r['rating']) ?></span>
                <span class="muted"><?= e(time_ago($r['created_at'])) ?></span>
            </div>
            <p style="margin:0;"><?= e($r['comment']) ?></p>
        </article>
    <?php endforeach; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
