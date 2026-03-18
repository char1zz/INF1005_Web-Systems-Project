<?php
// admin/manage_classes.php
require_once '../config/db.php';require_once '../includes/auth.php';requireAdmin();
$success=$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $action=$_POST['action']??'';
    if(in_array($action,['add','edit'])){
        $name=trim($_POST['name']??'');$type=$_POST['type']??'spin';$instr=trim($_POST['instructor']??'');
        $date=$_POST['class_date']??'';$time=$_POST['start_time']??'';$dur=(int)($_POST['duration_min']??45);
        $venue=trim($_POST['venue']??'');$cap=(int)($_POST['capacity']??20);$desc=trim($_POST['description']??'');
        if($name===''||$instr===''||$date===''||$time===''||$venue===''){$error='Please fill in all required fields.';}
        else{
            if($action==='add'){$pdo->prepare('INSERT INTO classes(name,type,instructor,class_date,start_time,duration_min,venue,capacity,description) VALUES(?,?,?,?,?,?,?,?,?)')->execute([$name,$type,$instr,$date,$time,$dur,$venue,$cap,$desc]);$success="Class \"$name\" added.";}
            else{$id=(int)$_POST['id'];$pdo->prepare('UPDATE classes SET name=?,type=?,instructor=?,class_date=?,start_time=?,duration_min=?,venue=?,capacity=?,description=? WHERE id=?')->execute([$name,$type,$instr,$date,$time,$dur,$venue,$cap,$desc,$id]);$success='Class updated.';}
        }
    }elseif($action==='delete'){$id=(int)$_POST['id'];$pdo->prepare('DELETE FROM classes WHERE id=?')->execute([$id]);$success='Class deleted.';}
}
$edit=null;if(isset($_GET['edit'])){$s=$pdo->prepare('SELECT * FROM classes WHERE id=?');$s->execute([(int)$_GET['edit']]);$edit=$s->fetch();}
$classes=$pdo->query("SELECT c.*,COUNT(CASE WHEN b.status='confirmed' THEN 1 END) AS booked_count FROM classes c LEFT JOIN bookings b ON b.class_id=c.id GROUP BY c.id ORDER BY c.class_date DESC,c.start_time DESC")->fetchAll();
$pageTitle='Manage Classes';include '../includes/header.php';
?>
<div class="sf-admin-wrap">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px">
        <h1 class="sf-admin-title" style="margin:0">Manage classes</h1>
        <a href="dashboard.php" class="sf-nav-btn sf-nav-btn-outline" style="text-decoration:none">← Dashboard</a>
    </div>
    <?php if($error): ?><div class="sf-alert sf-alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if($success): ?><div class="sf-alert sf-alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <div style="display:grid;grid-template-columns:340px 1fr;gap:24px;align-items:start">
        <div class="sf-admin-form-card">
            <div class="sf-admin-form-title"><?= $edit?'Edit class':'Add new class' ?></div>
            <form method="POST">
                <input type="hidden" name="action" value="<?= $edit?'edit':'add' ?>">
                <?php if($edit): ?><input type="hidden" name="id" value="<?= $edit['id'] ?>"><?php endif; ?>
                <div class="sf-form-group"><label class="sf-label">Name *</label><input type="text" name="name" class="sf-input" required value="<?= htmlspecialchars($edit['name']??'') ?>"></div>
                <div class="sf-form-group"><label class="sf-label">Type</label><select name="type" class="sf-input" style="padding:10px 12px"><option value="spin" <?= ($edit['type']??'spin')==='spin'?'selected':'' ?>>Spin</option><option value="hiit" <?= ($edit['type']??'')==='hiit'?'selected':'' ?>>HIIT</option></select></div>
                <div class="sf-form-group"><label class="sf-label">Instructor *</label><input type="text" name="instructor" class="sf-input" required value="<?= htmlspecialchars($edit['instructor']??'') ?>"></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                    <div class="sf-form-group"><label class="sf-label">Date *</label><input type="date" name="class_date" class="sf-input" required value="<?= $edit['class_date']??'' ?>"></div>
                    <div class="sf-form-group"><label class="sf-label">Start time *</label><input type="time" name="start_time" class="sf-input" required value="<?= $edit['start_time']??'' ?>"></div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                    <div class="sf-form-group"><label class="sf-label">Duration (min)</label><input type="number" name="duration_min" class="sf-input" min="10" value="<?= $edit['duration_min']??45 ?>"></div>
                    <div class="sf-form-group"><label class="sf-label">Capacity</label><input type="number" name="capacity" class="sf-input" min="1" value="<?= $edit['capacity']??20 ?>"></div>
                </div>
                <div class="sf-form-group"><label class="sf-label">Venue *</label><input type="text" name="venue" class="sf-input" required value="<?= htmlspecialchars($edit['venue']??'') ?>"></div>
                <div class="sf-form-group"><label class="sf-label">Description</label><textarea name="description" class="sf-input" rows="2" style="height:auto;resize:vertical"><?= htmlspecialchars($edit['description']??'') ?></textarea></div>
                <div style="display:flex;gap:10px"><button type="submit" class="sf-submit-btn" style="margin-top:0"><?= $edit?'Save changes':'Add class' ?></button><?php if($edit): ?><a href="manage_classes.php" class="sf-back-btn" style="display:inline-flex;align-items:center;justify-content:center;text-decoration:none;padding:12px 16px">Cancel</a><?php endif; ?></div>
            </form>
        </div>
        <div style="border:.5px solid var(--border);border-radius:var(--radius-lg);overflow:hidden">
            <div style="padding:14px 20px;border-bottom:.5px solid var(--border);font-size:13px;font-weight:500">All classes (<?= count($classes) ?>)</div>
            <table class="sf-table" style="width:100%;font-size:12px">
                <thead><tr><th>Class</th><th>Date</th><th>Time</th><th>Booked</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach($classes as $c): ?>
                <tr>
                    <td><div style="font-weight:500"><?= htmlspecialchars($c['name']) ?></div><span class="badge-<?= $c['type'] ?>"><?= strtoupper($c['type']) ?></span> <span style="color:var(--ink-soft)"><?= htmlspecialchars($c['venue']) ?></span></td>
                    <td><?= date('d M Y',strtotime($c['class_date'])) ?></td>
                    <td><?= date('H:i',strtotime($c['start_time'])) ?></td>
                    <td><span class="<?= $c['booked_count']>=$c['capacity']?'text-brand':'' ?>"><?= $c['booked_count'] ?>/<?= $c['capacity'] ?></span></td>
                    <td><div style="display:flex;gap:6px"><a href="?edit=<?= $c['id'] ?>" class="sf-nav-btn sf-nav-btn-outline" style="padding:4px 10px;font-size:11px;text-decoration:none">Edit</a><form method="POST" style="display:inline"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $c['id'] ?>"><button type="submit" class="sf-nav-btn" style="padding:4px 10px;font-size:11px;border:1px solid #fca5a5;color:#991b1b;background:none" data-confirm="Delete this class and all bookings?">Delete</button></form></div></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
