<?php
/**
 * Step 1 of the booking flow - date, time, number of people.
 * The choice is kept in the session and confirmed on payment.php.
 */
require_once __DIR__ . '/includes/functions.php';
require_login();

$placeId = (int)($_GET['id'] ?? $_POST['place_id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM places WHERE id = ?');
$stmt->execute([$placeId]);
$place = $stmt->fetch();

if (!$place) {
    set_flash('error', 'Pick a study space first.');
    header('Location: explore.php');
    exit;
}

$rating = place_rating($pdo, $placeId);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date   = $_POST['booking_date'] ?? '';
    $start  = $_POST['start_time'] ?? '';
    $hours  = max(1, (int)($_POST['hours'] ?? 2));
    $people = max(1, min(10, (int)($_POST['people'] ?? 1)));

    if (!$date || strtotime($date) < strtotime(date('Y-m-d'))) {
        $errors[] = 'Choose today or a future date.';
    }
    if (!$start) {
        $errors[] = 'Choose a start time.';
    }

    if (!$errors) {
        $end = date('H:i:s', strtotime($start) + $hours * 3600);

        $_SESSION['pending_booking'] = [
            'place_id'    => $placeId,
            'date'        => $date,
            'start'       => $start . ':00',
            'end'         => $end,
            'people'      => $people,
            'total_price' => (float)$place['price'] * $people,
        ];
        header('Location: payment.php');
        exit;
    }
}

$page_title = 'Book Your Spot';
$active     = 'explore';
require __DIR__ . '/includes/header.php';
?>

<div class="container page">
    <div class="split">
        <div>
            <img src="<?= e(place_image($place['cover_image'])) ?>" alt="<?= e($place['name']) ?>"
                 style="width:100%;height:230px;object-fit:cover;border-radius:10px;">
            <h2 style="margin-top:18px;"><?= e($place['name']) ?></h2>
            <p><b><?= e($place['city']) ?></b></p>
            <p class="rating"><span class="star">&#9733;</span> <b><?= number_format($rating['avg'], 1) ?></b>
                <small>(<?= $rating['total'] ?>)</small></p>
            <p class="muted"><?= e(hours_label($place['open_time'], $place['close_time'])) ?> &middot; <?= e($place['open_days']) ?></p>
        </div>

        <div>
            <h1 style="font-size:30px;">Book Your Spot</h1>

            <?php foreach ($errors as $error): ?>
                <p class="error"><?= e($error) ?></p>
            <?php endforeach; ?>

            <form method="post" action="booking.php">
                <input type="hidden" name="place_id" value="<?= $placeId ?>">

                <div class="field">
                    <label for="booking_date">Date</label>
                    <input type="date" id="booking_date" name="booking_date"
                           min="<?= date('Y-m-d') ?>" value="<?= e($_POST['booking_date'] ?? date('Y-m-d')) ?>" required>
                </div>

                <div class="form-row">
                    <div class="field">
                        <label for="start_time">Start time</label>
                        <input type="time" id="start_time" name="start_time"
                               value="<?= e($_POST['start_time'] ?? substr($place['open_time'], 0, 5)) ?>" required>
                    </div>
                    <div class="field">
                        <label for="hours">How long?</label>
                        <select name="hours" id="hours">
                            <?php foreach ([1, 2, 3, 4, 6, 8] as $h): ?>
                                <option value="<?= $h ?>" <?= ($_POST['hours'] ?? 2) == $h ? 'selected' : '' ?>><?= $h ?> hours</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="field">
                    <label for="people">Number of People</label>
                    <div class="stepper" id="peopleCounter" data-price="<?= (float)$place['price'] ?>">
                        <button type="button" data-step="-1" aria-label="Fewer people">&minus;</button>
                        <output id="peopleOut">1</output>
                        <button type="button" data-step="1" aria-label="More people">+</button>
                    </div>
                    <input type="hidden" name="people" id="people" value="1">
                </div>

                <div class="total">
                    <h3 style="margin:0;">Total Price</h3>
                    <b id="totalOut">Rs. <?= number_format((float)$place['price'], 0) ?></b>
                </div>

                <button class="btn btn--soft btn--block" type="submit">Process to Payment</button>
                <p class="center" style="margin-top:16px;">
                    <a href="place.php?id=<?= $placeId ?>" style="color:var(--green-700);font-weight:600;">&larr; Back to details</a>
                </p>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
