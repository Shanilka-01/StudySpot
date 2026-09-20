<?php
/**
 * My Bookings - tabs for upcoming / completed / cancelled
 */
require_once __DIR__ . '/includes/functions.php';
require_login();

$tab   = $_GET['tab'] ?? 'upcoming';
$valid = ['upcoming', 'completed', 'cancelled'];
if (!in_array($tab, $valid, true)) {
    $tab = 'upcoming';
}

// cancel a booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    $cancel = $pdo->prepare('UPDATE bookings SET status = "cancelled" WHERE id = ? AND user_id = ? AND status = "upcoming"');
    $cancel->execute([(int)$_POST['cancel_id'], current_user_id()]);
    set_flash('success', 'Booking cancelled.');
    header('Location: my-bookings.php?tab=cancelled');
    exit;
}

// counts for the tab labels
$counts = ['upcoming' => 0, 'completed' => 0, 'cancelled' => 0];
$cstmt  = $pdo->prepare('SELECT status, COUNT(*) AS n FROM bookings WHERE user_id = ? GROUP BY status');
$cstmt->execute([current_user_id()]);
foreach ($cstmt->fetchAll() as $row) {
    if (isset($counts[$row['status']])) {
        $counts[$row['status']] = (int)$row['n'];
    }
}

$stmt = $pdo->prepare(
    'SELECT b.*, p.name, p.city, p.cover_image
     FROM bookings b JOIN places p ON p.id = b.place_id
     WHERE b.user_id = ? AND b.status = ?
     ORDER BY b.booking_date DESC'
);
$stmt->execute([current_user_id(), $tab]);
$bookings = $stmt->fetchAll();

$page_title = 'My Bookings';
require __DIR__ . '/includes/header.php';
?>

<div class="container page">
    <h1>My Bookings</h1>

    <nav class="tabs">
        <?php foreach ($valid as $t): ?>
            <a href="my-bookings.php?tab=<?= $t ?>" class="<?= $tab === $t ? 'is-active' : '' ?>">
                <?= ucfirst($t) ?> (<?= $counts[$t] ?>)
            </a>
        <?php endforeach; ?>
    </nav>

    <?php if (!$bookings): ?>
        <div class="card card__pad">
            <h3>Nothing here yet</h3>
            <p class="muted">When you book a study space it will show up on this tab.</p>
            <a class="btn btn--sm" href="explore.php">Find a study space</a>
        </div>
    <?php endif; ?>

    <?php foreach ($bookings as $b): ?>
        <article class="booking-row">
            <img src="<?= e(place_image($b['cover_image'])) ?>" alt="">
            <div class="booking-row__info">
                <h3><?= e($b['name']) ?></h3>
                <p><b><?= e($b['city']) ?></b></p>
                <p>&#128197; <?= e($b['booking_date']) ?> &nbsp;
                   <?= date('g.i A', strtotime($b['start_time'])) ?> - <?= date('g.i A', strtotime($b['end_time'])) ?></p>
                <p>&#128100; <?= (int)$b['people'] ?> <?= $b['people'] > 1 ? 'people' : 'person' ?>
                   &middot; Rs. <?= number_format((float)$b['total_price'], 2) ?></p>
            </div>
            <div class="booking-row__side">
                <span class="badge badge--green"><?= ucfirst($b['status']) ?></span>
                <a class="btn btn--outline btn--sm" href="place.php?id=<?= (int)$b['place_id'] ?>">View Details</a>
                <?php if ($b['status'] === 'upcoming'): ?>
                    <form method="post" action="my-bookings.php" onsubmit="return confirm('Cancel this booking?');">
                        <input type="hidden" name="cancel_id" value="<?= (int)$b['id'] ?>">
                        <button class="btn btn--ghost btn--sm" type="submit">Cancel</button>
                    </form>
                <?php elseif ($b['status'] === 'completed'): ?>
                    <a class="btn btn--ghost btn--sm" href="review.php?id=<?= (int)$b['place_id'] ?>">Write a review</a>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
