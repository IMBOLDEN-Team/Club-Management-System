<?php
session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'student') {
    header("Location: login.php");
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: club_list.php");
    exit();
}

// Get club_id from POST
$club_id = isset($_POST['club_id']) ? (int)$_POST['club_id'] : 0;
if ($club_id <= 0) {
    $_SESSION['flash'] = ['message' => 'Invalid club ID.', 'type' => 'error'];
    header("Location: club_list.php");
    exit();
}

// Include database connection
include "index.php";

// Get student_id from session
$student_id = $_SESSION['user_id'];

// Ensure CLUB_JOIN_REQUEST table exists
$create_table_query = "CREATE TABLE IF NOT EXISTS CLUB_JOIN_REQUEST (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    club_id INT NOT NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    requested_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    responded_at DATETIME NULL,
    UNIQUE KEY uniq_request (student_id, club_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!mysqli_query($connect, $create_table_query)) {
    $_SESSION['flash'] = ['message' => 'Database error: Could not create table.', 'type' => 'error'];
    header("Location: club_list.php");
    exit();
}

// Check if club exists
$club_check_query = "SELECT id, name FROM CLUB WHERE id = ?";
$club_check_stmt = mysqli_prepare($connect, $club_check_query);
mysqli_stmt_bind_param($club_check_stmt, 'i', $club_id);
mysqli_stmt_execute($club_check_stmt);
$club_result = mysqli_stmt_get_result($club_check_stmt);

if (mysqli_num_rows($club_result) === 0) {
    $_SESSION['flash'] = ['message' => 'Club not found.', 'type' => 'error'];
    header("Location: club_list.php");
    exit();
}

$club = mysqli_fetch_assoc($club_result);
$club_name = $club['name'];

// Check if student is already a member
$member_check_query = "SELECT * FROM CLUB_PARTICIPANT WHERE student_id = ? AND club_id = ?";
$member_check_stmt = mysqli_prepare($connect, $member_check_query);
mysqli_stmt_bind_param($member_check_stmt, 'ii', $student_id, $club_id);
mysqli_stmt_execute($member_check_stmt);
$member_result = mysqli_stmt_get_result($member_check_stmt);

if (mysqli_num_rows($member_result) > 0) {
    $_SESSION['flash'] = ['message' => 'You are already a member of this club.', 'type' => 'error'];
    header("Location: club_list.php");
    exit();
}

// Check if there's already a pending request
$pending_check_query = "SELECT * FROM CLUB_JOIN_REQUEST WHERE student_id = ? AND club_id = ? AND status = 'pending'";
$pending_check_stmt = mysqli_prepare($connect, $pending_check_query);
mysqli_stmt_bind_param($pending_check_stmt, 'ii', $student_id, $club_id);
mysqli_stmt_execute($pending_check_stmt);
$pending_result = mysqli_stmt_get_result($pending_check_stmt);

if (mysqli_num_rows($pending_result) > 0) {
    $_SESSION['flash'] = ['message' => 'You already have a pending request for this club.', 'type' => 'error'];
    header("Location: club_list.php");
    exit();
}

// Insert new join request using INSERT IGNORE to avoid duplicates
$insert_query = "INSERT IGNORE INTO CLUB_JOIN_REQUEST (student_id, club_id, status) VALUES (?, ?, 'pending')";
$insert_stmt = mysqli_prepare($connect, $insert_query);
mysqli_stmt_bind_param($insert_stmt, 'ii', $student_id, $club_id);

if (mysqli_stmt_execute($insert_stmt)) {
    if (mysqli_affected_rows($connect) > 0) {
        $_SESSION['flash'] = ['message' => "Join request sent successfully for '$club_name'. Waiting for approval.", 'type' => 'success'];
    } else {
        $_SESSION['flash'] = ['message' => 'Request already exists or could not be processed.', 'type' => 'error'];
    }
} else {
    $_SESSION['flash'] = ['message' => 'Database error: Could not submit join request.', 'type' => 'error'];
}

// Redirect back to club list
header("Location: club_list.php");
exit();
?>
