<?php
session_start();

// Clubbers approve/reject
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'clubber') {
    header('Location: login.php');
    exit;
}

include 'index.php';

function back($msg, $type = 'success') {
    $_SESSION['flash'] = ['message' => $msg, 'type' => $type];
    $target = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'clubber_dashboard.php';
    header('Location: ' . $target);
    exit;
}

$clubberId = (int)$_SESSION['user_id'];

// Resolve club id for this clubber
$clubStmt = mysqli_prepare($connect, 'SELECT club_id FROM CLUBER WHERE id = ?');
mysqli_stmt_bind_param($clubStmt, 'i', $clubberId);
mysqli_stmt_execute($clubStmt);
$clubRes = mysqli_stmt_get_result($clubStmt);
$club = mysqli_fetch_assoc($clubRes);
if (!$club) back('Club not found for this account.', 'error');
$clubId = (int)$club['club_id'];

$requestId = isset($_POST['request_id']) ? (int)$_POST['request_id'] : 0;
$action = $_POST['action'] ?? '';
if ($requestId <= 0 || !in_array($action, ['approve','reject'], true)) back('Invalid request.', 'error');

// Ensure table exists
mysqli_query($connect, "CREATE TABLE IF NOT EXISTS CLUB_JOIN_REQUEST (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    club_id INT NOT NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    requested_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    responded_at DATETIME NULL,
    UNIQUE KEY uniq_request (student_id, club_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Verify request belongs to this club
$rs = mysqli_prepare($connect, 'SELECT student_id, status FROM CLUB_JOIN_REQUEST WHERE id = ? AND club_id = ?');
mysqli_stmt_bind_param($rs, 'ii', $requestId, $clubId);
mysqli_stmt_execute($rs);
$r = mysqli_fetch_assoc(mysqli_stmt_get_result($rs));
if (!$r || $r['status'] !== 'pending') back('Request not found or already processed.', 'error');

if ($action === 'approve') {
    // Add to club participants idempotently
    $studentId = (int)$r['student_id'];
    $ins = mysqli_prepare($connect, 'INSERT IGNORE INTO CLUB_PARTICIPANT (student_id, club_id, position) VALUES (?, ?, ?)');
    $pos = 'Member';
    mysqli_stmt_bind_param($ins, 'iis', $studentId, $clubId, $pos);
    if (!mysqli_stmt_execute($ins)) back('Failed to add member.', 'error');
    $st = 'approved';
} else {
    $st = 'rejected';
}

$upd = mysqli_prepare($connect, "UPDATE CLUB_JOIN_REQUEST SET status = ?, responded_at = NOW() WHERE id = ?");
mysqli_stmt_bind_param($upd, 'si', $st, $requestId);
mysqli_stmt_execute($upd);

back('Request ' . $st . '.');
?>


