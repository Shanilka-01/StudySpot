<?php
/**
 * Add / remove a favourite, then go back to the page the user came from.
 */
require_once __DIR__ . '/../includes/functions.php';
require_login();

$placeId = (int)($_POST['place_id'] ?? 0);
$back    = $_POST['back'] ?? 'explore.php';

// only allow local redirects
if (preg_match('#^(https?:)?//#', $back)) {
    $back = 'explore.php';
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

header('Location: ../' . $back);
exit;
