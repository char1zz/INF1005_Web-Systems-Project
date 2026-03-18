<?php
// book_class.php — POST handler
require_once 'config/db.php';
require_once 'includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('classes.php');

$classId = isset($_POST['class_id']) ? (int)$_POST['class_id'] : 0;
$userId  = (int)$_SESSION['user_id'];

// Fetch class
$stmt = $pdo->prepare("
    SELECT c.*,
           COUNT(CASE WHEN b.status = 'confirmed' THEN 1 END) AS booked_count
    FROM classes c
    LEFT JOIN bookings b ON b.class_id = c.id
    WHERE c.id = ?
    GROUP BY c.id
");
$stmt->execute([$classId]);
$class = $stmt->fetch();

if (!$class) {
    setFlash('error', 'Class not found.');
    redirect('classes.php');
}

// Check already booked
$chk = $pdo->prepare("SELECT id FROM bookings WHERE user_id = ? AND class_id = ? AND status = 'confirmed'");
$chk->execute([$userId, $classId]);
if ($chk->fetch()) {
    setFlash('warning', 'You have already booked this class.');
    redirect("class_detail.php?id=$classId");
}

// Check capacity
$spotsLeft = $class['capacity'] - $class['booked_count'];
if ($spotsLeft <= 0) {
    setFlash('error', 'Sorry, this class is now full.');
    redirect("class_detail.php?id=$classId");
}

// Insert booking
$ins = $pdo->prepare("INSERT INTO bookings (user_id, class_id, status) VALUES (?, ?, 'confirmed')");
$ins->execute([$userId, $classId]);

setFlash('success', 'Booking confirmed! See you in ' . $class['name'] . '.');
redirect('my_bookings.php');
