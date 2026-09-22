<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';

/** Escape output. Use on every echo that prints data from the database or a form. */
function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

function current_user_id(): ?int
{
    return $_SESSION['user_id'] ?? null;
}

/** Send a guest to the login page and come back here afterwards. */
function require_login(): void
{
    if (!is_logged_in()) {
        $back = urlencode(basename($_SERVER['REQUEST_URI'] ?? 'index.php'));
        header('Location: login.php?redirect=' . $back);
        exit;
    }
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

/** Average rating + review count for one place. */
function place_rating(PDO $pdo, int $placeId): array
{
    $stmt = $pdo->prepare('SELECT ROUND(AVG(rating),1) AS avg_rating, COUNT(*) AS total FROM reviews WHERE place_id = ?');
    $stmt->execute([$placeId]);
    $row = $stmt->fetch();
    return [
        'avg'   => $row['avg_rating'] ? (float)$row['avg_rating'] : 0.0,
        'total' => (int)$row['total'],
    ];
}

function is_favorite(PDO $pdo, int $placeId): bool
{
    if (!is_logged_in()) {
        return false;
    }
    $stmt = $pdo->prepare('SELECT 1 FROM favorites WHERE user_id = ? AND place_id = ?');
    $stmt->execute([current_user_id(), $placeId]);
    return (bool)$stmt->fetchColumn();
}

/** Photo for a place, with a safe fallback when the file is missing. */
function place_image(?string $file): string
{
    if ($file && file_exists(__DIR__ . '/../assets/img/' . $file)) {
        return 'assets/img/' . $file;
    }
    return 'assets/img/placeholder.svg';
}

function type_label(string $type): string
{
    return [
        'library'    => 'Library',
        'cafe'       => 'Cafe',
        'coworking'  => 'Co-working Space',
        'university' => 'University Area',
    ][$type] ?? ucfirst($type);
}

function noise_label(string $noise): string
{
    return [
        'very_quiet' => 'Very Quiet',
        'quiet'      => 'Quiet',
        'moderate'   => 'Moderate',
        'lively'     => 'Lively',
    ][$noise] ?? ucfirst($noise);
}

function wifi_label(string $wifi): string
{
    return ['free' => 'Free Wi-Fi', 'paid' => 'Paid Wi-Fi', 'none' => 'No Wi-Fi'][$wifi] ?? $wifi;
}

/** 8.00 AM - 8.00 PM */
function hours_label(string $open, string $close): string
{
    return date('g.i A', strtotime($open)) . ' - ' . date('g.i A', strtotime($close));
}

function stars(float $rating): string
{
    $full = (int)round($rating);
    $out  = '';
    for ($i = 1; $i <= 5; $i++) {
        $out .= '<span class="star' . ($i <= $full ? '' : ' star--empty') . '">&#9733;</span>';
    }
    return $out;
}

function time_ago(string $datetime): string
{
    $diff = time() - strtotime($datetime);
    if ($diff < 60)      return 'just now';
    if ($diff < 3600)    return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400)   return floor($diff / 3600) . ' hours ago';
    if ($diff < 2592000) return floor($diff / 86400) . ' days ago';
    return date('d M Y', strtotime($datetime));
}

/** One reusable place card (used on the home page and the favourites page). */
function render_place_card(PDO $pdo, array $place): void
{
    $rating = place_rating($pdo, (int)$place['id']);
    ?>
    <a class="place-card" href="place.php?id=<?= (int)$place['id'] ?>">
        <img src="<?= e(place_image($place['cover_image'])) ?>" alt="<?= e($place['name']) ?>">
        <div class="place-card__body">
            <p class="place-card__city">&#128205; <?= e($place['city']) ?></p>
            <h3><?= e($place['name']) ?></h3>
            <div class="place-card__meta">
                <span class="rating"><span class="star">&#9733;</span> <b><?= number_format($rating['avg'], 1) ?></b>
                    <small>(<?= $rating['total'] ?>)</small></span>
                <span class="badge badge--green">Open Now</span>
            </div>
        </div>
    </a>
    <?php
}
