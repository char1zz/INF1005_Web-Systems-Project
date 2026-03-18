<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
requireLogin();
$userId=(int)$_SESSION['user_id'];
$up=$pdo->prepare("SELECT b.id AS booking_id,b.booked_at,c.* FROM bookings b JOIN classes c ON c.id=b.class_id WHERE b.user_id=? AND b.status='confirmed' AND c.class_date>=CURDATE() ORDER BY c.class_date,c.start_time");
$up->execute([$userId]);$upcoming=$up->fetchAll();
$past=$pdo->prepare("SELECT b.status,b.booked_at,c.* FROM bookings b JOIN classes c ON c.id=b.class_id WHERE b.user_id=? AND (c.class_date<CURDATE() OR b.status='cancelled') ORDER BY c.class_date DESC LIMIT 20");
$past->execute([$userId]);$past=$past->fetchAll();
$pageTitle='My Bookings';include 'includes/header.php';
?>
<div style="padding:0 28px 40px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;padding-top:24px;gap:20px;flex-wrap:wrap">
        <div>
            <h1 class="sf-section-title" style="margin:0">My bookings</h1>
            <div class="sf-section-meta">You can cancel online up to 12 hours before class starts.</div>
        </div>
        <a href="classes.php" class="sf-nav-btn sf-nav-btn-solid" style="text-decoration:none">Book a class</a>
    </div>
    <div style="font-size:13px;font-weight:500;margin-bottom:14px;color:var(--brand);text-transform:uppercase;letter-spacing:.07em">Upcoming (<?= count($upcoming) ?>)</div>
    <?php if(empty($upcoming)): ?>
    <div style="padding:32px;text-align:center;color:var(--ink-soft);border:.5px solid var(--border);border-radius:var(--radius-lg);margin-bottom:28px">No upcoming bookings. <a href="classes.php" style="color:var(--brand)">Browse classes</a></div>
    <?php else: ?>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:28px">
        <?php foreach($upcoming as $b): $classStart = strtotime($b['class_date'].' '.$b['start_time']); $canCancel = $classStart > strtotime('+12 hours'); ?>
        <div style="border:.5px solid var(--border);border-radius:var(--radius-lg);padding:18px;display:flex;gap:16px;align-items:flex-start">
            <div style="text-align:center;background:var(--surface);border-radius:var(--radius-sm);padding:10px 12px;min-width:60px">
                <div style="font-size:20px;font-weight:500;line-height:1;color:var(--brand)"><?= date('d',strtotime($b['class_date'])) ?></div>
                <div style="font-size:11px;color:var(--ink-soft);text-transform:uppercase;margin-top:2px"><?= date('M',strtotime($b['class_date'])) ?></div>
                <div style="font-size:11px;color:var(--ink-mid);margin-top:2px"><?= date('H:i',strtotime($b['start_time'])) ?></div>
            </div>
            <div style="flex:1">
                <div style="font-size:14px;font-weight:500;margin-bottom:4px"><?= htmlspecialchars($b['name']) ?> <span class="badge-<?= $b['type'] ?>"><?= strtoupper($b['type']) ?></span></div>
                <div style="font-size:12px;color:var(--ink-soft)"><?= htmlspecialchars($b['instructor']) ?> · <?= htmlspecialchars($b['venue']) ?></div>
                <div style="font-size:12px;color:var(--ink-soft)"><?= $b['duration_min'] ?> min</div>
                <?php if ($canCancel): ?>
                <form method="POST" action="cancel_booking.php" style="margin-top:10px">
                    <input type="hidden" name="class_id" value="<?= $b['id'] ?>">
                    <input type="hidden" name="redirect" value="my_bookings.php">
                    <button type="submit" class="sf-remove-btn" style="font-size:12px" data-confirm="Cancel this booking?">Cancel</button>
                </form>
                <?php else: ?>
                <div style="margin-top:10px;font-size:12px;color:#b45309">Cancellation window has closed.</div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php if(!empty($past)): ?>
    <div style="font-size:13px;font-weight:500;margin-bottom:14px;color:var(--ink-soft);text-transform:uppercase;letter-spacing:.07em">History</div>
    <div style="border:.5px solid var(--border);border-radius:var(--radius-lg);overflow:hidden">
        <table class="sf-table" style="width:100%">
            <thead><tr><th>Class</th><th>Date</th><th>Instructor</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach($past as $b): ?>
            <tr>
                <td><div style="font-weight:500"><?= htmlspecialchars($b['name']) ?></div><span class="badge-<?= $b['type'] ?>"><?= strtoupper($b['type']) ?></span></td>
                <td><?= date('d M Y',strtotime($b['class_date'])) ?> <?= date('H:i',strtotime($b['start_time'])) ?></td>
                <td><?= htmlspecialchars($b['instructor']) ?></td>
                <td><span class="sf-pill <?= $b['status']==='confirmed'?'sf-pill-green':'sf-pill-gray' ?>"><?= $b['status']==='confirmed'?'Attended':'Cancelled' ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
