<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
$id=isset($_GET['id'])?(int)$_GET['id']:0;
$stmt=$pdo->prepare("SELECT c.*,COUNT(CASE WHEN b.status='confirmed' THEN 1 END) AS booked_count FROM classes c LEFT JOIN bookings b ON b.class_id=c.id WHERE c.id=? GROUP BY c.id");
$stmt->execute([$id]);$class=$stmt->fetch();
if(!$class){setFlash('error','Class not found.');redirect('classes.php');}
$spotsLeft=$class['capacity']-$class['booked_count'];$isFull=$spotsLeft<=0;
$alreadyBooked=false;
if(isLoggedIn()){$chk=$pdo->prepare("SELECT id FROM bookings WHERE user_id=? AND class_id=? AND status='confirmed'");$chk->execute([$_SESSION['user_id'],$id]);$alreadyBooked=(bool)$chk->fetch();}
$pageTitle=htmlspecialchars($class['name']);include 'includes/header.php';
?>
<div class="sf-breadcrumb"><a href="classes.php">Classes</a><span class="sf-breadcrumb-sep">/</span><span class="sf-breadcrumb-current"><?= htmlspecialchars($class['name']) ?></span></div>
<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;padding:16px 28px 40px;align-items:start">
    <div style="border:.5px solid var(--border);border-radius:var(--radius-lg);padding:28px">
        <div style="margin-bottom:14px"><span class="badge-<?= $class['type'] ?>"><?= strtoupper($class['type']) ?></span></div>
        <h1 style="font-size:24px;font-weight:500;margin-bottom:12px"><?= htmlspecialchars($class['name']) ?></h1>
        <?php if($class['description']): ?><p style="font-size:14px;color:var(--ink-mid);line-height:1.7;margin-bottom:24px"><?= nl2br(htmlspecialchars($class['description'])) ?></p><?php endif; ?>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
            <?php $meta=[['Date',date('l, d M Y',strtotime($class['class_date']))],['Time',date('H:i',strtotime($class['start_time'])).' ('.$class['duration_min'].' min)'],['Instructor',$class['instructor']],['Venue',$class['venue']]];
            foreach($meta as[$k,$v]): ?>
            <div style="background:var(--surface);border-radius:var(--radius-sm);padding:14px">
                <div style="font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:var(--ink-soft);margin-bottom:4px"><?= $k ?></div>
                <div style="font-size:14px;font-weight:500"><?= htmlspecialchars($v) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div style="border:.5px solid var(--border);border-radius:var(--radius-lg);padding:24px;position:sticky;top:100px">
        <div style="font-size:12px;text-transform:uppercase;letter-spacing:.08em;color:var(--ink-soft);margin-bottom:8px">Availability</div>
        <div style="font-size:28px;font-weight:600;margin-bottom:4px"><?= $spotsLeft > 0 ? $spotsLeft : 0 ?></div>
        <div style="font-size:13px;color:var(--ink-soft);margin-bottom:16px">spot<?= $spotsLeft===1?'':'s' ?> left out of <?= (int)$class['capacity'] ?></div>
        <div style="font-size:12px;color:var(--ink-soft);background:var(--surface);padding:12px;border-radius:var(--radius-sm);margin-bottom:16px">Members may cancel online up to 12 hours before class start. After that, please contact the studio directly.</div>
        <?php if($alreadyBooked): ?>
            <div class="sf-alert sf-alert-success" style="margin-bottom:12px">You have already booked this class.</div>
            <form method="POST" action="cancel_booking.php">
                <input type="hidden" name="class_id" value="<?= $class['id'] ?>">
                <input type="hidden" name="redirect" value="class_detail.php?id=<?= $class['id'] ?>">
                <button type="submit" class="sf-back-btn" style="width:100%" data-confirm="Cancel your booking?">Cancel booking</button>
            </form>
        <?php elseif(!isLoggedIn()): ?>
            <a href="login.php" class="sf-add-btn" style="display:block;text-align:center;text-decoration:none">Log in to book</a>
        <?php elseif($isFull): ?>
            <button class="sf-back-btn" style="width:100%;cursor:not-allowed" disabled>Class full</button>
        <?php else: ?>
            <form method="POST" action="book_class.php">
                <input type="hidden" name="class_id" value="<?= $class['id'] ?>">
                <button type="submit" class="sf-add-btn" style="width:100%">Confirm booking</button>
            </form>
        <?php endif; ?>
        <a href="classes.php?year=<?= date('Y',strtotime($class['class_date'])) ?>&month=<?= date('m',strtotime($class['class_date'])) ?>&date=<?= htmlspecialchars($class['class_date']) ?>" class="sf-back-btn" style="display:block;text-align:center;text-decoration:none;margin-top:10px">← Back to schedule</a>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
