<?php
/**
 * Home / landing page
 */
require_once __DIR__ . '/includes/functions.php';

// Four highest rated places for the "Popular Study Spaces" row
$popular = $pdo->query(
    'SELECT p.*, COALESCE(AVG(r.rating),0) AS avg_rating
     FROM places p LEFT JOIN reviews r ON r.place_id = p.id
     GROUP BY p.id ORDER BY avg_rating DESC, p.id ASC LIMIT 4'
)->fetchAll();

$page_title = 'Find the Best Study Spots Near You';
$active     = 'home';
require __DIR__ . '/includes/header.php';
?>

<section class="container hero">
    <div>
        <h1>Find the Best Study Spots Near You</h1>
        <p class="lead">Discover quiet libraries, cozy cafés, co-working spaces and university areas
            to help you focus and get more done.</p>

        <form class="hero__search" action="explore.php" method="get">
            <input type="search" name="q" placeholder="Search study spaces, e.g. library, cafe...">
            <button class="btn" type="submit">Search</button>
            <button class="btn btn--ghost" type="button" id="useLocation">&#10148; Use My Location</button>
        </form>

        <div class="chips">
            <a class="chip" href="explore.php?type[]=library">&#128218; Libraries</a>
            <a class="chip" href="explore.php?type[]=cafe">&#9749; Cafés</a>
            <a class="chip" href="explore.php?type[]=coworking">&#128188; Co-working Spaces</a>
            <a class="chip" href="explore.php?type[]=university">&#127891; University Areas</a>
        </div>
    </div>

    <div class="hero__art">
        <img src="assets/img/hero.jpg" alt="A student studying at a desk by a window">
    </div>
</section>

<section class="container">
    <div class="section-head">
        <h2>Popular Study Spaces</h2>
        <a href="explore.php">View All &rarr;</a>
    </div>

    <div class="grid-4">
        <?php foreach ($popular as $place) { render_place_card($pdo, $place); } ?>
    </div>
</section>

<section class="container">
    <div class="section-head"><h2>How StudySpot works</h2></div>
    <div class="about-grid">
        <div class="card card__pad">
            <h3>1. Search</h3>
            <p class="muted">Filter by distance, noise level, Wi-Fi and cost until you find a place that suits the way you study.</p>
        </div>
        <div class="card card__pad">
            <h3>2. Book a seat</h3>
            <p class="muted">Pick a date, a time slot and how many people are coming. Free places can be reserved too.</p>
        </div>
        <div class="card card__pad">
            <h3>3. Leave a review</h3>
            <p class="muted">Tell other students how quiet it really was, how the Wi-Fi held up and whether it was worth the money.</p>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
