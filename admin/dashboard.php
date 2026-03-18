<?php
require_once '../config/db.php';require_once '../includes/auth.php';requireAdmin();
$totalMembers=$pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
$activeMems=$pdo->query("SELECT COUNT(*) FROM user_memberships WHERE membership_status='active' AND end_date>=CURDATE()")->fetchColumn();
$todayBookings=$pdo->query("SELECT COUNT(*) FROM bookings b JOIN classes c ON c.id=b.class_id WHERE b.status='confirmed' AND c.class_date=CURDATE()")->fetchColumn();
$revenue=$pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status!='cancelled'")->fetchColumn();
$pendingOrders=$pdo->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();
$totalProducts=$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$recentMembers=$pdo->query("SELECT id,name,email,role,created_at FROM users ORDER BY created_at DESC LIMIT 8")->fetchAll();
$recentOrders=$pdo->query("SELECT o.*,u.name AS user_name FROM orders o JOIN users u ON u.id=o.user_id ORDER BY o.created_at DESC LIMIT 8")->fetchAll();
$statusMap=['pending'=>'sf-pill-amber','processing'=>'sf-pill-blue','completed'=>'sf-pill-green','cancelled'=>'sf-pill-gray'];
$pageTitle='Admin Dashboard';include '../includes/header.php';
?>
<div class="sf-admin-wrap">
    <h1 class="sf-admin-title">Dashboard</h1>
    <div class="sf-stat-grid">
        <div class="sf-stat"><div class="sf-stat-val text-brand"><?= $totalMembers ?></div><div class="sf-stat-label">Total members</div></div>
        <div class="sf-stat"><div class="sf-stat-val" style="color:#15803d"><?= $activeMems ?></div><div class="sf-stat-label">Active memberships</div></div>
        <div class="sf-stat"><div class="sf-stat-val"><?= $todayBookings ?></div><div class="sf-stat-label">Bookings today</div></div>
        <div class="sf-stat"><div class="sf-stat-val" style="color:#1d4ed8">$<?= number_format($revenue,0) ?></div><div class="sf-stat-label">Total revenue</div></div>
    </div>
    <div class="sf-stat-grid" style="grid-template-columns:repeat(2,1fr);margin-bottom:28px">
        <div class="sf-stat"><div class="sf-stat-val" style="color:#b45309"><?= $pendingOrders ?></div><div class="sf-stat-label">Pending orders</div></div>
        <div class="sf-stat"><div class="sf-stat-val"><?= $totalProducts ?></div><div class="sf-stat-label">Products listed</div></div>
    </div>

    <div class="sf-quick-links">
        <a href="manage_classes.php" class="sf-ql">
            <div class="sf-ql-icon" style="font-size:22px;color:var(--brand)">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M16 2v4M8 2v4M3 10h18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            </div>
            <div class="sf-ql-label">Classes</div><div class="sf-ql-sub">Add, edit, delete</div>
        </a>
        <a href="manage_plans.php" class="sf-ql">
            <div class="sf-ql-icon" style="font-size:22px;color:var(--brand)">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><rect x="2" y="5" width="20" height="14" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M2 10h20" stroke="currentColor" stroke-width="1.5"/></svg>
            </div>
            <div class="sf-ql-label">Membership plans</div><div class="sf-ql-sub">Pricing & features</div>
        </a>
        <a href="../shop/admin/admin_products.php" class="sf-ql">
            <div class="sf-ql-icon" style="font-size:22px;color:var(--brand)">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/><line x1="3" y1="6" x2="21" y2="6" stroke="currentColor" stroke-width="1.5"/></svg>
            </div>
            <div class="sf-ql-label">Products</div><div class="sf-ql-sub">Shop catalogue</div>
        </a>
        <a href="manage_orders.php" class="sf-ql">
            <div class="sf-ql-icon" style="font-size:22px;color:var(--brand)">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M9 11l3 3L22 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            </div>
            <div class="sf-ql-label">Orders</div><div class="sf-ql-sub">Update status</div>
        </a>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
        <div style="border:.5px solid var(--border);border-radius:var(--radius-lg);overflow:hidden">
            <div style="padding:14px 20px;border-bottom:.5px solid var(--border);font-size:13px;font-weight:500">Recent members</div>
            <table class="sf-table" style="width:100%">
                <thead><tr><th>Name</th><th>Role</th><th>Joined</th></tr></thead>
                <tbody>
                <?php foreach($recentMembers as $m): ?>
                <tr><td><div style="font-weight:500"><?= htmlspecialchars($m['name']) ?></div><div style="font-size:11px;color:var(--ink-soft)"><?= htmlspecialchars($m['email']) ?></div></td><td><span class="sf-pill <?= $m['role']==='admin'?'sf-pill-amber':'sf-pill-gray' ?>"><?= ucfirst($m['role']) ?></span></td><td style="font-size:12px"><?= date('d M Y',strtotime($m['created_at'])) ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div style="border:.5px solid var(--border);border-radius:var(--radius-lg);overflow:hidden">
            <div style="padding:14px 20px;border-bottom:.5px solid var(--border);font-size:13px;font-weight:500">Recent orders</div>
            <table class="sf-table" style="width:100%">
                <thead><tr><th>#</th><th>Member</th><th>Total</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach($recentOrders as $o): ?>
                <tr><td style="font-weight:500">#<?= $o['id'] ?></td><td><?= htmlspecialchars($o['user_name']) ?></td><td>$<?= number_format($o['total'],2) ?></td><td><span class="sf-pill <?= $statusMap[$o['status']]??'sf-pill-gray' ?>"><?= ucfirst($o['status']) ?></span></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
