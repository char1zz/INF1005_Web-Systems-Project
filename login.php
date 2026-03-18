<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
if (isLoggedIn()) redirect('index.php');
$email = ''; $errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = sanitize($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($email) || empty($password)) { $errors[] = 'Email and password are required.'; }
    else {
        $stmt = $pdo->prepare('SELECT id,name,email,password,role FROM users WHERE email=?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['role']    = $user['role'];
            setFlash('success', 'Welcome back, ' . $user['name'] . '!');
            redirect($user['role'] === 'admin' ? 'admin/dashboard.php' : 'profile.php');
        } else { $errors[] = 'Invalid email or password.'; }
    }
}
$pageTitle = 'Log In';
include 'includes/header.php';
?>
<div class="sf-auth-wrap">
    <div class="sf-auth-logo"><span>SPIN</span>FIT</div>
    <h1 class="sf-auth-title">Welcome back</h1>
    <p class="sf-auth-sub">Log in to book classes and manage your account.</p>
    <?php if (isset($_GET['registered'])): ?><div class="sf-alert sf-alert-success">Registration successful — please log in.</div><?php endif; ?>
    <?php if (!empty($errors)): ?><div class="sf-alert sf-alert-danger"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div><?php endif; ?>
    <form method="POST" novalidate>
        <div class="sf-form-group"><label class="sf-label">Email address</label><input type="email" name="email" class="sf-input" placeholder="you@email.com" value="<?= htmlspecialchars($email) ?>" required></div>
        <div class="sf-form-group"><label class="sf-label">Password</label><input type="password" name="password" class="sf-input" placeholder="••••••••" required></div>
        <button type="submit" class="sf-submit-btn">Log in</button>
    </form>
    <div class="sf-auth-divider"><span>or</span></div>
    <p class="sf-auth-link">No account yet? <a href="register.php">Sign up</a></p>
</div>
<?php include 'includes/footer.php'; ?>
