<?php
session_start();

// Google OAuth configuration
$google_client_id = '97160173587-te9kcnb230i0bjensibrmb26gv68ru9l.apps.googleusercontent.com';
$google_client_secret = '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';
$redirect_uri = 'http://localhost/Club-Management-System/google_auth_handler.php';

// Check if we have an authorization code from Google
if (!isset($_GET['code'])) {
    // No authorization code, redirect to login
    header('Location: login.php?error=no_auth_code');
    exit;
}

$auth_code = $_GET['code'];

try {
    // Exchange authorization code for access token
    $token_url = 'https://oauth2.googleapis.com/token';
    $token_data = [
        'client_id' => $google_client_id,
        'client_secret' => $google_client_secret,
        'code' => $auth_code,
        'grant_type' => 'authorization_code',
        'redirect_uri' => $redirect_uri
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $token_response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        throw new Exception('Failed to get access token. HTTP Code: ' . $http_code);
    }

    $token_info = json_decode($token_response, true);
    
    if (!isset($token_info['access_token'])) {
        throw new Exception('No access token received from Google');
    }

    $access_token = $token_info['access_token'];

    // Get user information from Google
    $user_info_url = 'https://www.googleapis.com/oauth2/v2/userinfo';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $user_info_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $access_token
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $user_response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        throw new Exception('Failed to get user info. HTTP Code: ' . $http_code);
    }

    $user_info = json_decode($user_response, true);
    
    if (!isset($user_info['id']) || !isset($user_info['email'])) {
        throw new Exception('Invalid user info received from Google');
    }

    // Include index.php to get database connection
    include "index.php";

    $google_id = $user_info['id'];
    $email = $user_info['email'];
    $name = $user_info['name'] ?? '';
    $picture = $user_info['picture'] ?? '';

    // Check if user already exists
    $check_query = "SELECT * FROM STUDENT WHERE google_id = ? OR email = ?";
    $check_stmt = mysqli_prepare($connect, $check_query);
    mysqli_stmt_bind_param($check_stmt, 'ss', $google_id, $email);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);

    if (mysqli_num_rows($check_result) > 0) {
        // User exists, log them in
        $existing_user = mysqli_fetch_assoc($check_result);
        
        // Update Google ID if it was missing
        if (empty($existing_user['google_id'])) {
            $update_query = "UPDATE STUDENT SET google_id = ? WHERE id = ?";
            $update_stmt = mysqli_prepare($connect, $update_query);
            mysqli_stmt_bind_param($update_stmt, 'si', $google_id, $existing_user['id']);
            mysqli_stmt_execute($update_stmt);
        }
        
        // Set session variables
        $_SESSION['user_id'] = $existing_user['id'];
        $_SESSION['username'] = $existing_user['username'];
        $_SESSION['user_type'] = 'student';
        $_SESSION['email'] = $existing_user['email'];
        $_SESSION['name'] = $existing_user['name'];
        
        mysqli_close($connect);
        
        // Redirect to student dashboard
        header('Location: student_dashboard.php?login=success');
        exit;
        
    } else {
        // Create new user
        $username = 'student_' . substr($google_id, 0, 8); // Generate username from Google ID
        
        // Check if username already exists and make it unique
        $username_check_query = "SELECT COUNT(*) as count FROM STUDENT WHERE username = ?";
        $username_check_stmt = mysqli_prepare($connect, $username_check_query);
        mysqli_stmt_bind_param($username_check_stmt, 's', $username);
        mysqli_stmt_execute($username_check_stmt);
        $username_check_result = mysqli_stmt_get_result($username_check_stmt);
        $username_count = mysqli_fetch_assoc($username_check_result)['count'];
        
        if ($username_count > 0) {
            $username = $username . '_' . $username_count;
        }
        
        // Insert new user
        $insert_query = "INSERT INTO STUDENT (username, google_id, email, name, created_date) VALUES (?, ?, ?, ?, NOW())";
        $insert_stmt = mysqli_prepare($connect, $insert_query);
        mysqli_stmt_bind_param($insert_stmt, 'ssss', $username, $google_id, $email, $name);
        
        if (mysqli_stmt_execute($insert_stmt)) {
            $new_user_id = mysqli_insert_id($connect);
            
            // Set session variables
            $_SESSION['user_id'] = $new_user_id;
            $_SESSION['username'] = $username;
            $_SESSION['user_type'] = 'student';
            $_SESSION['email'] = $email;
            $_SESSION['name'] = $name;
            
            mysqli_close($connect);
            
            // Redirect to student dashboard
            header('Location: student_dashboard.php?login=success&new_user=1');
            exit;
            
        } else {
            throw new Exception('Failed to create new user account');
        }
    }

} catch (Exception $e) {
    // Log error (in production, log to file)
    error_log('Google OAuth Error: ' . $e->getMessage());
    
    // Redirect back to login with error
    header('Location: login.php?error=oauth_failed&message=' . urlencode($e->getMessage()));
    exit;
}
?>
