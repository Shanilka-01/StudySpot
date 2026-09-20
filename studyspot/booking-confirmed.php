<?php
/**
 * Step 3 - confirmation screen
 */
require_once __DIR__ . '/includes/functions.php';
require_login();

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    'SELECT b.*, p.name, p.city, p.cover_image
     FROM bookings b JOIN places p ON p.id = b.place_id
     WHERE b.id = ? AND b.user_id = ?'
);
$stmt->execute([$id, current_user_id()]);
$booking = $stmt->fetch();

if (!$booking) {
    set_flash('error', 'That booking was not found.');
    header('Location: my-bookings.php');
    exit;
}

$page_title = 'Booking Confirmed';
require __DIR__ . '/includes/header.php';
?>

<div class="container page">
    <div class="confirm">
        <div class="confirm__tick">&#10003;</div>
        <h1>Booking Confirmed!</h1>
        <p class="muted">Your spot has been successfully booked. Reference <?= e($booking['payment_ref']) ?>.</p>

        <div class="confirm__card">
            <img src="<?= e(place_image($booking['cover_image'])) ?>" alt="">
            <div style="flex:1;">
                <h3><?= e($booking['name']) ?></h3>
                <p class="muted" style="margin:0 0 12px;"><?= e($booking['city']) ?></p>
                <div class="summary-line"><span>Date</span><b><?= e($booking['booking_date']) ?></b></div>
                <div class="summary-line"><span>Time</span>
                    <b><?= date('g.i A', strtotime($booking['start_time'])) ?> - <?= date('g.i A', strtotime($booking['end_time'])) ?></b></div>
                <div class="summary-line"><span>People</span><b><?= (int)$booking['people'] ?></b></div>
                <div class="summary-line"><span>Total Price</span><b>Rs. <?= number_format((float)$booking['total_price'], 2) ?></b></div>
            </div>
        </div>

        <a class="btn btn--block" href="my-bookings.php">View My Bookings</a>
        <a class="btn btn--outline btn--block" style="margin-top:12px;" href="index.php">Back to Home</a>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
