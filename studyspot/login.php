<?php
/**
 * Login page
 */
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$errors   = [];
$email    = '';
$redirect = $_GET['redirect'] ?? 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $redirect = $_POST['redirect'] ?? 'index.php';

    if ($email === '' || $password === '') {
        $errors[] = 'Enter your email and password.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']   = (int)$user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email']     = $user['email'];
            set_flash('success', 'Welcome back, ' . $user['full_name'] . '.');
            header('Location: ' . (str_starts_with($redirect, 'http') ? 'index.php' : $redirect));
            exit;
        }
        $errors[] = 'That email and password do not match an account.';
    }
}

$page_title = 'Login';
$hide_nav   = true;
require __DIR__ . '/includes/header.php';
?>

<div class="auth">
    <div class="auth__card">
        <p class="auth__logo"><img src="assets/img/logo.png" alt="" width="30" height="30">
            <span class="logo__text">Study<span>Spot</span></span></p>
        <h1>Welcome Back!</h1>
        <p class="sub">Login to continue</p>

        <?php foreach ($errors as $error): ?>
            <p class="error"><?= e($error) ?></p>
        <?php endforeach; ?>

        <form method="post" action="login.php">
            <input type="hidden" name="redirect" value="<?= e($redirect) ?>">

            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= e($email) ?>" placeholder="You@example.com" required>
            </div>

            <div class="field">
                <label for="password" style="display:flex;justify-content:space-between;">
                    Password <a href="help.php" style="color:var(--green-700);">Forgot Password?</a>
                </label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>

            <button class="btn btn--block" type="submit">Login</button>
        </form>

        <p class="auth__foot">Don't have an account? <a href="register.php">Sign up</a></p>
        <p class="center muted" style="font-size:13px;margin-top:18px;">
            Demo login: sahan@example.com / 123456
        </p>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
