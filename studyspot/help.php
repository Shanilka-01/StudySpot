<?php
/**
 * Help & FAQ
 */
require_once __DIR__ . '/includes/functions.php';

$faqs = [
    'How do I make a booking?' =>
        'Open any study space, press "Book your spot", then choose a date, a start time and how many people are coming. You confirm on the payment screen and the booking appears under My Bookings.',
    'Can I cancel my booking?' =>
        'Yes. Go to My Bookings, open the Upcoming tab and press Cancel on the booking. Cancelling more than 2 hours before the start time is free.',
    'What payment methods are available?' =>
        'Credit and debit cards, bank transfer, or paying at the counter when you arrive. Free study spaces need no payment at all.',
    'Is there a refund policy?' =>
        'Bookings cancelled at least 2 hours before the start time are refunded in full to the original payment method within 5 working days.',
    'How do I add a place to my favorites?' =>
        'Press the heart on a study space page or in the search results. Everything you save is listed under My Favourites.',
    'How can I contact support?' =>
        'Email support@studyspot.lk and the team replies within one working day.',
];

$page_title = 'Help & FAQ';
require __DIR__ . '/includes/header.php';
?>

<div class="container page faq">
    <h1>Help &amp; FAQ</h1>

    <?php foreach ($faqs as $question => $answer): ?>
        <details>
            <summary><?= e($question) ?></summary>
            <p><?= e($answer) ?></p>
        </details>
    <?php endforeach; ?>

    <div class="help-box">
        <span>&#9993;</span>
        <div>
            <h3 style="margin:0;">Need more help?</h3>
            <p style="margin:4px 0 0;">Contact us at <a href="mailto:support@studyspot.lk"><b>support@studyspot.lk</b></a></p>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
