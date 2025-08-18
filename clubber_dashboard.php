<?php
session_start();

// Check if user is logged in and is clubber
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'clubber') {
    header('Location: login.php');
    exit;
}

// Include database connection
include "index.php";

// Get clubber's club information
$clubber_id = $_SESSION['user_id'];
$club_query = "SELECT c.*, cl.name as club_name FROM CLUBER c JOIN CLUB cl ON c.club_id = cl.id WHERE c.id = ?";
$stmt = mysqli_prepare($connect, $club_query);
mysqli_stmt_bind_param($stmt, 'i', $clubber_id);
mysqli_stmt_execute($stmt);
$clubber_result = mysqli_stmt_get_result($stmt);
$clubber_data = mysqli_fetch_assoc($clubber_result);

if (!$clubber_data) {
    header('Location: login.php');
    exit;
}

$club_id = $clubber_data['club_id'];
$club_name = $clubber_data['club_name'];

// Safety: drop any legacy triggers on CLUB_PARTICIPANT that can cause recursive updates (error 1442)
function dropClubParticipantTriggersIfAny($connect) {
    $result = mysqli_query($connect, "SHOW TRIGGERS WHERE `Table`='CLUB_PARTICIPANT'");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $triggerName = $row['Trigger'] ?? '';
            if (!empty($triggerName)) {
                @mysqli_query($connect, "DROP TRIGGER IF EXISTS `{$triggerName}`");
            }
        }
        mysqli_free_result($result);
    }
}

// Handle form submissions
$message = '';
$message_type = '';

// Ensure no recursive triggers interfere in this request
dropClubParticipantTriggersIfAny($connect);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_activity':
                $activity_name = trim($_POST['activity_name'] ?? '');
                $start = str_replace('T', ' ', $_POST['start'] ?? '');
                $end = str_replace('T', ' ', $_POST['end'] ?? '');
                $merit_point = isset($_POST['merit_point']) ? (int)$_POST['merit_point'] : 0;
                if ($activity_name === '' || $start === '' || $end === '') {
                    $message = 'Activity name, start and end are required';
                    $message_type = 'error';
                } else {
                    $query = "INSERT INTO CLUB_ACTIVITY (`name`, `start`, `end`, merit_point, club_id) VALUES (?, ?, ?, ?, ?)";
                    $stmt = mysqli_prepare($connect, $query);
                    mysqli_stmt_bind_param($stmt, 'sssii', $activity_name, $start, $end, $merit_point, $club_id);
                    if (mysqli_stmt_execute($stmt)) {
                        $message = 'Activity added successfully';
                        $message_type = 'success';
                    } else {
                        $message = 'Error adding activity: ' . mysqli_error($connect);
                        $message_type = 'error';
                    }
                }
                break;
            case 'delete_activity':
                $activity_id = isset($_POST['activity_id']) ? (int)$_POST['activity_id'] : 0;
                $query = "DELETE FROM CLUB_ACTIVITY WHERE id = ? AND club_id = ?";
                $stmt = mysqli_prepare($connect, $query);
                mysqli_stmt_bind_param($stmt, 'ii', $activity_id, $club_id);
                if (mysqli_stmt_execute($stmt)) {
                    $message = 'Activity deleted successfully';
                    $message_type = 'success';
                } else {
                    $message = 'Error deleting activity: ' . mysqli_error($connect);
                    $message_type = 'error';
                }
                break;
            case 'set_hierarchy':
                $student_id = $_POST['student_id'];
                $position = trim($_POST['position']);
                
                if (empty($position)) {
                    $message = 'Position is required';
                    $message_type = 'error';
                } else {
                    $query = "UPDATE CLUB_PARTICIPANT SET position = ? WHERE student_id = ? AND club_id = ?";
                    $stmt = mysqli_prepare($connect, $query);
                    mysqli_stmt_bind_param($stmt, 'sii', $position, $student_id, $club_id);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $message = 'Hierarchy updated successfully';
                        $message_type = 'success';
                    } else {
                        $message = 'Error updating hierarchy: ' . mysqli_error($connect);
                        $message_type = 'error';
                    }
                }
                break;
                
            case 'add_member':
                $student_email = trim($_POST['student_email']);
                $position = trim($_POST['position']);
                
                if (empty($student_email) || empty($position)) {
                    $message = 'Student email and position are required';
                    $message_type = 'error';
                } else {
                    // Check if student exists
                    $student_query = "SELECT id FROM STUDENT WHERE email = ?";
                    $stmt = mysqli_prepare($connect, $student_query);
                    mysqli_stmt_bind_param($stmt, 's', $student_email);
                    mysqli_stmt_execute($stmt);
                    $student_result = mysqli_stmt_get_result($stmt);
                    
                    if (mysqli_num_rows($student_result) > 0) {
                        $student = mysqli_fetch_assoc($student_result);
                        $student_id = $student['id'];
                        
                        // Check if already a member
                        $check_query = "SELECT student_id FROM CLUB_PARTICIPANT WHERE student_id = ? AND club_id = ?";
                        $stmt = mysqli_prepare($connect, $check_query);
                        mysqli_stmt_bind_param($stmt, 'ii', $student_id, $club_id);
                        mysqli_stmt_execute($stmt);
                        
                        if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0) {
                            $message = 'Student is already a member of this club';
                            $message_type = 'error';
                        } else {
                            $query = "INSERT INTO CLUB_PARTICIPANT (student_id, club_id, position) VALUES (?, ?, ?)";
                            $stmt = mysqli_prepare($connect, $query);
                            mysqli_stmt_bind_param($stmt, 'iis', $student_id, $club_id, $position);
                            
                            if (mysqli_stmt_execute($stmt)) {
                                $message = 'Member added successfully';
                                $message_type = 'success';
                            } else {
                                $message = 'Error adding member: ' . mysqli_error($connect);
                                $message_type = 'error';
                            }
                        }
                    } else {
                        $message = 'Student not found with this email';
                        $message_type = 'error';
                    }
                }
                break;
                
            case 'delete_member':
                $student_id = $_POST['student_id'];
                $query = "DELETE FROM CLUB_PARTICIPANT WHERE student_id = ? AND club_id = ?";
                $stmt = mysqli_prepare($connect, $query);
                mysqli_stmt_bind_param($stmt, 'ii', $student_id, $club_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $message = 'Member removed successfully';
                    $message_type = 'success';
                } else {
                    $message = 'Error removing member: ' . mysqli_error($connect);
                    $message_type = 'error';
                }
                break;
        }
    }
}

// Fetch data for display
$members_query = "SELECT cp.*, s.name as student_name, s.email FROM CLUB_PARTICIPANT cp 
                  JOIN STUDENT s ON cp.student_id = s.id 
                  WHERE cp.club_id = ? ORDER BY cp.position, s.name";
$stmt = mysqli_prepare($connect, $members_query);
mysqli_stmt_bind_param($stmt, 'i', $club_id);
mysqli_stmt_execute($stmt);
$members_result = mysqli_stmt_get_result($stmt);

// Get member count
$member_count = mysqli_num_rows($members_result);

// Activities for this club
$activities_query = "SELECT id, `name`, `start`, `end`, merit_point FROM CLUB_ACTIVITY WHERE club_id = ? ORDER BY `start` DESC";
$stmt = mysqli_prepare($connect, $activities_query);
mysqli_stmt_bind_param($stmt, 'i', $club_id);
mysqli_stmt_execute($stmt);
$activities_result = mysqli_stmt_get_result($stmt);
$total_activities = $activities_result ? mysqli_num_rows($activities_result) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clubber Dashboard - <?= htmlspecialchars($club_name) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-[#0F172A] text-white shadow-lg">
            <div class="p-6 h-full flex flex-col">
                <!-- Logo Section -->
                <div class="flex items-center space-x-3 mb-8">
                    <img src="img/logo.png" alt="Logo" class="w-10 h-10">
                    <div>
                        <h1 class="text-xl font-bold text-white"><?= htmlspecialchars($club_name) ?></h1>
                        <p class="text-sm text-gray-400">Club Management</p>
                    </div>
                </div>
                
                <!-- Navigation Menu -->
                <nav class="flex-1 space-y-2">
                    <a href="#dashboard" onclick="showSection('dashboard')" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-[#1E293B] transition-colors duration-200 active-section">
                        <i class="fas fa-tachometer-alt text-gray-400"></i>
                        <span class="font-medium">Dashboard</span>
                    </a>
                    
                    <a href="#activities" onclick="showSection('activities')" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-[#1E293B] transition-colors duration-200">
                        <i class="fas fa-calendar-alt text-gray-400"></i>
                        <span class="font-medium">Activities</span>
                    </a>
                    
                    <a href="#members" onclick="showSection('members')" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-[#1E293B] transition-colors duration-200">
                        <i class="fas fa-users text-gray-400"></i>
                        <span class="font-medium">Manage Members</span>
                    </a>
                    
                    <a href="#hierarchy" onclick="showSection('hierarchy')" 
                       class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-[#1E293B] transition-colors duration-200">
                        <i class="fas fa-sitemap text-gray-400"></i>
                        <span class="font-medium">Club Hierarchy</span>
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

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <!-- Message Display -->
            <?php if ($message): ?>
                <div id="flash-message" class="fixed top-4 right-4 z-50">
                    <div class="px-6 py-4 rounded-lg shadow-lg <?= $message_type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white' ?>">
                        <?= htmlspecialchars($message) ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Dashboard Section -->
            <div id="dashboard" class="section p-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-8">Club Dashboard</h2>
                
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition-all duration-300 border-l-4 border-l-blue-500">
                        <div class="flex items-center">
                            <div class="p-3 bg-blue-500 rounded-xl shadow-lg">
                                <i class="fas fa-users text-white text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-600 font-medium">Total Members</p>
                                <p class="text-3xl font-bold text-gray-800"><?= $member_count ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition-all duration-300 border-l-4 border-l-purple-500">
                        <div class="flex items-center">
                            <div class="p-3 bg-purple-500 rounded-xl shadow-lg">
                                <i class="fas fa-star text-white text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-600 font-medium">Club Status</p>
                                <p class="text-3xl font-bold text-gray-800">Active</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6">Quick Actions</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <button onclick="showSection('members')" class="flex items-center space-x-4 p-4 bg-blue-50 rounded-xl border border-blue-100 hover:bg-blue-100 transition-colors duration-200">
                            <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                            <div class="flex-1 text-left">
                                <p class="text-gray-800 font-medium">Manage Club Members</p>
                                <p class="text-sm text-gray-500">Add, remove, and manage club members</p>
                            </div>
                        </button>
                        <button onclick="showSection('hierarchy')" class="flex items-center space-x-4 p-4 bg-green-50 rounded-xl border border-green-100 hover:bg-green-100 transition-colors duration-200">
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            <div class="flex-1 text-left">
                                <p class="text-gray-800 font-medium">Set Club Hierarchy</p>
                                <p class="text-sm text-gray-500">Organize member positions and roles</p>
                            </div>
                        </button>
                    </div>
                </div>
            </div>



            <!-- Members Management Section -->
            <div id="members" class="section p-8 hidden">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-800">Manage Club Members</h2>
                    <button onclick="openAddMemberModal()" class="bg-[#F59E0B] text-white px-6 py-3 rounded-lg hover:bg-amber-600 transition-colors duration-200">
                        <i class="fas fa-plus mr-2"></i>Add New Member
                    </button>
                </div>

                <!-- Pending Join Requests -->
                <div class="bg-white rounded-xl shadow-md mb-8">
                    <div class="p-6 border-b">
                        <h3 class="text-lg font-semibold text-gray-800">Pending Join Requests</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Student</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Requested At</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php
                                mysqli_query($connect, "CREATE TABLE IF NOT EXISTS CLUB_JOIN_REQUEST (id INT AUTO_INCREMENT PRIMARY KEY, student_id INT NOT NULL, club_id INT NOT NULL, status ENUM('pending','approved','rejected') DEFAULT 'pending', requested_at DATETIME DEFAULT CURRENT_TIMESTAMP, responded_at DATETIME NULL, UNIQUE KEY uniq_request (student_id, club_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                                $rq = mysqli_prepare($connect, "SELECT r.id, r.requested_at, s.name, s.email FROM CLUB_JOIN_REQUEST r JOIN STUDENT s ON s.id = r.student_id WHERE r.club_id = ? AND r.status = 'pending' ORDER BY r.requested_at ASC");
                                mysqli_stmt_bind_param($rq, 'i', $club_id);
                                mysqli_stmt_execute($rq);
                                $rqRes = mysqli_stmt_get_result($rq);
                                if ($rqRes && mysqli_num_rows($rqRes) > 0):
                                    while ($req = mysqli_fetch_assoc($rqRes)):
                                ?>
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($req['name'] ?: $req['email']) ?></div>
                                        <div class="text-xs text-gray-500"><?= htmlspecialchars($req['email']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600"><?= date('Y-m-d H:i', strtotime($req['requested_at'])) ?></td>
                                    <td class="px-6 py-4 text-right space-x-2">
                                        <form action="approve_join_request.php" method="POST" class="inline swal-confirm" data-title="Approve this request?" data-confirm="Approve">
                                            <input type="hidden" name="request_id" value="<?= (int)$req['id'] ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button class="px-3 py-1 rounded-md bg-green-50 text-green-700 hover:bg-green-100 text-sm">Approve</button>
                                        </form>
                                        <form action="approve_join_request.php" method="POST" class="inline swal-confirm" data-title="Reject this request?" data-confirm="Reject">
                                            <input type="hidden" name="request_id" value="<?= (int)$req['id'] ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button class="px-3 py-1 rounded-md bg-red-50 text-red-700 hover:bg-red-100 text-sm">Reject</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr>
                                    <td colspan="3" class="px-6 py-6 text-center text-gray-500">No pending requests</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Student Name</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Position</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Joined Date</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (mysqli_num_rows($members_result) > 0): ?>
                                    <?php mysqli_data_seek($members_result, 0); ?>
                                    <?php while ($member = mysqli_fetch_assoc($members_result)): ?>
                                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($member['student_name']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-600"><?= htmlspecialchars($member['email']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                <?= htmlspecialchars($member['position']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500"><?= date('M d, Y', strtotime($member['joined'])) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button onclick="openSetHierarchyModal(<?= $member['student_id'] ?>, '<?= htmlspecialchars($member['position']) ?>')" class="text-indigo-600 hover:text-indigo-900 mr-3 px-3 py-1 rounded-md hover:bg-indigo-50 transition-colors duration-200">
                                                <i class="fas fa-edit mr-1"></i>Set Position
                                            </button>
                                            <form method="POST" class="inline swal-confirm" data-title="Remove this member?" data-confirm="Remove">
                                                <input type="hidden" name="action" value="delete_member">
                                                <input type="hidden" name="student_id" value="<?= $member['student_id'] ?>">
                                                <button type="submit" class="text-red-600 hover:text-red-900 px-3 py-1 rounded-md hover:bg-red-50 transition-colors duration-200">
                                                    <i class="fas fa-trash mr-1"></i>Remove
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                            <div class="flex flex-col items-center py-8">
                                                <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
                                                <p class="text-gray-500 text-lg">No members found</p>
                                                <p class="text-gray-400 text-sm">Add members to your club</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Hierarchy Section -->
            <div id="hierarchy" class="section p-8 hidden">
                <h2 class="text-3xl font-bold text-gray-800 mb-8">Club Hierarchy</h2>
                
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Current Club Structure</h3>
                    <div class="space-y-4">
                        <?php 
                        mysqli_data_seek($members_result, 0);
                        $positions = [];
                        while ($member = mysqli_fetch_assoc($members_result)) {
                            $pos = $member['position'];
                            if (!isset($positions[$pos])) {
                                $positions[$pos] = [];
                            }
                            $positions[$pos][] = $member;
                        }
                        
                        if (!empty($positions)):
                            foreach ($positions as $position => $members):
                        ?>
                        <div class="border border-gray-200 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-800 mb-3"><?= htmlspecialchars($position) ?></h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                <?php foreach ($members as $member): ?>
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <div class="font-medium text-gray-900"><?= htmlspecialchars($member['student_name']) ?></div>
                                    <div class="text-sm text-gray-600"><?= htmlspecialchars($member['email']) ?></div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php 
                            endforeach;
                        else:
                        ?>
                        <div class="text-center text-gray-500 py-8">
                            <i class="fas fa-sitemap text-4xl text-gray-300 mb-4"></i>
                            <p>No hierarchy set yet. Add members and set their positions.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Activities Section -->
            <div id="activities" class="section p-8 hidden">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-800">Activities</h2>
                    <button onclick="openAddActivityModal()" class="bg-[#F59E0B] text-white px-6 py-3 rounded-lg hover:bg-amber-600 transition-colors duration-200">
                        <i class="fas fa-plus mr-2"></i>Add Activity
                    </button>
                </div>

                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Start</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">End</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Merit</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if ($total_activities > 0): ?>
                                    <?php mysqli_data_seek($activities_result, 0); ?>
                                    <?php while ($a = mysqli_fetch_assoc($activities_result)): ?>
                                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($a['name']) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-600"><?= date('Y-m-d H:i', strtotime($a['start'])) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-600"><?= date('Y-m-d H:i', strtotime($a['end'])) ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                <?= (int)$a['merit_point'] ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <form method="POST" class="inline swal-confirm" data-title="Delete this activity?" data-confirm="Delete">
                                                <input type="hidden" name="action" value="delete_activity">
                                                <input type="hidden" name="activity_id" value="<?= (int)$a['id'] ?>">
                                                <button type="submit" class="text-red-600 hover:text-red-900 px-3 py-1 rounded-md hover:bg-red-50 transition-colors duration-200">
                                                    <i class="fas fa-trash mr-1"></i>Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                            <div class="flex flex-col items-center">
                                                <i class="fas fa-calendar-alt text-4xl text-gray-300 mb-4"></i>
                                                <p>No activities yet</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Add Member Modal -->
    <div id="addMemberModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50" style="display: none;" onclick="closeAddMemberModal()">
        <div class="flex items-center justify-center min-h-screen p-4" onclick="event.stopPropagation()">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
                <div class="p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Add New Member</h3>
                                         <form method="POST" onsubmit="setTimeout(closeAddMemberModal, 100);">
                         <input type="hidden" name="action" value="add_member">
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Student Email</label>
                            <input type="email" name="student_email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F59E0B] focus:border-transparent" placeholder="Enter student email">
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Position</label>
                            <input type="text" name="position" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F59E0B] focus:border-transparent" placeholder="e.g., Member, Secretary, Treasurer">
                        </div>
                        
                        <div class="flex space-x-3">
                            <button type="submit" class="flex-1 bg-[#F59E0B] text-white px-4 py-2 rounded-lg hover:bg-amber-600 transition-colors duration-200">
                                Add Member
                            </button>
                            <button type="button" onclick="closeAddMemberModal()" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors duration-200">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Set Hierarchy Modal -->
    <div id="setHierarchyModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
                <div class="p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Set Member Position</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="set_hierarchy">
                        <input type="hidden" name="student_id" id="hierarchy_student_id">
                        
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Position</label>
                            <input type="text" name="position" id="hierarchy_position" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F59E0B] focus:border-transparent" placeholder="e.g., Member, Secretary, Treasurer">
                        </div>
                        
                        <div class="flex space-x-3">
                            <button type="submit" class="flex-1 bg-[#F59E0B] text-white px-4 py-2 rounded-lg hover:bg-amber-600 transition-colors duration-200">
                                Update Position
                            </button>
                            <button type="button" onclick="closeSetHierarchyModal()" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors duration-200">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Activity Modal -->
    <div id="addActivityModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4" onclick="event.stopPropagation()">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
                <div class="p-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Add Activity</h3>
                    <form method="POST">
                        <input type="hidden" name="action" value="add_activity">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                            <input type="text" name="activity_name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F59E0B] focus:border-transparent">
                        </div>
                        <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Start</label>
                                <input type="datetime-local" name="start" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F59E0B] focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">End</label>
                                <input type="datetime-local" name="end" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F59E0B] focus:border-transparent">
                            </div>
                        </div>
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Merit Points</label>
                            <input type="number" name="merit_point" value="0" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F59E0B] focus:border-transparent">
                        </div>
                        <div class="flex space-x-3">
                            <button type="submit" class="flex-1 bg-[#F59E0B] text-white px-4 py-2 rounded-lg hover:bg-amber-600 transition-colors duration-200">Add</button>
                            <button type="button" onclick="closeAddActivityModal()" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors duration-200">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Section navigation
        function showSection(sectionId) {
            // Hide all sections
            document.querySelectorAll('.section').forEach(section => {
                section.classList.add('hidden');
            });
            
            // Show selected section
            document.getElementById(sectionId).classList.remove('hidden');
            
            // Update active navigation
            document.querySelectorAll('nav a').forEach(link => {
                link.classList.remove('active-section');
            });
            event.target.classList.add('active-section');
        }

        // Modal functions

        function openAddMemberModal() {
            const modal = document.getElementById('addMemberModal');
            if (modal) {
                modal.style.display = 'block';
                modal.style.visibility = 'visible';
                modal.style.opacity = '1';
            } else {
                console.error('Modal not found!');
            }
        }

        function closeAddMemberModal() {
            const modal = document.getElementById('addMemberModal');
            if (modal) {
                modal.style.display = 'none';
                modal.style.visibility = 'hidden';
                modal.style.opacity = '0';
            }
        }

        function openAddActivityModal() {
            const modal = document.getElementById('addActivityModal');
            modal.classList.remove('hidden');
        }

        function closeAddActivityModal() {
            const modal = document.getElementById('addActivityModal');
            modal.classList.add('hidden');
        }

        function openSetHierarchyModal(studentId, currentPosition) {
            document.getElementById('hierarchy_student_id').value = studentId;
            document.getElementById('hierarchy_position').value = currentPosition;
            document.getElementById('setHierarchyModal').classList.remove('hidden');
        }

        function closeSetHierarchyModal() {
            document.getElementById('setHierarchyModal').classList.add('hidden');
        }

        // Auto-hide flash message (only)
        setTimeout(() => {
            const flash = document.getElementById('flash-message');
            if (flash) {
                flash.remove();
            }
        }, 5000);

        // Add keyboard event listener for Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeAddMemberModal();
                closeSetHierarchyModal();
                closeAddActivityModal();
            }
        });

        // Generic confirm handler for forms with class swal-confirm
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.swal-confirm').forEach(form => {
                form.addEventListener('submit', function(event) {
                    event.preventDefault(); // Prevent default form submission
                    const title = this.dataset.title;
                    const confirmText = this.dataset.confirm;
                    const action = this.dataset.action; // 'approve' or 'reject'

                    Swal.fire({
                        title: title,
                        html: `Are you sure you want to ${confirmText} this request?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: `Yes, ${confirmText} it!`,
                        cancelButtonText: 'Cancel',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.submit(); // Submit the form if confirmed
                        }
                    });
                });
            });
        });
    </script>

    <style>
        .active-section {
            background-color: #1E293B;
        }
        
        .section {
            min-height: calc(100vh - 2rem);
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    </style>
</body>
</html>
