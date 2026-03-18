<?php
require_once 'config/db.php';
require_once 'includes/auth.php';

$year  = isset($_GET['year'])  ? (int)$_GET['year']  : (int)date('Y');
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$typeFilter = trim($_GET['type'] ?? '');
if($month<1){$month=12;$year--;}if($month>12){$month=1;$year++;}

$sql = "SELECT c.*, COUNT(CASE WHEN b.status='confirmed' THEN 1 END) AS booked_count FROM classes c LEFT JOIN bookings b ON b.class_id=c.id WHERE YEAR(c.class_date)=? AND MONTH(c.class_date)=?";
$params = [$year, $month];
if (in_array($typeFilter, ['spin','hiit'], true)) { $sql .= " AND c.type=?"; $params[] = $typeFilter; }
$sql .= " GROUP BY c.id ORDER BY c.class_date,c.start_time";
$stmt=$pdo->prepare($sql);
$stmt->execute($params);
$allClasses=$stmt->fetchAll();
$classesByDate=[];foreach($allClasses as $c)$classesByDate[$c['class_date']][]=$c;

$userBookedIds=[];
if(isLoggedIn()){
    $bStmt=$pdo->prepare("SELECT b.class_id FROM bookings b JOIN classes c ON c.id=b.class_id WHERE b.user_id=? AND b.status='confirmed' AND YEAR(c.class_date)=? AND MONTH(c.class_date)=?");
    $bStmt->execute([$_SESSION['user_id'],$year,$month]);
    $userBookedIds=$bStmt->fetchAll(PDO::FETCH_COLUMN);
}

$today=date('Y-m-d');
$selectedDate=$_GET['date']??($year==(int)date('Y')&&$month==(int)date('m')?$today:null);

$prevMonth=$month-1;$prevYear=$year;if($prevMonth<1){$prevMonth=12;$prevYear--;}
$nextMonth=$month+1;$nextYear=$year;if($nextMonth>12){$nextMonth=1;$nextYear++;}
$monthNames=['','January','February','March','April','May','June','July','August','September','October','November','December'];
$daysInMonth=cal_days_in_month(CAL_GREGORIAN,$month,$year);
$firstDow=(int)date('w',mktime(0,0,0,$month,1,$year));

$pageTitle='Book Classes';
include 'includes/header.php';
?>

<div class="sf-section-head">
    <div>
        <div class="sf-section-title">Book a class</div>
        <div class="sf-section-meta">Choose a date to see available sessions. Online cancellations are allowed up to 12 hours before class.</div>
    </div>
    <?php if(isLoggedIn()): ?>
    <a href="/spinfit/my_bookings.php" style="font-size:13px;color:var(--brand)">My bookings →</a>
    <?php endif; ?>
</div>

<div style="padding:0 28px 16px;display:flex;gap:10px;flex-wrap:wrap">
    <a class="sf-cat-btn <?= $typeFilter===''?'active':'' ?>" href="classes.php?year=<?= $year ?>&month=<?= $month ?><?= $selectedDate ? '&date='.urlencode($selectedDate) : '' ?>">All</a>
    <a class="sf-cat-btn <?= $typeFilter==='spin'?'active':'' ?>" href="classes.php?year=<?= $year ?>&month=<?= $month ?>&type=spin<?= $selectedDate ? '&date='.urlencode($selectedDate) : '' ?>">Spin</a>
    <a class="sf-cat-btn <?= $typeFilter==='hiit'?'active':'' ?>" href="classes.php?year=<?= $year ?>&month=<?= $month ?>&type=hiit<?= $selectedDate ? '&date='.urlencode($selectedDate) : '' ?>">HIIT</a>
</div>

<div style="display:grid;grid-template-columns:320px 1fr;gap:20px;padding:0 28px 40px;align-items:start">
    <div>
        <div class="sf-cal-wrap">
            <div class="sf-cal-header">
                <h3><?= $monthNames[$month] ?> <?= $year ?></h3>
                <div class="sf-cal-nav">
                    <a class="sf-cal-nav-btn" href="classes.php?year=<?= $prevYear ?>&month=<?= $prevMonth ?><?= $typeFilter ? '&type='.urlencode($typeFilter) : '' ?>">&#8249;</a>
                    <a class="sf-cal-nav-btn" href="classes.php?year=<?= $nextYear ?>&month=<?= $nextMonth ?><?= $typeFilter ? '&type='.urlencode($typeFilter) : '' ?>">&#8250;</a>
                </div>
            </div>
            <table class="sf-cal-table">
                <thead><tr><?php foreach(['Su','Mo','Tu','We','Th','Fr','Sa'] as $d): ?><th><?= $d ?></th><?php endforeach; ?></tr></thead>
                <tbody>
                <?php
                $cell=0;$rows=ceil(($firstDow+$daysInMonth)/7);
                for($row=0;$row<$rows;$row++){echo '<tr>';
                    for($col=0;$col<7;$col++,$cell++){
                        $dayNum=$cell-$firstDow+1;
                        $isValid=$dayNum>=1&&$dayNum<=$daysInMonth;
                        $dateStr=$isValid?sprintf('%04d-%02d-%02d',$year,$month,$dayNum):'';
                        $hasClass=$isValid&&!empty($classesByDate[$dateStr]);
                        $isToday=$dateStr===$today;
                        $isSel=$dateStr===$selectedDate;
                        $cls='cal-day'.(!$isValid?' other-month':'').($hasClass?' has-class':'').($isToday?' today':'').($isSel?' selected':'');
                        echo '<td>';
                        if($isValid) echo '<a href="classes.php?year='.$year.'&month='.$month.($typeFilter ? '&type='.urlencode($typeFilter) : '').'&date='.$dateStr.'" class="'.htmlspecialchars($cls).'" style="text-decoration:none;color:inherit">'.$dayNum.'</a>';
                        else echo '<span class="'.$cls.'"></span>';
                        echo '</td>';
                    }
                echo '</tr>';}
                ?>
                </tbody>
            </table>
            <div style="display:flex;gap:16px;margin-top:14px;padding-top:12px;border-top:.5px solid var(--border);font-size:11px;color:var(--ink-soft)">
                <span style="display:flex;align-items:center;gap:5px"><span style="width:8px;height:8px;border-radius:50%;background:var(--brand);display:inline-block"></span> Has classes</span>
                <span style="display:flex;align-items:center;gap:5px"><span style="width:8px;height:8px;border-radius:2px;border:1.5px solid var(--brand);display:inline-block"></span> Today</span>
            </div>
        </div>
    </div>

    <div>
        <?php if($selectedDate&&!empty($classesByDate[$selectedDate])): ?>
        <div style="font-size:14px;font-weight:500;margin-bottom:16px"><?= date('l, d F Y',strtotime($selectedDate)) ?></div>
        <?php foreach($classesByDate[$selectedDate] as $c):
            $booked=in_array($c['id'],$userBookedIds);
            $spotsLeft=$c['capacity']-$c['booked_count'];
            $isFull=$spotsLeft<=0;
        ?>
        <div class="sf-class-item">
            <div class="sf-class-time">
                <div class="sf-class-time-val"><?= date('H:i',strtotime($c['start_time'])) ?></div>
                <div class="sf-class-time-dur"><?= $c['duration_min'] ?>m</div>
            </div>
            <div class="sf-class-info" style="flex:1">
                <h4><?= htmlspecialchars($c['name']) ?> <span class="badge-<?= $c['type'] ?>"><?= strtoupper($c['type']) ?></span></h4>
                <p><?= htmlspecialchars($c['instructor']) ?> &middot; <?= htmlspecialchars($c['venue']) ?></p>
                <div class="sf-class-spots" style="color:<?= $isFull ? 'var(--brand)' : ($spotsLeft<=5 ? '#b45309' : '#15803d') ?>">
                    <?= $isFull ? 'Class full' : ($spotsLeft<=5 ? 'Only '.$spotsLeft.' spots left' : $spotsLeft.'/'.$c['capacity'].' spots available') ?>
                </div>
                <div style="font-size:11px;color:var(--ink-soft);margin-top:6px">Free cancellation until 12 hours before class start.</div>
            </div>
            <div>
                <?php if($booked): ?>
                    <span class="sf-pill sf-pill-green" style="display:block;margin-bottom:6px">Booked</span>
                    <form method="POST" action="cancel_booking.php">
                        <input type="hidden" name="class_id" value="<?= $c['id'] ?>">
                        <input type="hidden" name="redirect" value="classes.php?year=<?= $year ?>&month=<?= $month ?>&date=<?= urlencode($selectedDate) ?><?= $typeFilter ? '&type='.urlencode($typeFilter) : '' ?>">
                        <button type="submit" class="sf-back-btn" style="padding:7px 14px;font-size:11px" data-confirm="Cancel your booking?">Cancel</button>
                    </form>
                <?php elseif(!isLoggedIn()): ?>
                    <a href="login.php" class="sf-nav-btn sf-nav-btn-solid" style="font-size:12px;text-decoration:none">Log in to book</a>
                <?php elseif($isFull): ?>
                    <button class="sf-nav-btn" style="background:none;border:1px solid var(--border-mid);color:var(--ink-soft);cursor:not-allowed" disabled>Full</button>
                <?php else: ?>
                    <a href="class_detail.php?id=<?= $c['id'] ?>" class="sf-nav-btn sf-nav-btn-solid" style="font-size:12px;text-decoration:none">Book</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php elseif($selectedDate): ?>
        <div style="padding:40px;text-align:center;color:var(--ink-soft);border:.5px solid var(--border);border-radius:var(--radius-lg)">No classes on <?= date('d F Y',strtotime($selectedDate)) ?>.</div>
        <?php else: ?>
        <div style="padding:40px;text-align:center;color:var(--ink-soft);border:.5px solid var(--border);border-radius:var(--radius-lg)">Select a date to see available classes.</div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
