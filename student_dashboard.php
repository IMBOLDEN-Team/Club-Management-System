<?php
// Include the comprehensive error handling system
require_once 'config/config.php';
require_once __DIR__ . '/components/breadcrumb.php';

// Require student authentication
require_role('student');

// Get student information with error handling
try {
    $student_id = getCurrentUserId();
    $student_email = $_SESSION['username'] ?? '';
    $student_name = $_SESSION['student_name'] ?? 'Student';

    // Clubs joined with error handling
    $memberships_result = db_query(
        "SELECT c.id AS club_id, c.name AS club_name FROM CLUB c
         INNER JOIN CLUB_PARTICIPANT cp ON c.id = cp.club_id
         WHERE cp.student_id = ? ORDER BY c.name",
        [$student_id]
    );

    if ($memberships_result === false) {
        throwSystemError("Failed to fetch club memberships", "Database Error");
    }

    $member_count = $memberships_result ? mysqli_num_rows($memberships_result) : 0;

    // Activities participated + totals with error handling
    $stats_result = db_query(
        "SELECT COUNT(*) AS total_activities, COALESCE(SUM(ca.merit_point),0) AS total_merit
         FROM CLUB_ACTIVITY ca
         INNER JOIN ACTIVITY_PARTICIPANT ap ON ca.id = ap.club_activity_id
         WHERE ap.student_id = ?",
        [$student_id]
    );

    if ($stats_result === false) {
        throwSystemError("Failed to fetch activity statistics", "Database Error");
    }

    $total_activities = 0;
    $total_merit = 0;
    if ($stats_result) {
        $row = mysqli_fetch_assoc($stats_result);
        $total_activities = (int)($row['total_activities'] ?? 0);
        $total_merit = (int)($row['total_merit'] ?? 0);
    }

    // Activities list (latest 10) with error handling
    $activities_result = db_query(
        "SELECT ca.name AS activity_name, ca.start, ca.end, ca.merit_point, c.name AS club_name
         FROM CLUB_ACTIVITY ca
         INNER JOIN ACTIVITY_PARTICIPANT ap ON ca.id = ap.club_activity_id
         INNER JOIN CLUB c ON ca.club_id = c.id
         WHERE ap.student_id = ?
         ORDER BY ca.start DESC
         LIMIT 10",
        [$student_id]
    );

    if ($activities_result === false) {
        throwSystemError("Failed to fetch activities", "Database Error");
    }

    // Log successful dashboard access
    logSystemActivity('Dashboard Accessed', 'Student dashboard loaded successfully');

} catch (Exception $e) {
    // Log the error
    logSystemActivity('Dashboard Error', 'Error loading student dashboard: ' . $e->getMessage());
    
    // Redirect to error page or show error message
    redirectWithError('home.php', 'Unable to load dashboard. Please try again later.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Student Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100">
    <!-- Display any system errors (for admin/debug purposes) -->
    <?php if (isAdmin() && hasSystemErrors()): ?>
        <div class="fixed top-4 right-4 z-50">
            <?php displaySystemErrors(); ?>
        </div>
    <?php endif; ?>

    <!-- Display flash messages -->
    <?php displayFlashMessages(); ?>

    <div class="flex h-screen">
        <!-- Formal Sidebar -->
        <div class="w-64 bg-[#0F172A] text-white shadow-lg">
            <div class="p-6 h-full flex flex-col">
                <!-- Logo Section -->
                <div class="flex items-center space-x-3 mb-8">
                    <img src="img/logo.png" alt="Logo" class="w-10 h-10" />
                    <div>
                        <h1 class="text-xl font-bold text-white">Student Panel</h1>
                        <p class="text-sm text-gray-400">Dashboard</p>
                    </div>
                </div>

                <!-- Navigation Menu -->
                <nav class="flex-1 space-y-2">
                    <a href="#dashboard" onclick="showSection('dashboard')" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-[#1E293B] transition-colors duration-200 active-section">
                        <i class="fas fa-tachometer-alt text-gray-400"></i>
                        <span class="font-medium">Dashboard</span>
                    </a>
                    <a href="#clubs" onclick="showSection('clubs')" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-[#1E293B] transition-colors duration-200">
                        <i class="fas fa-users text-gray-400"></i>
                        <span class="font-medium">My Clubs</span>
                    </a>
                    <a href="#activities" onclick="showSection('activities')" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-[#1E293B] transition-colors duration-200">
                        <i class="fas fa-calendar-alt text-gray-400"></i>
                        <span class="font-medium">My Activities</span>
                    </a>
                    <a href="student_profile.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-[#1E293B] transition-colors duration-200">
                        <i class="fas fa-user text-gray-400"></i>
                        <span class="font-medium">My Profile</span>
                    </a>
                </nav>

                <!-- Logout Button -->
                <div class="mt-auto">
                    <a href="logout.php" class="flex items-center justify-center space-x-3 px-4 py-3 rounded-lg bg-red-600 hover:bg-red-700 transition-colors duration-200 text-white">
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="font-medium">Logout</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Main -->
        <div class="flex-1 overflow-auto">
            <?php 
            // Include notification system
            if (file_exists(__DIR__ . '/components/notification.php')) {
                require_once __DIR__ . '/components/notification.php';
                
                // Check for login messages
                if (isset($_GET['login']) && $_GET['login'] === 'success') {
                    Notification::showLogin();
                }
                
                // Render notifications
                $notification = Notification::getInstance();
                echo $notification->render();
            }
            ?>
            
            <!-- Top bar flash/info -->
            <div class="p-6 border-b bg-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Welcome back, <?= sanitizeOutput($student_name) ?></h2>
                        <p class="text-gray-500 text-sm">Logged in as <?= sanitizeOutput($student_email) ?></p>
                    </div>
                    <div class="hidden sm:flex gap-3">
                        <a href="club_list.php" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm">Browse Clubs</a>
                        <a href="student_profile.php" class="px-4 py-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white text-sm">My Profile</a>
                    </div>
                </div>
            </div>

            <!-- Dashboard -->
            <div id="dashboard" class="section p-8">
                <?php 
                $breadcrumb = Breadcrumb::forStudentDashboard();
                echo $breadcrumb->render();
                ?>
                <div class="mb-8 text-center">
                    <h3 class="text-4xl font-extrabold text-[#0F172A]">Dashboard Overview</h3>
                    <div class="w-24 h-1 bg-gradient-to-r from-[#F59E0B] to-[#EF4444] mx-auto mt-3 rounded"></div>
                </div>
                
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition-all duration-300 border-l-4 border-l-blue-500">
                        <div class="flex items-center">
                            <div class="p-3 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg">
                                <i class="fas fa-users text-white text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-600 font-medium">Clubs Joined</p>
                                <p class="text-3xl font-bold text-gray-800"><?= $member_count ?></p>
                                <p class="text-xs text-blue-600 font-medium">Active memberships</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition-all duration-300 border-l-4 border-l-green-500">
                        <div class="flex items-center">
                            <div class="p-3 bg-gradient-to-r from-green-500 to-green-600 rounded-xl shadow-lg">
                                <i class="fas fa-calendar-check text-white text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-600 font-medium">Activities</p>
                                <p class="text-3xl font-bold text-gray-800"><?= $total_activities ?></p>
                                <p class="text-xs text-green-600 font-medium">Participated</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition-all duration-300 border-l-4 border-l-yellow-500">
                        <div class="flex items-center">
                            <div class="p-3 bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-xl shadow-lg">
                                <i class="fas fa-star text-white text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-600 font-medium">Merit Points</p>
                                <p class="text-3xl font-bold text-gray-800"><?= $total_merit ?></p>
                                <p class="text-xs text-yellow-600 font-medium">Total earned</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Section -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="text-center mb-6">
                        <h4 class="text-2xl font-bold text-[#0F172A] mb-2">Quick Actions</h4>
                        <div class="w-16 h-1 bg-gradient-to-r from-[#F59E0B] to-[#EF4444] mx-auto rounded-full"></div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="club_list.php" class="group flex items-center p-4 bg-blue-50 rounded-xl border border-blue-100 hover:bg-blue-100 transition-all duration-300 transform hover:scale-105">
                            <div class="w-3 h-3 bg-blue-500 rounded-full group-hover:scale-125 transition-transform duration-300"></div>
                            <div class="ml-3">
                                <p class="text-gray-800 font-medium">Join a Club</p>
                                <p class="text-sm text-gray-500">Discover and join new clubs</p>
                            </div>
                            <i class="fas fa-arrow-right text-blue-500 ml-auto group-hover:translate-x-1 transition-transform duration-300"></i>
                        </a>
                        <a href="#activities" onclick="showSection('activities')" class="group flex items-center p-4 bg-green-50 rounded-xl border border-green-100 hover:bg-green-100 transition-all duration-300 transform hover:scale-105">
                            <div class="w-3 h-3 bg-green-500 rounded-full group-hover:scale-125 transition-transform duration-300"></div>
                            <div class="ml-3">
                                <p class="text-gray-800 font-medium">View Activities</p>
                                <p class="text-sm text-gray-500">See your participation</p>
                            </div>
                            <i class="fas fa-arrow-right text-green-500 ml-auto group-hover:translate-x-1 transition-transform duration-300"></i>
                        </a>
                        <a href="student_profile.php" class="group flex items-center p-4 bg-purple-50 rounded-xl border border-purple-100 hover:bg-purple-100 transition-all duration-300 transform hover:scale-105">
                            <div class="w-3 h-3 bg-purple-500 rounded-full group-hover:scale-125 transition-transform duration-300"></div>
                            <div class="ml-3">
                                <p class="text-gray-800 font-medium">My Profile</p>
                                <p class="text-sm text-gray-500">Update your information</p>
                            </div>
                            <i class="fas fa-arrow-right text-purple-500 ml-auto group-hover:translate-x-1 transition-transform duration-300"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Clubs Section -->
            <div id="clubs" class="section p-8 hidden">
                <?php 
                $breadcrumb = new Breadcrumb();
                $breadcrumb->addItem('Student Portal', '#', 'fas fa-user-graduate');
                $breadcrumb->addItem('Dashboard', '#dashboard', 'fas fa-tachometer-alt');
                $breadcrumb->addItem('My Clubs', null, 'fas fa-users');
                echo $breadcrumb->render();
                ?>
                <div class="mb-8 text-center">
                    <h3 class="text-4xl font-extrabold text-[#0F172A]">My Clubs</h3>
                    <div class="w-24 h-1 bg-gradient-to-r from-[#F59E0B] to-[#EF4444] mx-auto mt-3 rounded"></div>
                </div>
                <div class="flex items-center justify-between mb-8">
                    <p class="text-gray-600 text-lg">Manage your club memberships</p>
                    <a href="club_list.php" class="px-6 py-3 rounded-xl bg-gradient-to-r from-[#F59E0B] to-[#EF4444] hover:from-[#EF4444] hover:to-[#F59E0B] text-white transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                        <i class="fas fa-plus mr-2"></i>Browse Clubs
                    </a>
                </div>
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50/80 backdrop-blur">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Club</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if ($member_count > 0): ?>
                                    <?php mysqli_data_seek($memberships_result, 0); ?>
                                    <?php while ($club = mysqli_fetch_assoc($memberships_result)): ?>
                                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900"><?= sanitizeOutput($club['club_name']) ?></div>
                                            <div class="text-xs text-gray-500">ID: <?= (int)$club['club_id'] ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="club.php?club_id=<?= (int)$club['club_id'] ?>" class="text-blue-600 hover:text-blue-900 px-3 py-1.5 rounded-md hover:bg-blue-50 border border-blue-100">View</a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="2" class="px-6 py-8 text-center text-gray-500">You haven't joined any clubs yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Activities Section -->
            <div id="activities" class="section p-8 hidden">
                <?php 
                $breadcrumb = new Breadcrumb();
                $breadcrumb->addItem('Student Portal', '#', 'fas fa-user-graduate');
                $breadcrumb->addItem('Dashboard', '#dashboard', 'fas fa-tachometer-alt');
                $breadcrumb->addItem('My Activities', null, 'fas fa-calendar-alt');
                echo $breadcrumb->render();
                ?>
                <div class="mb-8 text-center">
                    <h3 class="text-4xl font-extrabold text-[#0F172A]">My Activities</h3>
                    <div class="w-24 h-1 bg-gradient-to-r from-[#F59E0B] to-[#EF4444] mx-auto mt-3 rounded"></div>
                </div>
                <div class="mb-8">
                    <p class="text-gray-600 text-lg text-center">Track your participation and merit points</p>
                </div>
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50/80 backdrop-blur">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Activity</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Club</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Merit</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if ($activities_result && mysqli_num_rows($activities_result) > 0): ?>
                                    <?php mysqli_data_seek($activities_result, 0); ?>
                                    <?php while ($a = mysqli_fetch_assoc($activities_result)): ?>
                                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900"><?= sanitizeOutput($a['activity_name']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-600"><?= formatDate($a['start']) ?> - <?= formatDate($a['end']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-600"><?= sanitizeOutput($a['club_name']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-amber-100 text-amber-800">
                                                <?= (int)$a['merit_point'] ?> pts
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">You haven't participated in any activities yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Profile -->
            <div id="profile" class="section p-8 hidden">
                <h3 class="text-3xl font-bold text-gray-800 mb-8">My Profile</h3>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-500">Name</p>
                            <p class="text-lg font-semibold text-gray-800 mt-1"><?= sanitizeOutput($student_name) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Email</p>
                            <p class="text-lg font-semibold text-gray-800 mt-1"><?= sanitizeOutput($student_email) ?></p>
                        </div>
                    </div>
                    <div class="mt-6">
                        <a href="student_profile.php" class="px-4 py-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white">Update Password</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showSection(sectionId) {
            document.querySelectorAll('.section').forEach(section => {
                section.classList.add('hidden');
            });
            document.getElementById(sectionId).classList.remove('hidden');
            document.querySelectorAll('nav a').forEach(link => link.classList.remove('active-section'));
            event.target.closest('a').classList.add('active-section');
        }

        // Show success message if redirected with success
        <?php if (isset($_GET['success'])): ?>
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: '<?= sanitizeOutput($_GET['success']) ?>',
            timer: 3000,
            showConfirmButton: false
        });
        <?php endif; ?>

        // Show error message if redirected with error
        <?php if (isset($_GET['error'])): ?>
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: '<?= sanitizeOutput($_GET['error']) ?>',
            timer: 5000,
            showConfirmButton: true
        });
        <?php endif; ?>
    </script>

    <style>
        .active-section {
            background-color: #1E293B;
        }
        
        /* Notification animations */
        .animate-slide-down {
            animation: slideDown 0.5s ease-out forwards;
        }
        
        @keyframes slideDown {
            from {
                transform: translateY(-100%) translateX(-50%);
                opacity: 0;
            }
            to {
                transform: translateY(0) translateX(-50%);
                opacity: 1;
            }
        }
        .section {
            min-height: calc(100vh - 2rem);
        }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
    </style>
</body>
</html>
