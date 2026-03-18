<?php
require_once 'config/db.php';
require_once 'includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('my_bookings.php');

$classId = isset($_POST['class_id']) ? (int)$_POST['class_id'] : 0;
$userId  = (int)$_SESSION['user_id'];
$redirectTo = $_POST['redirect'] ?? 'my_bookings.php';
$allowed = ['my_bookings.php', 'classes.php', 'class_detail.php'];
if (!in_array(basename(parse_url($redirectTo, PHP_URL_PATH) ?? ''), $allowed, true)) {
    $redirectTo = 'my_bookings.php';
}

$stmt = $pdo->prepare("SELECT c.name, c.class_date, c.start_time FROM bookings b JOIN classes c ON c.id = b.class_id WHERE b.user_id = ? AND b.class_id = ? AND b.status = 'confirmed' LIMIT 1");
$stmt->execute([$userId, $classId]);
$booking = $stmt->fetch();

if (!$booking) {
    setFlash('error', 'Booking not found or already cancelled.');
    redirect($redirectTo);
}

$classStart = strtotime($booking['class_date'] . ' ' . $booking['start_time']);
$cutoff = strtotime('+12 hours');
if ($classStart <= $cutoff) {
    setFlash('warning', 'This booking can no longer be cancelled online. Cancellations must be made at least 12 hours before class start time.');
    redirect($redirectTo);
}

$update = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE user_id = ? AND class_id = ? AND status = 'confirmed'");
$update->execute([$userId, $classId]);
setFlash('success', 'Booking cancelled successfully. Your slot is now open for another member.');
redirect($redirectTo);
