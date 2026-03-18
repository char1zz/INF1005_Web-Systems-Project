<?php
// shop/includes/shop_helpers.php

define('MEMBER_DISCOUNT', 0.10);

function userHasActiveMembership($pdo, int $userId): bool {
    $stmt = $pdo->prepare("
        SELECT id FROM user_memberships
        WHERE user_id = ? AND membership_status = 'active' AND end_date >= CURDATE()
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    return (bool)$stmt->fetch();
}

function getCartCount($pdo, int $userId): int {
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity),0) FROM cart_items WHERE user_id = ?");
    $stmt->execute([$userId]);
    return (int)$stmt->fetchColumn();
}

function getCartItems($pdo, int $userId): array {
    $stmt = $pdo->prepare("
        SELECT ci.id AS cart_id, ci.quantity, p.*
        FROM cart_items ci
        JOIN products p ON p.id = ci.product_id
        WHERE ci.user_id = ?
        ORDER BY ci.created_at ASC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function redirect(string $url): void {
    header("Location: $url");
    exit;
}

function setFlash(string $type, string $msg): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
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
