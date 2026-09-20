<?php
/**
 * Map page - embedded map + nearby list.
 * The map is an OpenStreetMap iframe so it works without an API key.
 * If your group has a Google Maps key, swap the iframe src for the
 * Google Maps Embed URL (see README).
 */
require_once __DIR__ . '/includes/functions.php';

$q = trim($_GET['q'] ?? '');

$sql    = 'SELECT p.*, COALESCE(AVG(r.rating),0) AS avg_rating, COUNT(r.id) AS review_count
           FROM places p LEFT JOIN reviews r ON r.place_id = p.id';
$params = [];
if ($q !== '') {
    $sql     .= ' WHERE p.name LIKE ? OR p.city LIKE ?';
    $params[] = "%$q%";
    $params[] = "%$q%";
}
$sql .= ' GROUP BY p.id ORDER BY p.distance_km ASC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$places = $stmt->fetchAll();

// centre the map on the first result (or Colombo)
$centerLat = $places[0]['latitude']  ?? 6.9271;
$centerLng = $places[0]['longitude'] ?? 79.8612;
if (isset($_GET['lat'], $_GET['lng'])) {
    $centerLat = (float)$_GET['lat'];
    $centerLng = (float)$_GET['lng'];
}
$bbox = ($centerLng - 0.25) . ',' . ($centerLat - 0.2) . ',' . ($centerLng + 0.25) . ',' . ($centerLat + 0.2);

$page_title = 'Map';
$active     = 'map';
require __DIR__ . '/includes/header.php';
?>

<div class="container page">
    <form method="get" action="map.php" style="display:flex;gap:14px;margin-bottom:20px;">
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search in this area...">
        <button class="btn btn--ghost" type="submit">Filters</button>
    </form>

    <div class="map-wrap">
        <div class="map-canvas">
            <iframe
                title="Study spaces map"
                loading="lazy"
                src="https://www.openstreetmap.org/export/embed.html?bbox=<?= e($bbox) ?>&amp;layer=mapnik&amp;marker=<?= e($centerLat) ?>,<?= e($centerLng) ?>">
            </iframe>
        </div>

        <aside class="map-list">
            <h3>Nearby Study Spaces</h3>
            <?php foreach ($places as $p): ?>
                <a class="map-list__item" href="place.php?id=<?= (int)$p['id'] ?>">
                    <img src="<?= e(place_image($p['cover_image'])) ?>" alt="">
                    <div>
                        <b><?= e($p['name']) ?></b>
                        <p class="muted" style="margin:2px 0;font-size:13px;"><?= e($p['city']) ?> &middot; <?= e($p['distance_km']) ?> km</p>
                        <span class="rating" style="font-size:13px;"><span class="star">&#9733;</span>
                            <?= number_format((float)$p['avg_rating'], 1) ?> (<?= (int)$p['review_count'] ?>)</span>
                        <span class="muted" style="font-size:13px;display:block;"><?= e($p['cost_label']) ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </aside>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
