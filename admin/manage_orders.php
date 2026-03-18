<?php
require_once '../config/db.php';require_once '../includes/auth.php';requireAdmin();
$success='';
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['order_id'],$_POST['status'])){
    $allowed=['pending','processing','completed','cancelled'];
    $newStatus=$_POST['status'];$oid=(int)$_POST['order_id'];
    if(in_array($newStatus,$allowed)){$pdo->prepare('UPDATE orders SET status=? WHERE id=?')->execute([$newStatus,$oid]);$success="Order #$oid updated to ".ucfirst($newStatus).".";}
}
$sf=$_GET['status']??'';
$where=['1=1'];$params=[];if($sf!==''){$where[]='o.status=?';$params[]=$sf;}
$os=$pdo->prepare("SELECT o.*,u.name AS user_name,u.email AS user_email FROM orders o JOIN users u ON u.id=o.user_id WHERE ".implode(' AND ',$where)." ORDER BY o.created_at DESC");
$os->execute($params);$orders=$os->fetchAll();
$smap=['pending'=>'sf-pill-amber','processing'=>'sf-pill-blue','completed'=>'sf-pill-green','cancelled'=>'sf-pill-gray'];
$pageTitle='Manage Orders';include '../includes/header.php';
?>
<div class="sf-admin-wrap">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px"><h1 class="sf-admin-title" style="margin:0">Orders</h1><a href="dashboard.php" class="sf-nav-btn sf-nav-btn-outline" style="text-decoration:none">← Dashboard</a></div>
    <?php if($success): ?><div class="sf-alert sf-alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <div style="display:flex;gap:8px;margin-bottom:18px;flex-wrap:wrap">
        <a href="manage_orders.php" class="sf-nav-btn <?= $sf===''?'sf-nav-btn-solid':'sf-nav-btn-outline' ?>" style="text-decoration:none;padding:6px 14px;font-size:12px">All</a>
        <?php foreach(['pending','processing','completed','cancelled'] as $s): ?>
        <a href="?status=<?= $s ?>" class="sf-nav-btn <?= $sf===$s?'sf-nav-btn-solid':'sf-nav-btn-outline' ?>" style="text-decoration:none;padding:6px 14px;font-size:12px"><?= ucfirst($s) ?></a>
        <?php endforeach; ?>
    </div>
    <div style="border:.5px solid var(--border);border-radius:var(--radius-lg);overflow:hidden">
        <table class="sf-table" style="width:100%">
            <thead><tr><th>#</th><th>Member</th><th>Subtotal</th><th>Discount</th><th>Total</th><th>Status</th><th>Date</th><th>Update</th></tr></thead>
            <tbody>
            <?php if(empty($orders)): ?><tr><td colspan="8" style="text-align:center;color:var(--ink-soft);padding:32px">No orders found.</td></tr>
            <?php else: foreach($orders as $o):$c=$smap[$o['status']]??'sf-pill-gray'; ?>
            <tr>
                <td style="font-weight:500">#<?= $o['id'] ?></td>
                <td><div style="font-weight:500"><?= htmlspecialchars($o['user_name']) ?></div><div style="font-size:11px;color:var(--ink-soft)"><?= htmlspecialchars($o['user_email']) ?></div></td>
                <td>$<?= number_format($o['subtotal'],2) ?></td>
                <td style="color:#15803d"><?= $o['discount_applied']?'-$'.number_format($o['discount_amount'],2):'—' ?></td>
                <td style="font-weight:500;color:var(--brand)">$<?= number_format($o['total'],2) ?></td>
                <td><span class="sf-pill <?= $c ?>"><?= ucfirst($o['status']) ?></span></td>
                <td style="font-size:12px;color:var(--ink-soft)"><?= date('d M Y',strtotime($o['created_at'])) ?></td>
                <td>
                    <form method="POST" style="display:flex;gap:6px">
                        <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                        <select name="status" class="sf-sort-select" style="font-size:11px">
                            <?php foreach(['pending','processing','completed','cancelled'] as $s): ?><option value="<?= $s ?>" <?= $o['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?>
                        </select>
                        <button type="submit" class="sf-nav-btn sf-nav-btn-solid" style="padding:5px 10px;font-size:11px">Save</button>
                    </form>
                </td>
            </tr>
            <?php endforeach;endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
