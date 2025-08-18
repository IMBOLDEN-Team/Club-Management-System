<?php
session_start();

// Google OAuth configuration
$google_client_id = '97160173587-te9kcnb230i0bjensibrmb26gv68ru9l.apps.googleusercontent.com';
$google_client_secret = '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';
$redirect_uri = 'http://localhost/Club-Management-System/google_auth_handler.php';

// Google OAuth URL
$google_auth_url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
    'client_id' => $google_client_id,
    'redirect_uri' => $redirect_uri,
    'scope' => 'email profile',
    'response_type' => 'code',
    'access_type' => 'offline'
]);

$error_message = '';
$success_message = '';

// Include database connection once at the top
include "index.php";



// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if it's an admin creation request
    if (isset($_POST['action']) && $_POST['action'] === 'create_admin') {
        $admin_username = trim($_POST['admin_username'] ?? '');
        $admin_password = trim($_POST['admin_password'] ?? '');
        $admin_secret_key = trim($_POST['admin_secret_key'] ?? '');
        
        if (empty($admin_username) || empty($admin_password) || empty($admin_secret_key)) {
            $error_message = 'Please fill in all admin creation fields.';
        } elseif ($admin_secret_key !== 'ADMIN2024') { // Secret key to prevent unauthorized admin creation
            $error_message = 'Invalid secret key for admin creation.';
        } else {
            // Check if admin username already exists
            $check_query = "SELECT * FROM ADMIN WHERE username = ?";
            $check_stmt = mysqli_prepare($connect, $check_query);
            mysqli_stmt_bind_param($check_stmt, 's', $admin_username);
            mysqli_stmt_execute($check_stmt);
            $check_result = mysqli_stmt_get_result($check_stmt);
            
            if (mysqli_num_rows($check_result) > 0) {
                $error_message = 'Admin username already exists.';
            } else {
                // Create new admin - following models.sql structure exactly
                $create_query = "INSERT INTO ADMIN (username, password) VALUES (?, ?)";
                $create_stmt = mysqli_prepare($connect, $create_query);
                mysqli_stmt_bind_param($create_stmt, 'ss', $admin_username, $admin_password);
                
                if (mysqli_stmt_execute($create_stmt)) {
                    $success_message = 'Admin created successfully! You can now login.';
                } else {
                    $error_message = 'Error creating admin: ' . mysqli_error($connect);
                }
            }
        }
    } else {
        // Regular login logic
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $user_type = $_POST['user_type'] ?? '';

        if (empty($username) || empty($password)) {
            $error_message = 'Please fill in all fields.';
        } else {
            if ($user_type === 'admin') {
                $query = "SELECT * FROM ADMIN WHERE username = ? AND password = ?";
            } elseif ($user_type === 'clubber') {
                $query = "SELECT c.*, cl.name as club_name FROM CLUBER c JOIN CLUB cl ON c.club_id = cl.id WHERE c.username = ? AND c.password = ?";
            }

            $stmt = mysqli_prepare($connect, $query);
            mysqli_stmt_bind_param($stmt, 'ss', $username, $password);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) > 0) {
                $user = mysqli_fetch_assoc($result);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_type'] = $user_type;
                
                if ($user_type === 'clubber') {
                    $_SESSION['club_id'] = $user['club_id'];
                    $_SESSION['club_name'] = $user['club_name'];
                }
                
                $success_message = 'Login successful! Redirecting...';
                
                // Redirect based on user type
                if ($user_type === 'admin') {
                    header("Refresh: 2; URL=admin_dashboard.php");
                } elseif ($user_type === 'clubber') {
                    header("Refresh: 2; URL=clubber_dashboard.php");
                } else {
                    header("Refresh: 2; URL=home.php");
                }
            } else {
                // Debug information for admin login issues
                if ($user_type === 'admin') {
                    // Check if admin exists with just username
                    $debug_query = "SELECT * FROM ADMIN WHERE username = ?";
                    $debug_stmt = mysqli_prepare($connect, $debug_query);
                    mysqli_stmt_bind_param($debug_stmt, 's', $username);
                    mysqli_stmt_execute($debug_stmt);
                    $debug_result = mysqli_stmt_get_result($debug_stmt);
                    
                    if (mysqli_num_rows($debug_result) > 0) {
                        $error_message = 'Password is incorrect for admin user.';
                    } else {
                        $error_message = 'Admin username does not exist.';
                    }
                } else {
                    $error_message = 'Invalid username or password.';
                }
            }
        }
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
    <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>
<body class="bg-gradient-to-br from-[#F1F5F9] via-[#E2E8F0] to-[#CBD5E1] min-h-screen">
    <!-- Header -->
    <header class="bg-[#0F172A] text-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex-shrink-0">
                    <a href="home.php">
                        <img src="img/logo.png" alt="KPM Logo" class="h-16 w-16 object-contain md:h-20 md:w-20">
                    </a>
                </div>
                <nav class="hidden md:flex space-x-1 lg:space-x-4">
                    <a href="club_list.php" class="px-3 py-2 rounded-md text-sm font-medium hover:text-[#F59E0B] transition-colors duration-300">Club</a>
                    <a href="#" class="px-3 py-2 rounded-md text-sm font-medium hover:text-[#F59E0B] transition-colors duration-300">Activity</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="min-h-screen flex items-center justify-center px-4 py-16">
        <!-- Background Elements -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-gradient-to-r from-blue-200/20 to-purple-200/20 rounded-full blur-3xl animate-float"></div>
            <div class="absolute -bottom-32 -right-32 w-80 h-80 bg-gradient-to-r from-orange-200/20 to-pink-200/20 rounded-full blur-3xl animate-float-delayed"></div>
            <div class="absolute top-1/2 left-1/4 w-64 h-64 bg-gradient-to-r from-green-200/15 to-blue-200/15 rounded-full blur-2xl animate-float-slow"></div>
        </div>

        <div class="max-w-md w-full mx-auto relative z-10">
            <!-- Login Card -->
            <div class="bg-white/90 backdrop-blur-sm p-8 rounded-3xl shadow-2xl border border-white/50 relative overflow-hidden">
                <!-- Card Background Pattern -->
                <div class="absolute inset-0 bg-gradient-to-br from-[#F59E0B]/5 via-transparent to-[#EF4444]/5 opacity-50"></div>
                <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-bl from-[#F59E0B]/10 to-transparent rounded-full blur-2xl"></div>
                
                <div class="relative z-10">
                    <!-- Header -->
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-[#F59E0B] to-[#EF4444] rounded-full mb-6">
                            <span class="text-2xl">🔐</span>
                        </div>
                        <h1 class="text-3xl font-bold bg-gradient-to-r from-[#0F172A] to-[#334155] bg-clip-text text-transparent mb-2">
                            Welcome Back
                        </h1>
                        <p class="text-[#64748B]">Sign in to your account</p>
                        <div class="w-16 h-1 bg-gradient-to-r from-[#F59E0B] to-[#EF4444] mx-auto rounded-full mt-4"></div>
                    </div>

                    <!-- Error/Success Messages -->
                    <?php if ($error_message): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 animate-fade-in">
                            <?= htmlspecialchars($error_message) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success_message): ?>
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 animate-fade-in">
                            <?= htmlspecialchars($success_message) ?>
                        </div>
                    <?php endif; ?>



                    <!-- Student Google Sign-In -->
                    <div class="mb-8">
                        <div class="text-center mb-4">
                            <h3 class="text-lg font-semibold text-[#0F172A] mb-2">Student Login</h3>
                            <p class="text-sm text-[#64748B] mb-4">Sign in with your Google account</p>
                        </div>
                        
                        <a href="<?= htmlspecialchars($google_auth_url) ?>" 
                           class="w-full flex items-center justify-center bg-white border-2 border-gray-300 text-gray-700 px-6 py-3 rounded-xl font-semibold hover:bg-gray-50 hover:border-gray-400 transition-all duration-300 transform hover:scale-105 active:scale-95 shadow-lg">
                            <svg class="w-5 h-5 mr-3" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.8-2.43 2.66-4.53 5.16-4.53z"/>
                            </svg>
                            Sign in with Google
                        </a>
                    </div>

                    <!-- Divider -->
                    <div class="relative mb-8">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-500">or</span>
                        </div>
                    </div>

                    <!-- Admin/Clubber Login Form -->
                    <div>
                        <div class="text-center mb-4">
                            <h3 class="text-lg font-semibold text-[#0F172A] mb-2">Admin/Clubber Login</h3>
                            <p class="text-sm text-[#64748B]">Sign in with your credentials</p>
                        </div>

                        <form method="POST" class="space-y-4">
                            <!-- User Type Selection -->
                            <div>
                                <label class="block text-sm font-medium text-[#0F172A] mb-2">User Type</label>
                                <select name="user_type" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#F59E0B] focus:border-transparent transition-all duration-300 bg-white">
                                    <option value="">Select user type</option>
                                    <option value="admin">Admin</option>
                                    <option value="clubber">Clubber</option>
                                </select>
                            </div>

                            <!-- Username -->
                            <div>
                                <label class="block text-sm font-medium text-[#0F172A] mb-2">Username</label>
                                <input type="text" name="username" required 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#F59E0B] focus:border-transparent transition-all duration-300"
                                       placeholder="Enter your username">
                            </div>

                            <!-- Password -->
                            <div>
                                <label class="block text-sm font-medium text-[#0F172A] mb-2">Password</label>
                                <input type="password" name="password" required 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#F59E0B] focus:border-transparent transition-all duration-300"
                                       placeholder="Enter your password">
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" 
                                    class="w-full bg-gradient-to-r from-[#0F172A] to-[#334155] text-white px-6 py-3 rounded-xl font-semibold hover:from-[#F59E0B] hover:to-[#EF4444] transition-all duration-300 transform hover:scale-105 active:scale-95 shadow-lg hover:shadow-xl relative overflow-hidden">
                                <span class="relative z-10">Sign In</span>
                                <div class="absolute inset-0 bg-white/20 transform scale-x-0 hover:scale-x-100 transition-transform duration-500 origin-left"></div>
                            </button>
                        </form>
                    </div>

                    <!-- Footer -->
                    <div class="text-center mt-8 pt-6 border-t border-gray-200">
                        <p class="text-sm text-[#64748B]">
                            Don't have an account? 
                            <a href="home.php" class="text-[#F59E0B] hover:text-[#EF4444] font-medium transition-colors duration-300">
                                Contact administrator
                            </a>
                        </p>
                    </div>

                    <!-- Admin Creation Section -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <div class="text-center mb-4">
                            <h3 class="text-lg font-semibold text-[#0F172A] mb-2">Create Admin Account</h3>
                            <p class="text-sm text-[#64748B] mb-4">Create a new admin account (requires secret key)</p>
                        </div>

                        <form method="POST" class="space-y-4">
                            <input type="hidden" name="action" value="create_admin">
                            
                            <!-- Admin Username -->
                            <div>
                                <label class="block text-sm font-medium text-[#0F172A] mb-2">Username</label>
                                <input type="text" name="admin_username" required 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#F59E0B] focus:border-transparent transition-all duration-300"
                                       placeholder="Enter admin username">
                            </div>

                            <!-- Admin Password -->
                            <div>
                                <label class="block text-sm font-medium text-[#0F172A] mb-2">Password</label>
                                <input type="password" name="admin_password" required 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#F59E0B] focus:border-transparent transition-all duration-300"
                                       placeholder="Enter admin password">
                            </div>

                            <!-- Secret Key -->
                            <div>
                                <label class="block text-sm font-medium text-[#0F172A] mb-2">Secret Key</label>
                                <input type="password" name="admin_secret_key" required 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#F59E0B] focus:border-transparent transition-all duration-300"
                                       placeholder="Enter secret key (ADMIN2024)">
                                <p class="text-xs text-[#64748B] mt-1">Secret key: ADMIN2024</p>
                            </div>

                            <!-- Create Admin Button -->
                            <button type="submit" 
                                    class="w-full bg-gradient-to-r from-[#F59E0B] to-[#EF4444] text-white px-6 py-3 rounded-xl font-semibold hover:from-[#EF4444] hover:to-[#F59E0B] transition-all duration-300 transform hover:scale-105 active:scale-95 shadow-lg hover:shadow-xl relative overflow-hidden">
                                <span class="relative z-10">Create Admin Account</span>
                                <div class="absolute inset-0 bg-white/20 transform scale-x-0 hover:scale-x-100 transition-transform duration-500 origin-left"></div>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-20px) rotate(1deg); }
            66% { transform: translateY(10px) rotate(-1deg); }
        }

        @keyframes float-delayed {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(15px) rotate(-1deg); }
            66% { transform: translateY(-10px) rotate(1deg); }
        }

        @keyframes float-slow {
            0%, 100% { transform: translateY(0px) translateX(0px); }
            50% { transform: translateY(-30px) translateX(20px); }
        }

        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        .animate-float-delayed {
            animation: float-delayed 8s ease-in-out infinite;
        }

        .animate-float-slow {
            animation: float-slow 10s ease-in-out infinite;
        }

        .animate-fade-in {
            animation: fade-in 0.5s ease-out;
        }
    </style>
</body>
</html>
