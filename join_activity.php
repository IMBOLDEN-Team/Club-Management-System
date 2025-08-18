<?php
session_start();

// Only students can join
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: login.php');
    exit;
}

include 'index.php';

$studentId = (int)$_SESSION['user_id'];
$activityId = isset($_POST['club_activity_id']) ? (int)$_POST['club_activity_id'] : 0;

function redirect_with_message(string $msg, string $type = 'success'): void {
    $_SESSION['flash'] = ['message' => $msg, 'type' => $type];
    $target = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'student_dashboard.php';
    header('Location: ' . $target);
    exit;
}

if ($activityId <= 0) {
    redirect_with_message('Invalid activity.', 'error');
}

// Load activity and club
$sql = 'SELECT id, club_id, `start`, `end` FROM CLUB_ACTIVITY WHERE id = ?';
$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, 'i', $activityId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$activity = mysqli_fetch_assoc($res);

if (!$activity) {
    redirect_with_message('Activity not found.', 'error');
}

// Time guard: cannot join after activity ended
$now = new DateTime('now');
$end = new DateTime($activity['end']);
if ($now >= $end) {
    redirect_with_message('This activity has ended.', 'error');
}

// Ensure student is member of the club (auto-enroll if not)
$clubId = (int)$activity['club_id'];
$checkMember = 'SELECT 1 FROM CLUB_PARTICIPANT WHERE student_id = ? AND club_id = ?';
$stmt = mysqli_prepare($connect, $checkMember);
mysqli_stmt_bind_param($stmt, 'ii', $studentId, $clubId);
mysqli_stmt_execute($stmt);
$memberRes = mysqli_stmt_get_result($stmt);

if (!mysqli_fetch_row($memberRes)) {
    $insertMember = 'INSERT INTO CLUB_PARTICIPANT (student_id, club_id, position) VALUES (?, ?, ?)';
    $pos = 'Member';
    $stmt = mysqli_prepare($connect, $insertMember);
    mysqli_stmt_bind_param($stmt, 'iis', $studentId, $clubId, $pos);
    if (!mysqli_stmt_execute($stmt)) {
        redirect_with_message('Failed to enroll into club.', 'error');
    }
}

// Join activity (idempotent)
$check = 'SELECT 1 FROM ACTIVITY_PARTICIPANT WHERE student_id = ? AND club_activity_id = ?';
$stmt = mysqli_prepare($connect, $check);
$stmt && mysqli_stmt_bind_param($stmt, 'ii', $studentId, $activityId);
$stmt && mysqli_stmt_execute($stmt);
$exists = $stmt ? mysqli_stmt_get_result($stmt) : false;

if ($exists && mysqli_fetch_row($exists)) {
    redirect_with_message('Already joined this activity.', 'success');
}

$insert = 'INSERT INTO ACTIVITY_PARTICIPANT (student_id, club_activity_id) VALUES (?, ?)';
$stmt = mysqli_prepare($connect, $insert);
mysqli_stmt_bind_param($stmt, 'ii', $studentId, $activityId);
if (mysqli_stmt_execute($stmt)) {
    redirect_with_message('Successfully joined the activity.');
}

redirect_with_message('Failed to join the activity.', 'error');
?>


