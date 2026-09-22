<?php
/**
 * About Us
 */
require_once __DIR__ . '/includes/functions.php';

$placeCount  = (int)$pdo->query('SELECT COUNT(*) FROM places')->fetchColumn();
$reviewCount = (int)$pdo->query('SELECT COUNT(*) FROM reviews')->fetchColumn();
$cityCount   = (int)$pdo->query('SELECT COUNT(DISTINCT city) FROM places')->fetchColumn();

$page_title = 'About Us';
$active     = 'about';
require __DIR__ . '/includes/header.php';
?>

<div class="container page">
    <h1>About StudySpot</h1>
    <p style="max-width:70ch;font-size:17px;">
        Finding somewhere quiet to study in Sri Lanka usually means asking around and hoping for the best.
        StudySpot collects libraries, cafés, co-working spaces and university areas in one place, with the
        details students actually care about: how quiet it is, whether the Wi-Fi holds up, what it costs and
        when it closes.
    </p>

    <div class="about-grid">
        <div class="card card__pad center">
            <h2><?= $placeCount ?></h2>
            <p class="muted">study spaces listed</p>
        </div>
        <div class="card card__pad center">
            <h2><?= $reviewCount ?></h2>
            <p class="muted">student reviews</p>
        </div>
        <div class="card card__pad center">
            <h2><?= $cityCount ?></h2>
            <p class="muted">cities covered</p>
        </div>
    </div>

    <h2 style="margin-top:40px;">The team</h2>
    <p class="muted">Built as a Web Architecture group project.</p>
    <div class="about-grid">
        <div class="card card__pad"><h3>Member 1</h3><p class="muted">Home page, layout, shared CSS</p></div>
        <div class="card card__pad"><h3>Member 2</h3><p class="muted">Explore, filters, map</p></div>
        <div class="card card__pad"><h3>Member 3</h3><p class="muted">Login, register, profile</p></div>
        <div class="card card__pad"><h3>Member 4</h3><p class="muted">Booking, payment, confirmation</p></div>
        <div class="card card__pad"><h3>Member 5</h3><p class="muted">Reviews, favourites, help pages</p></div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
