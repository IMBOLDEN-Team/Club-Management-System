<?php
session_start();

// Google OAuth config
$google_client_id = '97160173587-te9kcnb230i0bjensibrmb26gv68ru9l.apps.googleusercontent.com';
$google_client_secret = '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';
$redirect_uri = 'http://localhost/Club-Management-System/google_auth_handler.php';

$google_auth_url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
    'client_id' => $google_client_id,
    'redirect_uri' => $redirect_uri,
    'scope' => 'email profile',
    'response_type' => 'code',
    'access_type' => 'offline'
]);

$error_message = '';

// DB connect
include "index.php";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error_message = 'Please fill in all fields.';
    } else {
        // ADMIN check
        $admin_query = "SELECT * FROM ADMIN WHERE username = ? AND password = ?";
        $admin_stmt = mysqli_prepare($connect, $admin_query);
        mysqli_stmt_bind_param($admin_stmt, 'ss', $username, $password);
        mysqli_stmt_execute($admin_stmt);
        $admin_result = mysqli_stmt_get_result($admin_stmt);

        if (mysqli_num_rows($admin_result) > 0) {
            $user = mysqli_fetch_assoc($admin_result);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = 'admin';
            header("Location: admin_dashboard.php");
            exit;
        }

        // CLUBBER check
        $clubber_query = "SELECT c.*, cl.name as club_name 
                          FROM CLUBER c 
                          JOIN CLUB cl ON c.club_id = cl.id 
                          WHERE c.username = ? AND c.password = ?";
        $clubber_stmt = mysqli_prepare($connect, $clubber_query);
        mysqli_stmt_bind_param($clubber_stmt, 'ss', $username, $password);
        mysqli_stmt_execute($clubber_stmt);
        $clubber_result = mysqli_stmt_get_result($clubber_stmt);

        if (mysqli_num_rows($clubber_result) > 0) {
            $user = mysqli_fetch_assoc($clubber_result);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = 'clubber';
            $_SESSION['club_id'] = $user['club_id'];
            $_SESSION['club_name'] = $user['club_name'];
            header("Location: clubber_dashboard.php");
            exit;
        }

        // STUDENT check
        $student_query = "SELECT * FROM STUDENT WHERE email = ? 
                          AND password IS NOT NULL 
                          AND password != '' 
                          AND password = ?";
        $student_stmt = mysqli_prepare($connect, $student_query);
        mysqli_stmt_bind_param($student_stmt, 'ss', $username, $password);
        mysqli_stmt_execute($student_stmt);
        $student_result = mysqli_stmt_get_result($student_stmt);

        if (mysqli_num_rows($student_result) > 0) {
            $user = mysqli_fetch_assoc($student_result);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['email'];
            $_SESSION['user_type'] = 'student';
            $_SESSION['student_name'] = $user['name'];
            header("Location: student_dashboard.php");
            exit;
        }

        $error_message = 'Invalid username/email or password.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Club Management System</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .container {
      position: relative;
      display: flex;
      width: 900px;
      height: 500px;
      overflow: hidden;
      border-radius: 15px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.2);
      background: white;
    }

    .page {
      position: absolute;
      top: 0;
      height: 100%;
      width: 50%;
      transition: transform 0.8s ease, opacity 0.8s ease;
    }

    /* PAGE 1 (WELCOME) */
    .page1 {
      left: 0;
      width: 100%;
      background: linear-gradient(to bottom right, #16a34a, #065f46);
      color: white;
      z-index: 1;
      transform: translateX(0);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      padding: 40px;
    }

    /* PAGE 2 (LOGIN) */
    .page2 {
      right: 0;
      background: white;
      z-index: 2;
      transform: translateX(-100%);
      opacity: 0;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 40px;
    }

    /* ACTIVE STATE */
    .container.active .page1 {
      transform: translateX(-50%);
    }

    .container.active .page2 {
      transform: translateX(0);
      opacity: 1;
    }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-100">

  <div id="container" class="container">
    <!-- Page 1 -->
    <div class="page page1">
      <div class="bg-white text-green-700 rounded-full w-16 h-16 flex items-center justify-center mb-6">
        🔥
      </div>
      <h1 class="text-3xl font-bold mb-2">Welcome Back!</h1>
      <p class="text-center text-sm mb-6">To stay connected with us please login with your personal info</p>
      <button id="show-login-btn" class="border border-white px-6 py-2 rounded-full hover:bg-white hover:text-green-700 transition">
        SIGN IN
      </button>
    </div>

    <!-- Page 2 -->
    <div class="page page2">
      <h2 class="text-2xl font-bold text-green-700 mb-6 text-center">Welcome</h2>
      <p class="text-gray-600 text-center mb-6">Login to your account to continue</p>

      <?php if ($error_message): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
          <?= htmlspecialchars($error_message) ?>
        </div>
      <?php endif; ?>

      <form method="POST" class="space-y-4">
        <input type="text" name="username" placeholder="Email..." required
               class="w-full px-4 py-3 border rounded-full focus:ring-2 focus:ring-green-600">
        <input type="password" name="password" placeholder="Password..." required
               class="w-full px-4 py-3 border rounded-full focus:ring-2 focus:ring-green-600">

        <div class="text-right">
          <a href="#" class="text-sm text-green-600 hover:underline">Forgot your password?</a>
        </div>

        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-full">
          LOG IN
        </button>
      </form>

      <!-- Google Sign In -->
      <a href="<?= htmlspecialchars($google_auth_url) ?>" 
         class="mt-4 w-full flex items-center justify-center border px-6 py-3 rounded-full text-gray-700 hover:bg-gray-100">
        <svg class="w-5 h-5 mr-3" viewBox="0 0 24 24">
          <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
          <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
          <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
          <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.8-2.43 2.66-4.53 5.16-4.53z"/>
        </svg>
        Sign in with Google
      </a>

      <p class="mt-6 text-center text-sm text-gray-600">
        Don’t have an account? <a href="signup.php" class="text-green-600 hover:underline">Sign up</a>
      </p>
    </div>
  </div>

  <script>
    const container = document.getElementById('container');
    const showLoginBtn = document.getElementById('show-login-btn');

    showLoginBtn.addEventListener('click', () => {
      container.classList.add('active');
    });
  </script>
</body>
</html>
