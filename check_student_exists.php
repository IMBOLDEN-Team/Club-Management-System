<?php
require_once __DIR__ . '/config/config.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get the email from POST data
$email = trim($_POST['email'] ?? '');

if (empty($email)) {
    http_response_code(400);
    echo json_encode(['error' => 'Email is required']);
    exit;
}

try {
    // Check if student exists
    $query = "SELECT id, name, email FROM STUDENT WHERE email = ?";
    $stmt = mysqli_prepare($connect, $query);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $student = mysqli_fetch_assoc($result);
        echo json_encode([
            'exists' => true,
            'student' => [
                'id' => $student['id'],
                'name' => $student['name'],
                'email' => $student['email']
            ]
        ]);
    } else {
        echo json_encode([
            'exists' => false,
            'message' => 'Student not found with this email'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
