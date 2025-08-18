<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: login.php');
    exit;
}

include 'index.php';

$studentId = (int)$_SESSION['user_id'];
$activityId = isset($_POST['club_activity_id']) ? (int)$_POST['club_activity_id'] : 0;

function back_with(string $msg, string $type = 'success') {
    $_SESSION['flash'] = ['message' => $msg, 'type' => $type];
    $target = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'student_dashboard.php';
    header('Location: ' . $target);
    exit;
}

if ($activityId <= 0) {
    back_with('Invalid activity.', 'error');
}

// Check time rule: cannot leave after start
$sql = 'SELECT `start` FROM CLUB_ACTIVITY WHERE id = ?';
$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, 'i', $activityId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);

if (!$row) {
    back_with('Activity not found.', 'error');
}

$now = new DateTime('now');
$start = new DateTime($row['start']);
if ($now >= $start) {
    back_with('You cannot leave after the activity has started.', 'error');
}

$del = 'DELETE FROM ACTIVITY_PARTICIPANT WHERE student_id = ? AND club_activity_id = ?';
$stmt = mysqli_prepare($connect, $del);
mysqli_stmt_bind_param($stmt, 'ii', $studentId, $activityId);
if (mysqli_stmt_execute($stmt)) {
    back_with('You have left the activity.');
}

back_with('Failed to leave the activity.', 'error');
?>


