<?php
/**
 * Step 2 - booking summary + payment details.
 * NOTE: this is a university project, so no real card is charged.
 * Card numbers are never stored - only a generated reference is saved.
 */
require_once __DIR__ . '/includes/functions.php';
require_login();

$pending = $_SESSION['pending_booking'] ?? null;
if (!$pending) {
    set_flash('error', 'Start a booking first.');
    header('Location: explore.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM places WHERE id = ?');
$stmt->execute([$pending['place_id']]);
$place = $stmt->fetch();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $card   = preg_replace('/\s+/', '', $_POST['card_number'] ?? '');
    $expiry = trim($_POST['expiry'] ?? '');
    $cvv    = trim($_POST['cvv'] ?? '');
    $holder = trim($_POST['card_name'] ?? '');
    $method = $_POST['method'] ?? 'card';

    if ($method === 'card') {
        if (!preg_match('/^\d{16}$/', $card))            $errors[] = 'Enter the 16 digits of your card number.';
        if (!preg_match('/^\d{2}\/\d{2}$/', $expiry))    $errors[] = 'Enter the expiry date as MM/YY.';
        if (!preg_match('/^\d{3,4}$/', $cvv))            $errors[] = 'Enter the 3 digit CVV.';
        if ($holder === '')                              $errors[] = 'Enter the name printed on the card.';
    }

    if (!$errors) {
        $ref = 'SS-' . strtoupper(bin2hex(random_bytes(3)));

        $insert = $pdo->prepare(
            'INSERT INTO bookings (user_id, place_id, booking_date, start_time, end_time, people, total_price, status, payment_ref)
             VALUES (?,?,?,?,?,?,?,"upcoming",?)'
        );
        $insert->execute([
            current_user_id(), $pending['place_id'], $pending['date'],
            $pending['start'], $pending['end'], $pending['people'],
            $pending['total_price'], $ref,
        ]);

        unset($_SESSION['pending_booking']);
        header('Location: booking-confirmed.php?id=' . $pdo->lastInsertId());
        exit;
    }
}

$page_title = 'Payment';
$active     = 'explore';
require __DIR__ . '/includes/header.php';
?>

<div class="container page">
    <div class="split">
        <div>
            <div class="card card__pad">
                <h2>Booking Summary</h2>
                <div style="display:flex;gap:16px;margin:16px 0;">
                    <img src="<?= e(place_image($place['cover_image'])) ?>" alt="" style="width:110px;height:82px;object-fit:cover;border-radius:8px;">
                    <div>
                        <h3 style="margin:0;"><?= e($place['name']) ?></h3>
                        <p class="muted" style="margin:4px 0;"><?= e($place['city']) ?></p>
                    </div>
                </div>
                <div class="summary-line"><span>Date</span><b><?= e($pending['date']) ?></b></div>
                <div class="summary-line"><span>Time</span>
                    <b><?= date('g.i A', strtotime($pending['start'])) ?> - <?= date('g.i A', strtotime($pending['end'])) ?></b></div>
                <div class="summary-line"><span>People</span><b><?= (int)$pending['people'] ?></b></div>
                <div class="summary-line"><span>Price</span><b>Rs. <?= number_format($pending['total_price'], 2) ?></b></div>
            </div>

            <div class="card card__pad pay-methods" style="margin-top:20px;">
                <h2>Payment Method</h2>
                <label><input type="radio" name="method" value="card" form="payForm" checked> Credit / Debit Card</label>
                <label><input type="radio" name="method" value="cash" form="payForm"> Pay at the counter</label>
                <label><input type="radio" name="method" value="bank" form="payForm"> Bank transfer</label>
            </div>
        </div>

        <div class="card card__pad">
            <h2>Card Details</h2>

            <?php foreach ($errors as $error): ?>
                <p class="error"><?= e($error) ?></p>
            <?php endforeach; ?>

            <form method="post" action="payment.php" id="payForm">
                <div class="field">
                    <label for="card_number">Card Number</label>
                    <input type="text" id="card_number" name="card_number" inputmode="numeric"
                           maxlength="19" placeholder="1234 5678 9012 3456">
                </div>
                <div class="form-row">
                    <div class="field">
                        <label for="expiry">Expiry Date</label>
                        <input type="text" id="expiry" name="expiry" maxlength="5" placeholder="MM/YY">
                    </div>
                    <div class="field">
                        <label for="cvv">CVV</label>
                        <input type="text" id="cvv" name="cvv" maxlength="4" placeholder="123">
                    </div>
                </div>
                <div class="field">
                    <label for="card_name">Name on Card</label>
                    <input type="text" id="card_name" name="card_name" placeholder="Enter name">
                </div>

                <button class="btn btn--soft btn--block" type="submit">Pay Now</button>
                <p class="center muted" style="margin-top:14px;font-size:13px;">
                    &#128274; Demo checkout - no card is charged and no card data is stored.
                </p>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
