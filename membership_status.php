<?php
// membership_status.php
require_once 'config/db.php';require_once 'includes/auth.php';requireLogin();
$userId=(int)$_SESSION['user_id'];
$stmt=$pdo->prepare("SELECT um.*,mp.plan_name,mp.description,mp.price FROM user_memberships um JOIN membership_plans mp ON mp.id=um.membership_plan_id WHERE um.user_id=? ORDER BY um.created_at DESC");
$stmt->execute([$userId]);$memberships=$stmt->fetchAll();
$pageTitle='My Membership';include 'includes/header.php';
?>
<div style="padding:28px 28px 40px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
        <h1 class="sf-section-title" style="margin:0">My membership</h1>
        <a href="membership_plans.php" class="sf-nav-btn sf-nav-btn-solid" style="text-decoration:none">View plans</a>
    </div>
    <?php if(empty($memberships)): ?>
    <div style="text-align:center;padding:60px;border:.5px solid var(--border);border-radius:var(--radius-lg);color:var(--ink-soft)">
        <p style="margin-bottom:14px">No memberships yet.</p>
        <a href="membership_plans.php" class="sf-nav-btn sf-nav-btn-solid" style="display:inline-block;text-decoration:none">Browse plans</a>
    </div>
    <?php else: ?>
    <?php foreach($memberships as $m):$isActive=$m['membership_status']==='active'&&$m['end_date']>=date('Y-m-d');$daysLeft=(int)ceil((strtotime($m['end_date'])-time())/86400); ?>
    <div style="border:.5px solid <?= $isActive?'var(--brand)':'var(--border)' ?>;border-radius:var(--radius-lg);padding:24px;display:flex;align-items:flex-start;justify-content:space-between;gap:20px;margin-bottom:14px;<?= $isActive?'border-left:3px solid var(--brand)':'' ?>">
        <div>
            <h3 style="font-size:18px;font-weight:500;margin-bottom:4px"><?= htmlspecialchars($m['plan_name']) ?></h3>
            <?php if($m['description']): ?><p style="font-size:13px;color:var(--ink-soft);margin-bottom:8px"><?= htmlspecialchars($m['description']) ?></p><?php endif; ?>
            <div style="font-size:12px;color:var(--ink-soft)"><?= date('d M Y',strtotime($m['start_date'])) ?> → <?= date('d M Y',strtotime($m['end_date'])) ?></div>
            <?php if($isActive&&$daysLeft>=0): ?><div style="font-size:12px;margin-top:4px;color:<?= $daysLeft<=7?'var(--brand)':'var(--ink-soft)' ?>"><?= $daysLeft ?> days remaining</div><?php endif; ?>
        </div>
        <div style="text-align:right;flex-shrink:0">
            <span class="sf-pill <?= $isActive?'sf-pill-green':($m['membership_status']==='cancelled'?'sf-pill-gray':'sf-pill-red') ?>" style="display:block;margin-bottom:6px"><?= ucfirst($m['membership_status']) ?></span>
            <div style="font-size:15px;font-weight:500">$<?= number_format($m['price'],2) ?>/mo</div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
