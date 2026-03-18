<?php
require_once 'config/db.php';require_once 'includes/auth.php';requireLogin();
$userId=(int)$_SESSION['user_id'];
$user=$pdo->prepare('SELECT id,name,email,role,created_at FROM users WHERE id=?');$user->execute([$userId]);$user=$user->fetch();
$bStmt=$pdo->prepare("SELECT b.id AS bid,c.* FROM bookings b JOIN classes c ON c.id=b.class_id WHERE b.user_id=? AND b.status='confirmed' AND c.class_date>=CURDATE() ORDER BY c.class_date,c.start_time LIMIT 5");
$bStmt->execute([$userId]);$upcoming=$bStmt->fetchAll();
$mStmt=$pdo->prepare("SELECT um.*,mp.plan_name FROM user_memberships um JOIN membership_plans mp ON mp.id=um.membership_plan_id WHERE um.user_id=? AND um.membership_status='active' AND um.end_date>=CURDATE() ORDER BY um.end_date DESC LIMIT 1");
$mStmt->execute([$userId]);$membership=$mStmt->fetch();
$pageTitle='My Profile';include 'includes/header.php';
?>
<div class="sf-section-head"><div class="sf-section-title">My profile</div></div>
<div class="sf-profile-grid">
    <div>
        <div class="sf-profile-card">
            <div class="sf-avatar"><?= strtoupper(substr($user['name'],0,1)) ?></div>
            <div class="sf-profile-name"><?= htmlspecialchars($user['name']) ?></div>
            <div class="sf-profile-role"><?= ucfirst($user['role']) ?></div>
            <dl class="sf-profile-dl">
                <dt>Email</dt><dd><?= htmlspecialchars($user['email']) ?></dd>
                <dt>Member since</dt><dd><?= date('d M Y',strtotime($user['created_at'])) ?></dd>
                <dt>Membership</dt><dd><?php if($membership): ?><span class="sf-pill sf-pill-green"><?= htmlspecialchars($membership['plan_name']) ?></span><?php else: ?><a href="membership_plans.php" style="color:var(--brand);font-size:13px">Get a plan</a><?php endif; ?></dd>
            </dl>
            <a href="logout.php" class="sf-back-btn" style="display:block;text-align:center;text-decoration:none;margin-top:16px">Log out</a>
        </div>
        <?php if($membership): ?>
        <div class="sf-profile-card" style="margin-top:12px;background:var(--surface)">
            <div style="font-size:12px;color:var(--ink-soft);text-transform:uppercase;letter-spacing:.07em;margin-bottom:6px">Active plan</div>
            <div style="font-size:16px;font-weight:500"><?= htmlspecialchars($membership['plan_name']) ?></div>
            <div style="font-size:12px;color:var(--ink-soft);margin-top:4px">Expires <?= date('d M Y',strtotime($membership['end_date'])) ?></div>
            <a href="membership_status.php" style="font-size:12px;color:var(--brand);display:block;margin-top:8px">Manage →</a>
        </div>
        <?php endif; ?>
    </div>
    <div>
        <div class="sf-profile-card">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
                <div style="font-size:14px;font-weight:500">Upcoming classes</div>
                <a href="my_bookings.php" style="font-size:12px;color:var(--brand)">View all</a>
            </div>
            <?php if(empty($upcoming)): ?>
            <div style="text-align:center;padding:24px;color:var(--ink-soft);font-size:13px">No upcoming bookings. <a href="classes.php" style="color:var(--brand)">Book a class</a></div>
            <?php else: foreach($upcoming as $b): ?>
            <div class="sf-class-item">
                <div class="sf-class-time"><div class="sf-class-time-val"><?= date('d',strtotime($b['class_date'])) ?></div><div class="sf-class-time-dur"><?= date('M',strtotime($b['class_date'])) ?></div></div>
                <div class="sf-class-info" style="flex:1">
                    <h4><?= htmlspecialchars($b['name']) ?> <span class="badge-<?= $b['type'] ?>"><?= strtoupper($b['type']) ?></span></h4>
                    <p><?= htmlspecialchars($b['instructor']) ?> · <?= htmlspecialchars($b['venue']) ?> · <?= date('H:i',strtotime($b['start_time'])) ?></p>
                </div>
            </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
