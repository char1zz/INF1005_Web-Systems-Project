<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
$plans=$pdo->query("SELECT * FROM membership_plans WHERE status='active' ORDER BY price ASC")->fetchAll();
$activeMembership=null;
if(isLoggedIn()){$m=$pdo->prepare("SELECT um.*,mp.plan_name FROM user_memberships um JOIN membership_plans mp ON mp.id=um.membership_plan_id WHERE um.user_id=? AND um.membership_status='active' AND um.end_date>=CURDATE() ORDER BY um.end_date DESC LIMIT 1");$m->execute([$_SESSION['user_id']]);$activeMembership=$m->fetch();}
if($_SERVER['REQUEST_METHOD']==='POST'&&isLoggedIn()){
    $planId=(int)($_POST['plan_id']??0);$userId=(int)$_SESSION['user_id'];
    $plan=$pdo->prepare("SELECT * FROM membership_plans WHERE id=? AND status='active'");$plan->execute([$planId]);$plan=$plan->fetch();
    if($plan){
        $pdo->prepare("UPDATE user_memberships SET membership_status='cancelled' WHERE user_id=? AND membership_status='active'")->execute([$userId]);
        $start=date('Y-m-d');$end=date('Y-m-d',strtotime("+{$plan['duration_months']} months"));
        $pdo->prepare("INSERT INTO user_memberships(user_id,membership_plan_id,start_date,end_date,membership_status) VALUES(?,?,?,?,'active')")->execute([$userId,$planId,$start,$end]);
        setFlash('success',"Welcome to the {$plan['plan_name']} plan!");redirect('membership_status.php');
    }
}
$featured='Pro';
$featureMap=['Starter'=>['4 classes per month','App access','Booking 24h in advance','Locker access'],'Pro'=>['Unlimited classes','Priority booking','10% shop discount','1 guest pass/month','Nutrition workshops'],'Elite'=>['Unlimited classes','2 PT sessions/month','20% shop discount','3 guest passes/month','Nutrition coaching','Towel & locker service']];
$pageTitle='Membership Plans';include 'includes/header.php';
?>
<div class="sf-section-head" style="text-align:center;flex-direction:column;align-items:center">
    <div class="sf-section-title">Membership plans</div>
    <div style="font-size:13px;color:var(--ink-soft)">Choose the plan that fits your hustle. Cancel anytime.</div>
    <?php if($activeMembership): ?>
    <div class="sf-alert sf-alert-success" style="margin-top:12px;display:inline-block">You are on the <strong><?= htmlspecialchars($activeMembership['plan_name']) ?></strong> plan, active until <?= date('d M Y',strtotime($activeMembership['end_date'])) ?>.</div>
    <?php endif; ?>
</div>
<div class="sf-plan-grid">
    <?php foreach($plans as $plan):$isFeat=$plan['plan_name']===$featured;$feats=$featureMap[$plan['plan_name']]??[]; ?>
    <div class="sf-plan <?= $isFeat?'featured':'' ?>">
        <?php if($isFeat): ?><div class="sf-plan-tag">Most popular</div><?php endif; ?>
        <div class="sf-plan-name"><?= htmlspecialchars($plan['plan_name']) ?></div>
        <div class="sf-plan-price <?= $isFeat?'text-brand':'' ?>">$<?= number_format($plan['price'],0) ?></div>
        <div class="sf-plan-period">per month</div>
        <?php if($plan['description']): ?><p style="font-size:13px;color:var(--ink-soft);margin-bottom:16px;line-height:1.6"><?= htmlspecialchars($plan['description']) ?></p><?php endif; ?>
        <ul class="sf-plan-feats">
            <?php foreach($feats as $f): ?><li><?= htmlspecialchars($f) ?></li><?php endforeach; ?>
        </ul>
        <?php if(!isLoggedIn()): ?>
            <a href="login.php" class="sf-plan-btn <?= $isFeat?'sf-plan-btn-solid':'sf-plan-btn-outline' ?>" style="display:block;text-align:center;text-decoration:none">Log in to purchase</a>
        <?php elseif($activeMembership&&$activeMembership['plan_name']===$plan['plan_name']): ?>
            <button class="sf-plan-btn" style="background:#f0fdf4;border:1px solid #86efac;color:#15803d;cursor:not-allowed" disabled>Current plan</button>
        <?php else: ?>
            <form method="POST"><input type="hidden" name="plan_id" value="<?= $plan['id'] ?>"><button type="submit" class="sf-plan-btn <?= $isFeat?'sf-plan-btn-solid':'sf-plan-btn-outline' ?>" <?= $activeMembership?"data-confirm='Switch to the {$plan['plan_name']} plan?'":'' ?>>Choose <?= htmlspecialchars($plan['plan_name']) ?></button></form>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php include 'includes/footer.php'; ?>
