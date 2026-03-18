<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once 'includes/shop_helpers.php';

requireLogin('/spinfit/login.php');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('shop.php');

$action    = $_POST['action']    ?? '';
$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity  = isset($_POST['quantity'])   ? (int)$_POST['quantity'] : 1;
$cartId    = isset($_POST['cart_id'])    ? (int)$_POST['cart_id'] : 0;
$userId    = (int)$_SESSION['user_id'];
$quantity  = max(1, $quantity);

$redirectTo = $_POST['redirect'] ?? 'shop.php';
$path = basename(parse_url($redirectTo, PHP_URL_PATH) ?? '');
$allowed = ['shop.php', 'product_detail.php', 'cart.php', 'checkout.php'];
if (!in_array($path, $allowed, true)) {
    $redirectTo = 'shop.php';
}

switch ($action) {
    case 'add':
        $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
        $stmt->execute([$productId]);
        $product = $stmt->fetch();

        if (!$product) {
            setFlash('error', 'Product not found.');
            break;
        }
        if ((int)$product['stock'] <= 0) {
            setFlash('warning', 'This product is out of stock.');
            break;
        }

        $existing = $pdo->prepare('SELECT quantity FROM cart_items WHERE user_id = ? AND product_id = ?');
        $existing->execute([$userId, $productId]);
        $existingQty = (int)($existing->fetchColumn() ?: 0);
        if ($existingQty + $quantity > (int)$product['stock']) {
            setFlash('warning', 'Not enough stock available.');
            break;
        }

        $upsert = $pdo->prepare('INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)');
        $upsert->execute([$userId, $productId, $quantity]);
        setFlash('success', $product['name'] . ' added to cart.');
        break;

    case 'update':
        $stmt = $pdo->prepare('SELECT ci.id, p.stock FROM cart_items ci JOIN products p ON p.id = ci.product_id WHERE ci.id = ? AND ci.user_id = ?');
        $stmt->execute([$cartId, $userId]);
        $row = $stmt->fetch();

        if (!$row) {
            setFlash('error', 'Cart item not found.');
            break;
        }
        if ($quantity > (int)$row['stock']) {
            setFlash('warning', 'Not enough stock available.');
            break;
        }

        $pdo->prepare('UPDATE cart_items SET quantity = ? WHERE id = ? AND user_id = ?')->execute([$quantity, $cartId, $userId]);
        setFlash('success', 'Cart updated.');
        break;

    case 'remove':
        $pdo->prepare('DELETE FROM cart_items WHERE id = ? AND user_id = ?')->execute([$cartId, $userId]);
        setFlash('success', 'Item removed from cart.');
        break;

    case 'clear':
        $pdo->prepare('DELETE FROM cart_items WHERE user_id = ?')->execute([$userId]);
        setFlash('success', 'Cart cleared.');
        break;

    default:
        setFlash('error', 'Unknown action.');
}

redirect($redirectTo);
