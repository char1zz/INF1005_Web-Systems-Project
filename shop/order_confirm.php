<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once 'includes/shop_helpers.php';
requireLogin('/spinfit/login.php');
$orderId=(int)($_GET['order_id']??0);
$userId=(int)$_SESSION['user_id'];
$oStmt=$pdo->prepare('SELECT * FROM orders WHERE id=? AND user_id=?');
$oStmt->execute([$orderId,$userId]);
$order=$oStmt->fetch();
if(!$order){redirect('my_orders.php');}
$iStmt=$pdo->prepare('SELECT * FROM order_items WHERE order_id=?');
$iStmt->execute([$orderId]);
$orderItems=$iStmt->fetchAll();
$pageTitle='Order Confirmed';
include '../includes/header.php';
?>
<div class="sf-page-inner" style="max-width:640px">
    <div style="text-align:center;padding:32px 0 28px">
        <div style="width:56px;height:56px;background:#f0fdf4;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <h1 style="font-size:24px;font-weight:500;margin-bottom:6px">Order confirmed!</h1>
        <p style="color:var(--ink-soft);font-size:14px">Thank you! Order <strong>#<?= $order['id'] ?></strong> has been placed.</p>
        <p style="color:var(--ink-soft);font-size:12px"><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></p>
    </div>

    <div style="border:.5px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;margin-bottom:24px">
        <div style="padding:14px 20px;border-bottom:.5px solid var(--border);font-size:13px;font-weight:500">Order #<?= $order['id'] ?> summary</div>
        <?php foreach($orderItems as $item): ?>
        <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 20px;border-bottom:.5px solid var(--border)">
            <div>
                <div style="font-size:13px;font-weight:500"><?= htmlspecialchars($item['name']) ?></div>
                <div style="font-size:12px;color:var(--ink-soft)">Qty <?= $item['quantity'] ?></div>
            </div>
            <span style="font-size:13px;font-weight:500">$<?= number_format($item['unit_price']*$item['quantity'],2) ?></span>
        </div>
        <?php endforeach; ?>
        <div style="padding:14px 20px">
            <div class="sf-summary-row"><span>Subtotal</span><span>$<?= number_format($order['subtotal'],2) ?></span></div>
            <?php if($order['discount_applied']): ?>
            <div class="sf-summary-row" style="color:#15803d"><span>Member discount (10%)</span><span>−$<?= number_format($order['discount_amount'],2) ?></span></div>
            <?php endif; ?>
            <hr class="sf-summary-divider">
            <div class="sf-summary-total"><span>Total paid</span><span class="text-brand">$<?= number_format($order['total'],2) ?></span></div>
        </div>
    </div>

    <div style="display:flex;gap:12px;justify-content:center">
        <a href="shop.php" class="sf-nav-btn sf-nav-btn-solid" style="text-decoration:none">Continue shopping</a>
        <a href="my_orders.php" class="sf-nav-btn sf-nav-btn-outline" style="text-decoration:none">My orders</a>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
