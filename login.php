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
                            header("Location: admin_dashboard.php?login=success");
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
                            header("Location: clubber_dashboard.php?login=success");
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
                            header("Location: student_dashboard.php?login=success");
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
      background:
        linear-gradient(90deg, rgba(15,23,42,0.72) 0%, rgba(15,23,42,0.55) 35%, rgba(255,255,255,0.92) 65%, rgba(255,255,255,1) 100%),
        url('img/login_bg.png') left center/cover no-repeat,
        url('img/login_bg.jpg') left center/cover no-repeat,
        #ffffff;
    }

    /* Decorative left panel when login is active */
    .container.active::before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      width: 50%;
      height: 100%;
      border-top-left-radius: 15px;
      border-bottom-left-radius: 15px;
      background:
        url('img/login_bg.png') center/cover no-repeat,
        url('img/login_bg.jpg') center/cover no-repeat,
        linear-gradient(135deg, #0F172A, #1E293B);
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
      background: linear-gradient(to bottom right, #0F172A, #1E293B);
      color: #F1F5F9;
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
      transform: translateX(-100%);
      opacity: 0;
    }

    .container.active .page2 {
      transform: translateX(0);
      opacity: 1;
      /* keep 50% so left decorative area is visible */
      width: 50%;
    }

    /* Left info overlay */
    .left-info {
      position: absolute;
      left: 40px;
      top: 50%;
      transform: translateY(-50%);
      width: 40%;
      max-width: 420px;
      color: #F8FAFC;
      background: rgba(15, 23, 42, 0.35);
      padding: 20px 24px;
      border-radius: 12px;
      backdrop-filter: blur(4px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.15);
      display: none;
      z-index: 3;
    }
    .left-info h3 { margin: 0 0 8px 0; font-size: 22px; }
    .left-info p { margin: 0 0 12px 0; font-size: 14px; color: #E2E8F0; }
    .left-info ul { margin: 0; padding-left: 18px; font-size: 13px; color: #E2E8F0; }
    .left-info li { margin: 6px 0; }
    .left-info .badge { display:inline-block; background:#F59E0B; color:#0F172A; padding:4px 10px; border-radius:9999px; font-weight:600; font-size:12px; }
    .container.active .left-info { display: block; }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-100">

  <div id="container" class="container">
    <!-- Left informational overlay -->
    <div class="left-info">
      <img src="img/logo.png" alt="Logo" style="width:56px;height:56px;border-radius:12px;background:#ffffff;padding:6px;box-shadow:0 4px 12px rgba(0,0,0,0.2);display:block;margin-bottom:10px;" />
      <span class="badge">KPM Club Portal</span>
      <h3>Discover. Participate. Earn.</h3>
      <p>Join campus clubs, track your activities and collect merit points for your achievements.</p>
      <ul>
        <li>Secure login with Google or email</li>
        <li>One place for clubs, events and points</li>
        <li>Fast notifications and modern UI</li>
      </ul>
    </div>
    <!-- Page 1 -->
    <div class="page page1">
      <div class="bg-white text-[#0F172A] rounded-full w-16 h-16 flex items-center justify-center mb-6">
        🔥
      </div>
      <h1 class="text-3xl font-bold mb-2">Welcome Back!</h1>
      <p class="text-center text-sm mb-6">To stay connected with us please login with your personal info</p>
      <button id="show-login-btn" class="border border-white px-6 py-2 rounded-full hover:bg-white hover:text-[#0F172A] transition">
        SIGN IN
      </button>
    </div>

    <!-- Page 2 -->
    <div class="page page2">
      <h2 class="text-2xl font-bold text-[#0F172A] mb-6 text-center">Welcome</h2>
      <p class="text-gray-600 text-center mb-6">Login to your account to continue</p>

      <?php if ($error_message): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
          <?= htmlspecialchars($error_message) ?>
        </div>
      <?php endif; ?>

      <form method="POST" class="space-y-4">
        <input type="text" name="username" placeholder="Email..." required
               class="w-full px-4 py-3 border rounded-full focus:ring-2 focus:ring-amber-500">
        <input type="password" name="password" placeholder="Password..." required
               class="w-full px-4 py-3 border rounded-full focus:ring-2 focus:ring-amber-500">

        <div class="text-right">
                              <a href="#" class="text-sm text-amber-600 hover:underline">Need help?</a>
        </div>

        <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-[#0F172A] py-3 rounded-full font-semibold">
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
        Don’t have an account? <a href="signup.php" class="text-amber-600 hover:underline">Sign up</a>
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
