<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_once 'includes/shop_helpers.php';
requireLogin('/spinfit/login.php');
$userId=(int)$_SESSION['user_id'];
$oStmt=$pdo->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC");
$oStmt->execute([$userId]);
$orders=$oStmt->fetchAll();
$statusMap=['pending'=>['label'=>'Pending','class'=>'sf-pill-amber'],'processing'=>['label'=>'Processing','class'=>'sf-pill-blue'],'completed'=>['label'=>'Completed','class'=>'sf-pill-green'],'cancelled'=>['label'=>'Cancelled','class'=>'sf-pill-gray']];
$pageTitle='My Orders';
include '../includes/header.php';
?>
<div class="sf-page-inner">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
        <h1 class="sf-page-title" style="margin:0">My orders</h1>
        <a href="shop.php" class="sf-nav-btn sf-nav-btn-outline" style="text-decoration:none">Continue shopping</a>
    </div>
    <?php if(empty($orders)): ?>
    <div style="text-align:center;padding:60px 0;color:var(--ink-soft)">
        <div style="font-size:14px;margin-bottom:12px">No orders yet.</div>
        <a href="shop.php" class="sf-nav-btn sf-nav-btn-solid" style="display:inline-block;text-decoration:none">Start shopping</a>
    </div>
    <?php else: ?>
    <div style="border:.5px solid var(--border);border-radius:var(--radius-lg);overflow:hidden">
        <?php foreach($orders as $i=>$o):
            $sm=$statusMap[$o['status']]??['label'=>ucfirst($o['status']),'class'=>'sf-pill-gray'];
            $iStmt=$pdo->prepare('SELECT * FROM order_items WHERE order_id=?');
            $iStmt->execute([$o['id']]);
            $ois=$iStmt->fetchAll();
        ?>
        <details <?= $i===0?'open':'' ?> style="border-bottom:.5px solid var(--border)">
            <summary style="display:flex;align-items:center;gap:16px;padding:16px 20px;cursor:pointer;list-style:none;user-select:none">
                <span style="font-size:13px;font-weight:500">Order #<?= $o['id'] ?></span>
                <span style="font-size:12px;color:var(--ink-soft)"><?= date('d M Y', strtotime($o['created_at'])) ?></span>
                <span class="sf-pill <?= $sm['class'] ?>" style="margin-left:auto"><?= $sm['label'] ?></span>
                <span style="font-size:14px;font-weight:500">$<?= number_format($o['total'],2) ?></span>
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" style="flex-shrink:0;color:var(--ink-soft)"><path d="M3 5l4 4 4-4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/></svg>
            </summary>
            <div style="padding:0;border-top:.5px solid var(--border)">
                <table class="sf-table" style="width:100%">
                    <thead><tr><th>Product</th><th>Unit price</th><th>Qty</th><th>Subtotal</th></tr></thead>
                    <tbody>
                    <?php foreach($ois as $oi): ?>
                    <tr>
                        <td style="font-weight:500"><?= htmlspecialchars($oi['name']) ?></td>
                        <td>$<?= number_format($oi['unit_price'],2) ?></td>
                        <td><?= $oi['quantity'] ?></td>
                        <td style="font-weight:500">$<?= number_format($oi['unit_price']*$oi['quantity'],2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot style="border-top:.5px solid var(--border)">
                        <tr><td colspan="3" style="text-align:right;color:var(--ink-soft);padding:10px 14px">Subtotal</td><td style="padding:10px 14px">$<?= number_format($o['subtotal'],2) ?></td></tr>
                        <?php if($o['discount_applied']): ?><tr><td colspan="3" style="text-align:right;color:#15803d;padding:4px 14px">Member discount (10%)</td><td style="color:#15803d;padding:4px 14px">−$<?= number_format($o['discount_amount'],2) ?></td></tr><?php endif; ?>
                        <tr><td colspan="3" style="text-align:right;font-weight:500;padding:10px 14px">Total</td><td style="font-weight:500;color:var(--brand);padding:10px 14px">$<?= number_format($o['total'],2) ?></td></tr>
                    </tfoot>
                </table>
            </div>
        </details>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>
