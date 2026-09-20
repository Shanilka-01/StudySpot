<?php
/**
 * Create account page
 */
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$errors = [];
$name   = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['full_name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $pass    = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if ($name === '')                              $errors[] = 'Enter your full name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';
    if (strlen($pass) < 6)                          $errors[] = 'Use a password of at least 6 characters.';
    if ($pass !== $confirm)                         $errors[] = 'The two passwords do not match.';

    if (!$errors) {
        $check = $pdo->prepare('SELECT 1 FROM users WHERE email = ?');
        $check->execute([$email]);
        if ($check->fetchColumn()) {
            $errors[] = 'An account already uses that email. Log in instead.';
        } else {
            $insert = $pdo->prepare('INSERT INTO users (full_name, email, password_hash) VALUES (?,?,?)');
            $insert->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT)]);

            $_SESSION['user_id']   = (int)$pdo->lastInsertId();
            $_SESSION['full_name'] = $name;
            $_SESSION['email']     = $email;
            set_flash('success', 'Your account is ready. Start exploring.');
            header('Location: index.php');
            exit;
        }
    }
}

$page_title = 'Create Account';
$hide_nav   = true;
require __DIR__ . '/includes/header.php';
?>

<div class="auth">
    <div class="auth__card">
        <p class="auth__logo"><img src="assets/img/logo.png" alt="" width="30" height="30">
            <span class="logo__text">Study<span>Spot</span></span></p>
        <h1>Create Account</h1>
        <p class="sub">Sign up to get started</p>

        <?php foreach ($errors as $error): ?>
            <p class="error"><?= e($error) ?></p>
        <?php endforeach; ?>

        <form method="post" action="register.php">
            <div class="field">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" value="<?= e($name) ?>" placeholder="Enter your full name" required>
            </div>
            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= e($email) ?>" placeholder="You@example.com" required>
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <div class="field">
                <label for="confirm">Confirm Password</label>
                <input type="password" id="confirm" name="confirm" placeholder="Enter your password again" required>
            </div>

            <button class="btn btn--block" type="submit">Sign up</button>
        </form>

        <p class="auth__foot">Already have an account? <a href="login.php">Login</a></p>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
