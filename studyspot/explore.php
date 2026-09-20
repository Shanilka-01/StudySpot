<?php
/**
 * Explore page - search, filters, results list, pagination.
 * Every filter is applied in SQL with prepared statements.
 */
require_once __DIR__ . '/includes/functions.php';

$q        = trim($_GET['q'] ?? '');
$types    = (array)($_GET['type'] ?? []);
$wifi     = $_GET['wifi'] ?? 'any';
$noises   = (array)($_GET['noise'] ?? []);
$cost     = $_GET['cost'] ?? 'any';
$distance = (float)($_GET['distance'] ?? 10);
$sort     = $_GET['sort'] ?? 'recommended';
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 4;

$where  = ['p.distance_km <= ?'];
$params = [$distance];

if ($q !== '') {
    $where[]  = '(p.name LIKE ? OR p.city LIKE ? OR p.address LIKE ? OR p.type LIKE ?)';
    $like     = '%' . $q . '%';
    array_push($params, $like, $like, $like, $like);
}
if ($types) {
    $where[] = 'p.type IN (' . implode(',', array_fill(0, count($types), '?')) . ')';
    $params  = array_merge($params, $types);
}
if ($wifi !== 'any') {
    $where[]  = 'p.wifi = ?';
    $params[] = $wifi;
}
if ($noises) {
    $where[] = 'p.noise_level IN (' . implode(',', array_fill(0, count($noises), '?')) . ')';
    $params  = array_merge($params, $noises);
}
if ($cost !== 'any') {
    $where[]  = 'p.cost_type = ?';
    $params[] = $cost;
}

$whereSql = 'WHERE ' . implode(' AND ', $where);

$orderSql = match ($sort) {
    'rating'   => 'avg_rating DESC',
    'distance' => 'p.distance_km ASC',
    'price'    => 'p.price ASC',
    default    => 'avg_rating DESC, p.distance_km ASC',
};

// total for pagination
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM places p $whereSql");
$countStmt->execute($params);
$total      = (int)$countStmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));
$page       = min($page, $totalPages);
$offset     = ($page - 1) * $perPage;

$sql = "SELECT p.*, COALESCE(AVG(r.rating),0) AS avg_rating, COUNT(r.id) AS review_count
        FROM places p LEFT JOIN reviews r ON r.place_id = p.id
        $whereSql
        GROUP BY p.id
        ORDER BY $orderSql
        LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$places = $stmt->fetchAll();

/** Keep the current filters when building a page link. */
function page_link(int $n): string
{
    $query = $_GET;
    $query['page'] = $n;
    return 'explore.php?' . http_build_query($query);
}

$page_title = 'Explore Study Spaces';
$active     = 'explore';
require __DIR__ . '/includes/header.php';
?>

<div class="container page">
    <h1>Explore Study Spaces</h1>
    <p class="muted">Find the best places to study near you</p>

    <form method="get" action="explore.php" id="filterForm" style="margin:22px 0;">
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search location, place, name or type..." style="max-width:520px;">

        <div class="explore" style="margin-top:22px;">
            <!-- ---------- filters ---------- -->
            <aside class="filters">
                <h4>Filters <a href="explore.php">Clear All</a></h4>

                <fieldset>
                    <legend>Distance</legend>
                    <input type="range" name="distance" min="1" max="10" step="1" value="<?= (int)$distance ?>" style="width:100%;accent-color:var(--green-700)">
                    <div class="muted" style="display:flex;justify-content:space-between;font-size:12px;">
                        <span>1km</span><span><?= (int)$distance ?>km</span>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Type</legend>
                    <?php foreach (['library' => 'Libraries', 'cafe' => 'Cafes', 'coworking' => 'Co-working Spaces', 'university' => 'University Areas'] as $val => $text): ?>
                        <label><input type="checkbox" name="type[]" value="<?= $val ?>" <?= in_array($val, $types, true) ? 'checked' : '' ?>> <?= $text ?></label>
                    <?php endforeach; ?>
                </fieldset>

                <fieldset>
                    <legend>Wi-Fi</legend>
                    <?php foreach (['any' => 'Any', 'free' => 'Free Wi-Fi', 'paid' => 'Paid Wi-Fi'] as $val => $text): ?>
                        <label><input type="radio" name="wifi" value="<?= $val ?>" <?= $wifi === $val ? 'checked' : '' ?>> <?= $text ?></label>
                    <?php endforeach; ?>
                </fieldset>

                <fieldset>
                    <legend>Noise Level</legend>
                    <?php foreach (['very_quiet' => 'Very Quiet', 'quiet' => 'Quiet', 'moderate' => 'Moderate', 'lively' => 'Lively'] as $val => $text): ?>
                        <label><input type="checkbox" name="noise[]" value="<?= $val ?>" <?= in_array($val, $noises, true) ? 'checked' : '' ?>> <?= $text ?></label>
                    <?php endforeach; ?>
                </fieldset>

                <fieldset>
                    <legend>Cost</legend>
                    <?php foreach (['any' => 'Any', 'free' => 'Free', '1-200' => 'LKR 1-200', '201-500' => 'LKR 201-500', '500+' => 'LKR 500+'] as $val => $text): ?>
                        <label><input type="radio" name="cost" value="<?= $val ?>" <?= $cost === $val ? 'checked' : '' ?>> <?= $text ?></label>
                    <?php endforeach; ?>
                </fieldset>

                <noscript><button class="btn btn--block btn--sm" type="submit">Apply filters</button></noscript>
            </aside>

            <!-- ---------- results ---------- -->
            <section>
                <p class="muted"><?= $total ?> results found</p>

                <?php if (!$places): ?>
                    <div class="card card__pad">
                        <h3>No study spaces match those filters</h3>
                        <p class="muted">Try widening the distance or clearing the type filters.</p>
                    </div>
                <?php endif; ?>

                <?php foreach ($places as $p): ?>
                    <article class="result-row">
                        <img src="<?= e(place_image($p['cover_image'])) ?>" alt="<?= e($p['name']) ?>">
                        <div class="result-row__info">
                            <h3><a href="place.php?id=<?= (int)$p['id'] ?>"><?= e($p['name']) ?></a></h3>
                            <p class="muted" style="margin:2px 0;"><?= e($p['city']) ?> &nbsp; <?= e($p['distance_km']) ?>km</p>
                            <div class="result-row__tags">
                                <span class="badge badge--grey"><?= e(type_label($p['type'])) ?></span>
                                <span class="badge badge--grey"><?= e(wifi_label($p['wifi'])) ?></span>
                                <span class="badge badge--grey"><?= e(noise_label($p['noise_level'])) ?></span>
                            </div>
                            <div class="result-row__foot">
                                <span class="rating"><span class="star">&#9733;</span>
                                    <b><?= number_format((float)$p['avg_rating'], 1) ?></b>
                                    <small>(<?= (int)$p['review_count'] ?>)</small></span>
                                <span><?= e($p['cost_label']) ?></span>
                                <span>&#128336; <?= e(hours_label($p['open_time'], $p['close_time'])) ?></span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>

                <?php if ($totalPages > 1): ?>
                    <nav class="pagination">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i === $page): ?>
                                <span class="is-current"><?= $i ?></span>
                            <?php else: ?>
                                <a href="<?= e(page_link($i)) ?>"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        <?php if ($page < $totalPages): ?>
                            <a href="<?= e(page_link($page + 1)) ?>">&raquo;</a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            </section>

            <!-- ---------- right column ---------- -->
            <aside>
                <div class="card card__pad" style="margin-bottom:14px;">
                    <b>Colombo, Sri Lanka</b>
                </div>
                <div class="card card__pad">
                    <label for="sort">Sort by</label>
                    <select name="sort" id="sort" onchange="this.form.submit()">
                        <option value="recommended" <?= $sort === 'recommended' ? 'selected' : '' ?>>Recommended</option>
                        <option value="rating"      <?= $sort === 'rating' ? 'selected' : '' ?>>Highest rating</option>
                        <option value="distance"    <?= $sort === 'distance' ? 'selected' : '' ?>>Nearest first</option>
                        <option value="price"       <?= $sort === 'price' ? 'selected' : '' ?>>Lowest price</option>
                    </select>
                </div>
            </aside>
        </div>
    </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
