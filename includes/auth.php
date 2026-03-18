<?php
// includes/auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin(string $redirect = '/spinfit/login.php'): void {
    if (!isLoggedIn()) {
        header("Location: $redirect");
        exit();
    }
}

function requireAdmin(string $redirect = '/spinfit/login.php'): void {
    if (!isLoggedIn() || !isAdmin()) {
        header("Location: $redirect");
        exit();
    }
}

function sanitize(string $data): string {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function setFlash(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function showFlash(): void {
    if (empty($_SESSION['flash'])) return;
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    $class = match($f['type']) {
        'success' => 'alert-success',
        'error'   => 'alert-danger',
        'warning' => 'alert-warning',
        default   => 'alert-info',
    };
    echo "<div class='alert {$class} alert-dismissible fade show' role='alert'>"
       . htmlspecialchars($f['msg'])
       . "<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
}

function redirect(string $url): void {
    header("Location: $url");
    exit();
}
