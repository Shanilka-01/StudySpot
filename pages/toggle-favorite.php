<?php
/**
 * Add / remove a favourite, then go back to the page the user came from.
 * POST only - it prints nothing, it just changes the database and redirects.
 */
require_once __DIR__ . '/../includes/functions.php';
require_login();

$placeId = (int)($_POST['place_id'] ?? 0);

// "back" is a path inside this site, e.g. pages/place.php?id=3
$back = ltrim($_POST['back'] ?? 'pages/explore.php', '/');
if (preg_match('#^[a-z][a-z0-9+.-]*:|^//|\.\.#i', $back)) {
    $back = 'pages/explore.php';        // never redirect to another site
}

if ($placeId > 0) {
    if (is_favorite($pdo, $placeId)) {
        $pdo->prepare('DELETE FROM favorites WHERE user_id = ? AND place_id = ?')
            ->execute([current_user_id(), $placeId]);
        set_flash('success', 'Removed from your favourites.');
    } else {
        $pdo->prepare('INSERT IGNORE INTO favorites (user_id, place_id) VALUES (?,?)')
            ->execute([current_user_id(), $placeId]);
        set_flash('success', 'Saved to your favourites.');
    }
}

redirect($back);
