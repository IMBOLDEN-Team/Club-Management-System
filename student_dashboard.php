<?php
session_start();

// Require student login
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'student') {
    header('Location: login.php');
    exit();
}

include 'index.php';

$student_id = (int)($_SESSION['user_id'] ?? 0);
$student_email = $_SESSION['username'] ?? '';
$student_name = $_SESSION['student_name'] ?? 'Student';

// Clubs joined
$memberships_query = "SELECT c.id AS club_id, c.name AS club_name FROM CLUB c
                      INNER JOIN CLUB_PARTICIPANT cp ON c.id = cp.club_id
                      WHERE cp.student_id = ? ORDER BY c.name";
$memberships_stmt = mysqli_prepare($connect, $memberships_query);
mysqli_stmt_bind_param($memberships_stmt, 'i', $student_id);
mysqli_stmt_execute($memberships_stmt);
$memberships_result = mysqli_stmt_get_result($memberships_stmt);
$member_count = $memberships_result ? mysqli_num_rows($memberships_result) : 0;

// Activities participated + totals
$stats_stmt = mysqli_prepare(
    $connect,
    "SELECT COUNT(*) AS total_activities, COALESCE(SUM(ca.merit_point),0) AS total_merit
     FROM CLUB_ACTIVITY ca
     INNER JOIN ACTIVITY_PARTICIPANT ap ON ca.id = ap.club_activity_id
     WHERE ap.student_id = ?"
);
mysqli_stmt_bind_param($stats_stmt, 'i', $student_id);
mysqli_stmt_execute($stats_stmt);
$stats_res = mysqli_stmt_get_result($stats_stmt);
$total_activities = 0;
$total_merit = 0;
if ($stats_res) {
    $row = mysqli_fetch_assoc($stats_res);
    $total_activities = (int)($row['total_activities'] ?? 0);
    $total_merit = (int)($row['total_merit'] ?? 0);
}

// Activities list (latest 10)
$activities_query = "SELECT ca.name AS activity_name, ca.start, ca.end, ca.merit_point, c.name AS club_name
                     FROM CLUB_ACTIVITY ca
                     INNER JOIN ACTIVITY_PARTICIPANT ap ON ca.id = ap.club_activity_id
                     INNER JOIN CLUB c ON ca.club_id = c.id
                     WHERE ap.student_id = ?
                     ORDER BY ca.start DESC
                     LIMIT 10";
$activities_stmt = mysqli_prepare($connect, $activities_query);
mysqli_stmt_bind_param($activities_stmt, 'i', $student_id);
mysqli_stmt_execute($activities_stmt);
$activities_result = mysqli_stmt_get_result($activities_stmt);
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
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-[#0F172A] text-white shadow-lg">
            <div class="p-6 h-full flex flex-col">
                <!-- Logo -->
                <div class="flex items-center space-x-3 mb-8">
                    <img src="img/logo.png" alt="Logo" class="w-10 h-10" />
                    <div>
                        <h1 class="text-xl font-bold text-white">Student Portal</h1>
                        <p class="text-sm text-gray-400">Club Management</p>
                    </div>
                </div>

                <!-- Nav -->
                <nav class="flex-1 space-y-2">
                    <a href="#dashboard" onclick="showSection('dashboard')" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-[#1E293B] transition-colors duration-200 active-section">
                        <i class="fas fa-tachometer-alt text-gray-400"></i>
                        <span class="font-medium">Dashboard</span>
                    </a>
                    <a href="#clubs" onclick="showSection('clubs')" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-[#1E293B] transition-colors duration-200">
                        <i class="fas fa-building text-gray-400"></i>
                        <span class="font-medium">My Clubs</span>
                    </a>
                    <a href="#activities" onclick="showSection('activities')" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-[#1E293B] transition-colors duration-200">
                        <i class="fas fa-calendar-alt text-gray-400"></i>
                        <span class="font-medium">Activities</span>
                    </a>
                    <a href="#profile" onclick="showSection('profile')" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-[#1E293B] transition-colors duration-200">
                        <i class="fas fa-user text-gray-400"></i>
                        <span class="font-medium">Profile</span>
                    </a>
                </nav>

                <!-- Logout -->
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
            <!-- Top bar flash/info -->
            <div class="p-6 border-b bg-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Welcome back, <?= htmlspecialchars($student_name) ?></h2>
                        <p class="text-gray-500 text-sm">Logged in as <?= htmlspecialchars($student_email) ?></p>
                    </div>
                    <div class="hidden sm:flex gap-3">
                        <a href="club_list.php" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm">Browse Clubs</a>
                        <a href="student_profile.php" class="px-4 py-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white text-sm">My Profile</a>
                    </div>
                </div>
            </div>

            <!-- Dashboard -->
            <div id="dashboard" class="section p-8">
                <h3 class="text-3xl font-bold text-gray-800 mb-8">Dashboard</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-l-blue-500">
                        <div class="flex items-center">
                            <div class="p-3 bg-blue-500 rounded-xl shadow-lg">
                                <i class="fas fa-building text-white text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-600 font-medium">Clubs Joined</p>
                                <p class="text-3xl font-bold text-gray-800"><?= $member_count ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-l-emerald-500">
                        <div class="flex items-center">
                            <div class="p-3 bg-emerald-500 rounded-xl shadow-lg">
                                <i class="fas fa-calendar-check text-white text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-600 font-medium">Activities</p>
                                <p class="text-3xl font-bold text-gray-800"><?= $total_activities ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-l-amber-500">
                        <div class="flex items-center">
                            <div class="p-3 bg-amber-500 rounded-xl shadow-lg">
                                <i class="fas fa-star text-white text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-600 font-medium">Merit Points</p>
                                <p class="text-3xl font-bold text-gray-800"><?= $total_merit ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-md p-6">
                    <h4 class="text-xl font-semibold text-gray-800 mb-4">Quick Actions</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="club_list.php" class="flex items-center p-4 bg-blue-50 rounded-xl border border-blue-100 hover:bg-blue-100 transition-colors duration-200">
                            <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                            <div class="ml-3">
                                <p class="text-gray-800 font-medium">Join a Club</p>
                                <p class="text-sm text-gray-500">Discover and join new clubs</p>
                            </div>
                        </a>
                        <a href="#activities" onclick="showSection('activities')" class="flex items-center p-4 bg-green-50 rounded-xl border border-green-100 hover:bg-green-100 transition-colors duration-200">
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            <div class="ml-3">
                                <p class="text-gray-800 font-medium">View Activities</p>
                                <p class="text-sm text-gray-500">See your participation</p>
                            </div>
                        </a>
                        <a href="student_profile.php" class="flex items-center p-4 bg-purple-50 rounded-xl border border-purple-100 hover:bg-purple-100 transition-colors duration-200">
                            <div class="w-3 h-3 bg-purple-500 rounded-full"></div>
                            <div class="ml-3">
                                <p class="text-gray-800 font-medium">My Profile</p>
                                <p class="text-sm text-gray-500">Update your information</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Clubs -->
            <div id="clubs" class="section p-8 hidden">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-3xl font-bold text-gray-800">My Clubs</h3>
                    <a href="club_list.php" class="px-4 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white">Browse Clubs</a>
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
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($club['club_name']) ?></div>
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

            <!-- Activities -->
            <div id="activities" class="section p-8 hidden">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-3xl font-bold text-gray-800">My Activities</h3>
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
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($a['activity_name']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-600"><?= date('M d, Y g:i A', strtotime($a['start'])) ?> - <?= date('M d, Y g:i A', strtotime($a['end'])) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-600"><?= htmlspecialchars($a['club_name']) ?></div>
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
                            <p class="text-lg font-semibold text-gray-800 mt-1"><?= htmlspecialchars($student_name) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Email</p>
                            <p class="text-lg font-semibold text-gray-800 mt-1"><?= htmlspecialchars($student_email) ?></p>
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
    </script>

    <style>
        .active-section {
            background-color: #1E293B;
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
<?php
session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'student') {
    header("Location: login.php");
    exit();
}

// Include database connection
include "index.php";

// Get student information
$student_id = $_SESSION['user_id'];
$student_email = $_SESSION['username'];
$student_name = $_SESSION['student_name'] ?? 'Student'; // Provide default value

// Fetch student's club memberships and activities
$memberships_query = "SELECT c.name as club_name, c.id as club_id 
                     FROM CLUB c 
                     INNER JOIN CLUB_PARTICIPANT cp ON c.id = cp.club_id 
                     WHERE cp.student_id = ?";
$memberships_stmt = mysqli_prepare($connect, $memberships_query);
mysqli_stmt_bind_param($memberships_stmt, 'i', $student_id);
mysqli_stmt_execute($memberships_stmt);
$memberships_result = mysqli_stmt_get_result($memberships_stmt);

// Fetch student's activities
$activities_query = "SELECT ca.name as activity_name, ca.start, ca.end, ca.merit_point, c.name as club_name
                     FROM CLUB_ACTIVITY ca 
                     INNER JOIN ACTIVITY_PARTICIPANT ap ON ca.id = ap.club_activity_id
                     INNER JOIN CLUB c ON ca.club_id = c.id
                     WHERE ap.student_id = ?
                     ORDER BY ca.start DESC";
$activities_stmt = mysqli_prepare($connect, $activities_query);
mysqli_stmt_bind_param($activities_stmt, 'i', $student_id);
mysqli_stmt_execute($activities_stmt);
$activities_result = mysqli_stmt_get_result($activities_stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Club Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                <div class="flex items-center space-x-4">
                    <span class="text-sm">Welcome, <?= htmlspecialchars($student_name) ?></span>
                    <a href="logout.php" class="px-4 py-2 bg-[#F59E0B] text-white rounded-lg hover:bg-[#EF4444] transition-colors duration-300">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Welcome Section -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold bg-gradient-to-r from-[#0F172A] to-[#334155] bg-clip-text text-transparent mb-4">
                Student Dashboard
            </h1>
            <p class="text-[#64748B] text-lg">Welcome back, <?= htmlspecialchars($student_name) ?>!</p>
        </div>

        <!-- Dashboard Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- My Clubs Section -->
            <div class="bg-white/90 backdrop-blur-sm p-6 rounded-2xl shadow-xl border border-white/50">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-[#0F172A]">My Clubs</h2>
                    <a href="club_list.php" class="text-[#F59E0B] hover:text-[#EF4444] font-medium transition-colors duration-300">
                        View All Clubs
                    </a>
                </div>
                
                <?php if (mysqli_num_rows($memberships_result) > 0): ?>
                    <div class="space-y-4">
                        <?php while ($club = mysqli_fetch_assoc($memberships_result)): ?>
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-xl border border-blue-200">
                                <h3 class="font-semibold text-[#0F172A] mb-2"><?= htmlspecialchars($club['club_name']) ?></h3>
                                <p class="text-sm text-[#64748B] mb-3">Club ID: <?= htmlspecialchars($club['club_id']) ?></p>
                                <a href="club.php?id=<?= $club['club_id'] ?>" 
                                   class="inline-block px-4 py-2 bg-[#F59E0B] text-white rounded-lg hover:bg-[#EF4444] transition-colors duration-300 text-sm">
                                    View Club
                                </a>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <div class="text-4xl mb-4">🏢</div>
                        <p class="text-[#64748B] mb-4">You haven't joined any clubs yet.</p>
                        <a href="club_list.php" 
                           class="inline-block px-6 py-3 bg-gradient-to-r from-[#F59E0B] to-[#EF4444] text-white rounded-xl font-semibold hover:from-[#EF4444] hover:to-[#F59E0B] transition-all duration-300">
                            Browse Clubs
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- My Activities Section -->
            <div class="bg-white/90 backdrop-blur-sm p-6 rounded-2xl shadow-xl border border-white/50">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-[#0F172A]">My Activities</h2>
                    <a href="#" class="text-[#F59E0B] hover:text-[#EF4444] font-medium transition-colors duration-300">
                        View All Activities
                    </a>
                </div>
                
                <?php if (mysqli_num_rows($activities_result) > 0): ?>
                    <div class="space-y-4">
                        <?php while ($activity = mysqli_fetch_assoc($activities_result)): ?>
                            <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-4 rounded-xl border border-green-200">
                                <h3 class="font-semibold text-[#0F172A] mb-2"><?= htmlspecialchars($activity['activity_name']) ?></h3>
                                <div class="flex items-center justify-between text-xs text-[#64748B] mb-2">
                                    <span>📅 <?= date('M j, Y', strtotime($activity['start'])) ?></span>
                                    <span>🕒 <?= date('g:i A', strtotime($activity['start'])) ?></span>
                                    <span>🏢 <?= htmlspecialchars($activity['club_name']) ?></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-[#64748B]">Ends: <?= date('M j, Y g:i A', strtotime($activity['end'])) ?></span>
                                    <span class="text-xs font-semibold px-2 py-1 rounded bg-amber-100 text-amber-800">
                                        <?= (int)$activity['merit_point'] ?> pts
                                    </span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <div class="text-4xl mb-4">🎯</div>
                        <p class="text-[#64748B] mb-4">You haven't participated in any activities yet.</p>
                        <a href="#" 
                           class="inline-block px-6 py-3 bg-gradient-to-r from-[#F59E0B] to-[#EF4444] text-white rounded-xl font-semibold hover:from-[#EF4444] hover:to-[#F59E0B] transition-all duration-300">
                            Browse Activities
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-8 bg-white/90 backdrop-blur-sm p-6 rounded-2xl shadow-xl border border-white/50">
            <h2 class="text-2xl font-bold text-[#0F172A] mb-6">Quick Actions</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="club_list.php" 
                   class="flex items-center p-4 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl hover:from-blue-600 hover:to-blue-700 transition-all duration-300">
                    <div class="text-2xl mr-3">🏢</div>
                    <div>
                        <h3 class="font-semibold">Join a Club</h3>
                        <p class="text-sm opacity-90">Discover and join new clubs</p>
                    </div>
                </a>
                
                <a href="#" 
                   class="flex items-center p-4 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl hover:from-green-600 hover:to-green-700 transition-all duration-300">
                    <div class="text-2xl mr-3">🎯</div>
                    <div>
                        <h3 class="font-semibold">View Activities</h3>
                        <p class="text-sm opacity-90">See upcoming activities</p>
                    </div>
                </a>
                
                <a href="student_profile.php" 
                   class="flex items-center p-4 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl hover:from-purple-600 hover:to-purple-700 transition-all duration-300">
                    <div class="text-2xl mr-3">👤</div>
                    <div>
                        <h3 class="font-semibold">My Profile</h3>
                        <p class="text-sm opacity-90">Update your information</p>
                    </div>
                </a>
            </div>
        </div>
    </main>

    <style>
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fade-in 0.6s ease-out;
        }
    </style>
</body>
</html>
