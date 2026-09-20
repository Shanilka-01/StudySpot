<?php
/**
 * Write a review
 */
require_once __DIR__ . '/includes/functions.php';
require_login();

$placeId = (int)($_GET['id'] ?? $_POST['place_id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM places WHERE id = ?');
$stmt->execute([$placeId]);
$place = $stmt->fetch();

if (!$place) {
    set_flash('error', 'That study space does not exist.');
    header('Location: explore.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating  = (int)($_POST['rating'] ?? 0);
    $noise   = $_POST['noise_level'] ?? '';
    $wifi    = $_POST['wifi_quality'] ?? '';
    $value   = $_POST['value_for_money'] ?? '';
    $comment = trim($_POST['comment'] ?? '');

    if ($rating < 1 || $rating > 5)  $errors[] = 'Choose a rating between 1 and 5 stars.';
    if ($comment === '')             $errors[] = 'Write a few words about your visit.';
    if (strlen($comment) > 500)   $errors[] = 'Keep your review under 500 characters.';

    if (!$errors) {
        $insert = $pdo->prepare(
            'INSERT INTO reviews (place_id, user_id, rating, noise_level, wifi_quality, value_for_money, comment)
             VALUES (?,?,?,?,?,?,?)'
        );
        $insert->execute([$placeId, current_user_id(), $rating, $noise, $wifi, $value, $comment]);
        set_flash('success', 'Thanks! Your review is published.');
        header('Location: place.php?id=' . $placeId);
        exit;
    }
}

$page_title = 'Write a Review';
$active     = 'explore';
require __DIR__ . '/includes/header.php';
?>

<div class="container page">
    <p class="breadcrumb"><a href="place.php?id=<?= $placeId ?>">&larr; Back to <?= e($place['name']) ?></a></p>

    <div class="split">
        <div class="card card__pad">
            <h1 style="font-size:30px;">Write a Review</h1>
            <p class="muted">Share your experience and help other students</p>

            <?php foreach ($errors as $error): ?>
                <p class="error"><?= e($error) ?></p>
            <?php endforeach; ?>

            <form method="post" action="review.php">
                <input type="hidden" name="place_id" value="<?= $placeId ?>">
                <input type="hidden" name="rating" id="ratingField" value="<?= (int)($_POST['rating'] ?? 5) ?>">

                <div class="field">
                    <label>Overall Rating</label>
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div class="star-input" data-input="ratingField">
                            <span class="on">&#9733;</span><span class="on">&#9733;</span><span class="on">&#9733;</span><span class="on">&#9733;</span><span class="on">&#9733;</span>
                        </div>
                        <b id="ratingValue">5.0</b>
                    </div>
                </div>

                <div class="form-row">
                    <div class="field">
                        <label for="noise_level">Noise Level</label>
                        <select name="noise_level" id="noise_level">
                            <option>Very Quiet</option><option>Quiet</option>
                            <option>Moderate</option><option>Lively</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="wifi_quality">Wi-Fi Quality</label>
                        <select name="wifi_quality" id="wifi_quality">
                            <option>Excellent</option><option>Good</option>
                            <option>Average</option><option>Poor</option>
                        </select>
                    </div>
                </div>

                <div class="field" style="max-width:220px;">
                    <label for="value_for_money">Value for Money</label>
                    <select name="value_for_money" id="value_for_money">
                        <option>Excellent</option><option>Good</option>
                        <option>Average</option><option>Poor</option>
                    </select>
                </div>

                <div class="field">
                    <label for="comment">Your Review</label>
                    <textarea name="comment" id="comment" maxlength="500" placeholder="Write your review here..."><?= e($_POST['comment'] ?? '') ?></textarea>
                </div>

                <button class="btn" type="submit">Submit Review</button>
            </form>
        </div>

        <aside class="guidelines">
            <h3>Review Guidelines</h3>
            <ul>
                <li>Be honest and respectful</li>
                <li>Focus on your own experience</li>
                <li>Mention noise level, Wi-Fi, cleanliness and facilities</li>
                <li>Avoid personal or offensive comments</li>
            </ul>
            <img src="assets/img/review-guide.png" alt="" class="guidelines__art">
        </aside>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
