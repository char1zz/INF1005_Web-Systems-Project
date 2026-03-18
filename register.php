<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
if (isLoggedIn()) redirect('index.php');
$name = $email = ''; $errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = sanitize($_POST['name']             ?? '');
    $email    = sanitize($_POST['email']            ?? '');
    $password = $_POST['password']                  ?? '';
    $confirm  = $_POST['confirm_password']          ?? '';
    if (empty($name)||empty($email)||empty($password)||empty($confirm)) $errors[] = 'All fields are required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';
    if (empty($errors)) {
        $check = $pdo->prepare('SELECT id FROM users WHERE email=?');
        $check->execute([$email]);
        if ($check->fetch()) { $errors[] = 'That email is already registered.'; }
        else {
            $pdo->prepare('INSERT INTO users (name,email,password,role) VALUES (?,?,?,\'user\')')
                ->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            redirect('login.php?registered=1');
        }
    }
}
$pageTitle = 'Sign Up';
include 'includes/header.php';
?>
<div class="sf-auth-wrap">
    <div class="sf-auth-logo"><span>SPIN</span>FIT</div>
    <h1 class="sf-auth-title">Create account</h1>
    <p class="sf-auth-sub">Join the community and start booking today.</p>
    <?php if (!empty($errors)): ?><div class="sf-alert sf-alert-danger"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div><?php endif; ?>
    <form method="POST" novalidate>
        <div class="sf-form-group"><label class="sf-label">Full name</label><input type="text" name="name" class="sf-input" placeholder="Jane Doe" value="<?= htmlspecialchars($name) ?>" required></div>
        <div class="sf-form-group"><label class="sf-label">Email address</label><input type="email" name="email" class="sf-input" placeholder="you@email.com" value="<?= htmlspecialchars($email) ?>" required></div>
        <div class="sf-form-group"><label class="sf-label">Password</label><input type="password" name="password" class="sf-input" placeholder="Min 6 characters" required><div class="sf-form-hint">At least 6 characters</div></div>
        <div class="sf-form-group"><label class="sf-label">Confirm password</label><input type="password" name="confirm_password" class="sf-input" placeholder="Re-enter password" required></div>
        <button type="submit" class="sf-submit-btn">Create account</button>
    </form>
    <div class="sf-auth-divider"><span>or</span></div>
    <p class="sf-auth-link">Already a member? <a href="login.php">Log in</a></p>
</div>
<?php include 'includes/footer.php'; ?>
