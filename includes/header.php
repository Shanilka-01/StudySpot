<?php
/**
 * Top of every page.
 * A page can set these before including it:
 *   $page_title  – text for <title>
 *   $active      – which nav link is highlighted: home | explore | map | about
 *   $hide_nav    – true on the login / register pages
 */
require_once __DIR__ . '/functions.php';

$page_title = $page_title ?? SITE_NAME;
$active     = $active ?? '';
$hide_nav   = $hide_nav ?? false;
$flash      = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($page_title) ?> | <?= SITE_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <link rel="icon" type="image/png" href="<?= asset('img/logo.png') ?>">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>

<?php if (!$hide_nav): ?>
<header class="topbar">
    <div class="topbar__inner">
        <a class="logo" href="<?= url('index.php') ?>">
            <img class="logo__mark" src="<?= asset('img/logo.png') ?>" alt="" width="34" height="34">
            <span class="logo__text">Study<span>Spot</span></span>
        </a>

        <button class="nav-toggle" type="button" aria-label="Menu" onclick="document.getElementById('mainNav').classList.toggle('is-open')">&#9776;</button>

        <nav class="nav" id="mainNav">
            <a href="<?= url('index.php') ?>"   class="<?= $active === 'home' ? 'is-active' : '' ?>">Home</a>
            <a href="<?= url('pages/explore.php') ?>" class="<?= $active === 'explore' ? 'is-active' : '' ?>">Explore</a>
            <a href="<?= url('pages/map.php') ?>"     class="<?= $active === 'map' ? 'is-active' : '' ?>">Map</a>
            <a href="<?= url('pages/about.php') ?>"   class="<?= $active === 'about' ? 'is-active' : '' ?>">About Us</a>
        </nav>

        <div class="topbar__actions">
            <a href="<?= url('pages/favorites.php') ?>" class="icon-btn" title="My Favourites">&#9825;</a>
            <?php if (is_logged_in()): ?>
                <a href="<?= url('pages/profile.php') ?>" class="avatar" title="<?= e($_SESSION['full_name']) ?>">
                    <?= e(strtoupper(substr($_SESSION['full_name'], 0, 1))) ?>
                </a>
            <?php else: ?>
                <a href="<?= url('pages/login.php') ?>" class="btn btn--sm">Login</a>
            <?php endif; ?>
        </div>
    </div>
</header>
<?php endif; ?>

<?php if ($flash): ?>
<div class="flash flash--<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>

<main>
