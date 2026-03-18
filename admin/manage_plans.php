<?php
// admin/manage_plans.php
require_once '../config/db.php';require_once '../includes/auth.php';requireAdmin();
$success=$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $action=$_POST['action']??'';
    if(in_array($action,['add','edit'])){
        $name=trim($_POST['plan_name']??'');$desc=trim($_POST['description']??'');$price=(float)($_POST['price']??0);$dur=(int)($_POST['duration_months']??1);$status=$_POST['status']??'active';
        if($name===''||$price<=0){$error='Plan name and price required.';}
        else{if($action==='add'){$pdo->prepare('INSERT INTO membership_plans(plan_name,description,price,duration_months,status) VALUES(?,?,?,?,?)')->execute([$name,$desc,$price,$dur,$status]);$success="Plan \"$name\" added.";}else{$id=(int)$_POST['id'];$pdo->prepare('UPDATE membership_plans SET plan_name=?,description=?,price=?,duration_months=?,status=? WHERE id=?')->execute([$name,$desc,$price,$dur,$status,$id]);$success='Plan updated.';}}
    }elseif($action==='delete'){$id=(int)$_POST['id'];$pdo->prepare('DELETE FROM membership_plans WHERE id=?')->execute([$id]);$success='Plan deleted.';}
}
$edit=null;if(isset($_GET['edit'])){$s=$pdo->prepare('SELECT * FROM membership_plans WHERE id=?');$s->execute([(int)$_GET['edit']]);$edit=$s->fetch();}
$plans=$pdo->query("SELECT mp.*,COUNT(um.id) AS subs FROM membership_plans mp LEFT JOIN user_memberships um ON um.membership_plan_id=mp.id AND um.membership_status='active' GROUP BY mp.id ORDER BY mp.price ASC")->fetchAll();
$pageTitle='Manage Plans';include '../includes/header.php';
?>
<div class="sf-admin-wrap">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px"><h1 class="sf-admin-title" style="margin:0">Membership plans</h1><a href="dashboard.php" class="sf-nav-btn sf-nav-btn-outline" style="text-decoration:none">← Dashboard</a></div>
    <?php if($error): ?><div class="sf-alert sf-alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if($success): ?><div class="sf-alert sf-alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <div style="display:grid;grid-template-columns:320px 1fr;gap:24px;align-items:start">
        <div class="sf-admin-form-card">
            <div class="sf-admin-form-title"><?= $edit?'Edit plan':'Add new plan' ?></div>
            <form method="POST">
                <input type="hidden" name="action" value="<?= $edit?'edit':'add' ?>">
                <?php if($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
                <div class="sf-form-group"><label class="sf-label">Plan name *</label><input type="text" name="plan_name" class="sf-input" required value="<?= htmlspecialchars($edit['plan_name']??'') ?>"></div>
                <div class="sf-form-group"><label class="sf-label">Description</label><textarea name="description" class="sf-input" rows="2" style="height:auto;resize:vertical"><?= htmlspecialchars($edit['description']??'') ?></textarea></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                    <div class="sf-form-group"><label class="sf-label">Price ($/mo) *</label><input type="number" name="price" class="sf-input" step="0.01" min="0.01" required value="<?= $edit['price']??'' ?>"></div>
                    <div class="sf-form-group"><label class="sf-label">Duration (months)</label><input type="number" name="duration_months" class="sf-input" min="1" value="<?= $edit['duration_months']??1 ?>"></div>
                </div>
                <div class="sf-form-group"><label class="sf-label">Status</label><select name="status" class="sf-input" style="padding:10px 12px"><option value="active" <?= ($edit['status']??'active')==='active'?'selected':'' ?>>Active</option><option value="inactive" <?= ($edit['status']??'')==='inactive'?'selected':'' ?>>Inactive</option></select></div>
                <div style="display:flex;gap:10px"><button type="submit" class="sf-submit-btn" style="margin-top:0"><?= $edit?'Save changes':'Add plan' ?></button><?php if($edit): ?><a href="manage_plans.php" class="sf-back-btn" style="display:inline-flex;align-items:center;justify-content:center;text-decoration:none;padding:12px 16px">Cancel</a><?php endif; ?></div>
            </form>
        </div>
        <div style="border:.5px solid var(--border);border-radius:var(--radius-lg);overflow:hidden">
            <div style="padding:14px 20px;border-bottom:.5px solid var(--border);font-size:13px;font-weight:500">All plans (<?= count($plans) ?>)</div>
            <table class="sf-table" style="width:100%">
                <thead><tr><th>Plan</th><th>Price</th><th>Duration</th><th>Subscribers</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach($plans as $p): ?>
                <tr><td><div style="font-weight:500"><?= htmlspecialchars($p['plan_name']) ?></div><div style="font-size:11px;color:var(--ink-soft)"><?= htmlspecialchars(mb_strimwidth($p['description']??'',0,48,'…')) ?></div></td><td>$<?= number_format($p['price'],2) ?>/mo</td><td><?= $p['duration_months'] ?> mo</td><td><span class="sf-pill sf-pill-blue"><?= $p['subs'] ?> active</span></td><td><span class="sf-pill <?= $p['status']==='active'?'sf-pill-green':'sf-pill-gray' ?>"><?= ucfirst($p['status']) ?></span></td><td><div style="display:flex;gap:6px"><a href="?edit=<?= $p['id'] ?>" class="sf-nav-btn sf-nav-btn-outline" style="padding:4px 10px;font-size:11px;text-decoration:none">Edit</a><form method="POST" style="display:inline"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $p['id'] ?>"><button type="submit" class="sf-nav-btn" style="padding:4px 10px;font-size:11px;border:1px solid #fca5a5;color:#991b1b;background:none" data-confirm="Delete '<?= htmlspecialchars(addslashes($p['plan_name'])) ?>'?">Delete</button></form></div></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
