<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: login.php');
    exit;
}

include 'index.php';

function flash_and_back($msg, $type = 'success') {
    $_SESSION['flash'] = ['message' => $msg, 'type' => $type];
    $target = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'student_dashboard.php';
    header('Location: ' . $target);
    exit;
}

$studentId = (int)$_SESSION['user_id'];
$clubId = isset($_POST['club_id']) ? (int)$_POST['club_id'] : 0;
if ($clubId <= 0) {
    flash_and_back('Invalid club.', 'error');
}

// Ensure join request table exists
$create = "CREATE TABLE IF NOT EXISTS CLUB_JOIN_REQUEST (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    club_id INT NOT NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    requested_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    responded_at DATETIME NULL,
    UNIQUE KEY uniq_request (student_id, club_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
mysqli_query($connect, $create);

// Already a member?
$chk = mysqli_prepare($connect, 'SELECT 1 FROM CLUB_PARTICIPANT WHERE student_id = ? AND club_id = ?');
mysqli_stmt_bind_param($chk, 'ii', $studentId, $clubId);
mysqli_stmt_execute($chk);
if (mysqli_fetch_row(mysqli_stmt_get_result($chk))) {
    flash_and_back('You are already a member of this club.', 'success');
}

// Upsert request
$ins = mysqli_prepare($connect, 'INSERT INTO CLUB_JOIN_REQUEST (student_id, club_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE status = VALUES(status), requested_at = CURRENT_TIMESTAMP');
mysqli_stmt_bind_param($ins, 'ii', $studentId, $clubId);
if (mysqli_stmt_execute($ins)) {
    flash_and_back('Join request submitted. Waiting for approval.');
}

flash_and_back('Failed to submit request.', 'error');
?>


