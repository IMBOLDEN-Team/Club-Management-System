<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Include database connection
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/components/breadcrumb.php';

// Handle form submissions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (isset($_POST['action'])) {
		switch ($_POST['action']) {
			case 'add_clubber':
				$username = trim($_POST['username']);
				$password = trim($_POST['password']);
				$club_id = $_POST['club_id'];
				
				if (empty($username) || empty($password) || empty($club_id)) {
					$message = 'All fields are required';
					$message_type = 'error';
				} else {
					$query = "INSERT INTO CLUBER (username, password, club_id) VALUES (?, ?, ?)";
					$stmt = mysqli_prepare($connect, $query);
					mysqli_stmt_bind_param($stmt, 'ssi', $username, $password, $club_id);
					
					if (mysqli_stmt_execute($stmt)) {
						$message = 'Clubber added successfully';
						$message_type = 'success';
					} else {
						$message = 'Error adding clubber: ' . mysqli_error($connect);
						$message_type = 'error';
					}
				}
				break;
				
			case 'delete_clubber':
				$clubber_id = $_POST['clubber_id'];
				$query = "DELETE FROM CLUBER WHERE id = ?";
				$stmt = mysqli_prepare($connect, $query);
				mysqli_stmt_bind_param($stmt, 'i', $clubber_id);
				
				if (mysqli_stmt_execute($stmt)) {
					$message = 'Clubber deleted successfully';
					$message_type = 'success';
				} else {
					$message = 'Error deleting clubber: ' . mysqli_error($connect);
					$message_type = 'error';
				}
				break;
				
			case 'edit_clubber':
				$clubber_id = (int)$_POST['clubber_id'];
				$username = trim($_POST['username']);
				$password = trim($_POST['password']);
				$club_id = (int)$_POST['club_id'];
				if (empty($clubber_id) || $username === '' || empty($club_id)) {
					$message = 'Username and club are required';
					$message_type = 'error';
				} else {
					if ($password !== '') {
						$query = "UPDATE CLUBER SET username = ?, password = ?, club_id = ? WHERE id = ?";
						$stmt = mysqli_prepare($connect, $query);
						mysqli_stmt_bind_param($stmt, 'ssii', $username, $password, $club_id, $clubber_id);
					} else {
						$query = "UPDATE CLUBER SET username = ?, club_id = ? WHERE id = ?";
						$stmt = mysqli_prepare($connect, $query);
						mysqli_stmt_bind_param($stmt, 'sii', $username, $club_id, $clubber_id);
					}
					if (mysqli_stmt_execute($stmt)) {
						$message = 'Clubber updated successfully';
						$message_type = 'success';
					} else {
						$message = 'Error updating clubber: ' . mysqli_error($connect);
						$message_type = 'error';
					}
				}
				break;
				
			case 'add_club':
				$club_name = trim($_POST['club_name']);
				
				if (empty($club_name)) {
					$message = 'Club name is required';
					$message_type = 'error';
				} else {
					$query = "INSERT INTO CLUB (name) VALUES (?)";
					$stmt = mysqli_prepare($connect, $query);
					mysqli_stmt_bind_param($stmt, 's', $club_name);
					
					if (mysqli_stmt_execute($stmt)) {
						$message = 'Club added successfully';
						$message_type = 'success';
					} else {
						$message = 'Error adding club: ' . mysqli_error($connect);
						$message_type = 'error';
					}
				}
				break;
			
			case 'edit_club':
				$club_id = (int)$_POST['club_id'];
				$club_name = trim($_POST['club_name']);
				if (empty($club_id) || $club_name === '') {
					$message = 'Club name is required';
					$message_type = 'error';
				} else {
					$query = "UPDATE CLUB SET name = ? WHERE id = ?";
					$stmt = mysqli_prepare($connect, $query);
					mysqli_stmt_bind_param($stmt, 'si', $club_name, $club_id);
					if (mysqli_stmt_execute($stmt)) {
						$message = 'Club updated successfully';
						$message_type = 'success';
					} else {
						$message = 'Error updating club: ' . mysqli_error($connect);
						$message_type = 'error';
					}
				}
				break;
			
			case 'delete_club':
				$club_id = (int)$_POST['club_id'];
				if (empty($club_id)) {
					$message = 'Invalid club selected';
					$message_type = 'error';
				} else {
					$stmt = mysqli_prepare($connect, 'DELETE FROM CLUB WHERE id = ?');
					mysqli_stmt_bind_param($stmt, 'i', $club_id);
					if (mysqli_stmt_execute($stmt)) {
						$message = 'Club deleted successfully';
						$message_type = 'success';
					} else {
						$message = 'Unable to delete club. Ensure no clubbers or participants reference this club.';
						$message_type = 'error';
					}
				}
				break;
				

		}
	}
}

// Fetch data for display
$clubs_query = "SELECT * FROM CLUB ORDER BY name";
$clubs_result = mysqli_query($connect, $clubs_query);

$clubbers_query = "SELECT c.*, cl.name as club_name FROM CLUBER c JOIN CLUB cl ON c.club_id = cl.id ORDER BY c.username";
$clubbers_result = mysqli_query($connect, $clubbers_query);

// For student merit monitoring - using CLUB_PARTICIPANT table which has merit_point
$merit_query = "SELECT cp.student_id, cp.merit_point, cp.position, s.name as student_name, s.email, c.name as club_name 
                FROM CLUB_PARTICIPANT cp 
                JOIN STUDENT s ON cp.student_id = s.id 
                JOIN CLUB c ON cp.club_id = c.id 
                ORDER BY cp.merit_point DESC";
$merit_result = mysqli_query($connect, $merit_query);

// Get recent activities from existing tables
$recent_activities = [];

// Get recent club creations
$club_activities = mysqli_query($connect, "SELECT name, created_date FROM CLUB ORDER BY created_date DESC LIMIT 3");
while ($club = mysqli_fetch_assoc($club_activities)) {
    $recent_activities[] = [
        'type' => 'club_created',
        'title' => 'New club "' . $club['name'] . '" created',
        'subtitle' => 'Category: General',
        'time' => $club['created_date'],
        'color' => 'blue'
    ];
}

// Get recent clubber additions
$clubber_activities = mysqli_query($connect, "SELECT c.username, cl.name as club_name, c.id FROM CLUBER c JOIN CLUB cl ON c.club_id = cl.id ORDER BY c.id DESC LIMIT 3");
while ($clubber = mysqli_fetch_assoc($clubber_activities)) {
    $recent_activities[] = [
        'type' => 'clubber_added',
        'title' => 'New clubber registered for ' . $clubber['club_name'],
        'subtitle' => 'Username: ' . $clubber['username'],
        'time' => date('Y-m-d H:i:s'), // Since CLUBER table doesn't have created_date, using current time
        'color' => 'green'
    ];
}

// Get recent student merit updates
$merit_activities = mysqli_query($connect, "SELECT s.name, cp.merit_point, c.name as club_name FROM CLUB_PARTICIPANT cp JOIN STUDENT s ON cp.student_id = s.id JOIN CLUB c ON cp.club_id = c.id ORDER BY cp.merit_point DESC LIMIT 3");
while ($merit = mysqli_fetch_assoc($merit_activities)) {
    $recent_activities[] = [
        'type' => 'merit_updated',
        'title' => 'Student merit updated for ' . $merit['name'],
        'subtitle' => 'Merit points: ' . $merit['merit_point'] . ' (' . $merit['club_name'] . ')',
        'time' => date('Y-m-d H:i:s'), // Since no timestamp, using current time
        'color' => 'purple'
    ];
}

// Sort activities by time (most recent first) and limit to 5
usort($recent_activities, function($a, $b) {
    return strtotime($b['time']) - strtotime($a['time']);
});
$recent_activities = array_slice($recent_activities, 0, 5);
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Admin Dashboard - Club Management System</title>
	<script src="https://cdn.tailwindcss.com"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
	<div class="flex h-screen">
		<!-- Formal Sidebar -->
		<div class="w-64 bg-[#0F172A] text-white shadow-lg">
			<div class="p-6 h-full flex flex-col">
				<!-- Logo Section -->
				<div class="flex items-center space-x-3 mb-8">
					<img src="img/logo.png" alt="Logo" class="w-10 h-10">
					<div>
						<h1 class="text-xl font-bold text-white">Admin Panel</h1>
						<p class="text-sm text-gray-400">Management System</p>
					</div>
				</div>
				
				<!-- Navigation Menu -->
				<nav class="flex-1 space-y-2">
					<a href="#dashboard" onclick="showSection('dashboard')" 
					   class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-[#1E293B] transition-colors duration-200 active-section">
						<i class="fas fa-tachometer-alt text-gray-400"></i>
						<span class="font-medium">Dashboard</span>
					</a>
					
					<a href="#clubs" onclick="showSection('clubs')" 
					   class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-[#1E293B] transition-colors duration-200">
						<i class="fas fa-users text-gray-400"></i>
						<span class="font-medium">Manage Clubs</span>
					</a>
					
					<a href="#clubbers" onclick="showSection('clubbers')" 
					   class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-[#1E293B] transition-colors duration-200">
						<i class="fas fa-user-tie text-gray-400"></i>
						<span class="font-medium">Manage Clubbers</span>
					</a>
					
					<a href="#merit" onclick="showSection('merit')" 
					   class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-[#1E293B] transition-colors duration-200">
						<i class="fas fa-star text-gray-400"></i>
						<span class="font-medium">Student Merit</span>
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
             
             if ($message): ?>
				<script>
					document.addEventListener('DOMContentLoaded', function(){
						Swal.fire({
							icon: '<?= $message_type === 'success' ? 'success' : 'error' ?>',
							title: '<?= $message_type === 'success' ? 'Success' : 'Error' ?>',
							text: <?= json_encode($message) ?>,
							showConfirmButton: false,
							timer: 2200
						});
					});
				</script>
			<?php endif; ?>

			<!-- Dashboard Section -->
			<div id="dashboard" class="section p-8">
				<?php 
				$breadcrumb = Breadcrumb::forAdminDashboard();
				echo $breadcrumb->render();
			 ?>
				<div class="mb-8 text-center">
					<h2 class="text-4xl font-extrabold text-[#0F172A]">Dashboard Overview</h2>
					<div class="w-24 h-1 bg-gradient-to-r from-[#F59E0B] to-[#EF4444] mx-auto mt-3 rounded"></div>
				</div>
				
				<!-- Stats Cards -->
				<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
					<div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition-all duration-300 border-l-4 border-l-blue-500">
						<div class="flex items-center">
							<div class="p-3 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg">
								<i class="fas fa-users text-white text-xl"></i>
							</div>
							<div class="ml-4">
								<p class="text-sm text-gray-600 font-medium">Total Students</p>
								<p class="text-3xl font-bold text-gray-800"><?= mysqli_num_rows(mysqli_query($connect, "SELECT * FROM STUDENT")) ?></p>
								<p class="text-xs text-green-600 font-medium">+12% from last month</p>
							</div>
						</div>
					</div>
					
					<div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition-all duration-300 border-l-4 border-l-green-500">
						<div class="flex items-center">
							<div class="p-3 bg-gradient-to-r from-green-500 to-green-600 rounded-xl shadow-lg">
								<i class="fas fa-user-tie text-white text-xl"></i>
							</div>
							<div class="ml-4">
								<p class="text-sm text-gray-600 font-medium">Total Clubbers</p>
								<p class="text-3xl font-bold text-gray-800"><?= mysqli_num_rows(mysqli_query($connect, "SELECT * FROM CLUBER")) ?></p>
								<p class="text-xs text-green-600 font-medium">+8% from last month</p>
							</div>
						</div>
					</div>
					
					<div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition-all duration-300 border-l-4 border-l-purple-500">
						<div class="flex items-center">
							<div class="p-3 bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl shadow-lg">
								<i class="fas fa-users text-white text-xl"></i>
							</div>
							<div class="ml-4">
								<p class="text-sm text-gray-600 font-medium">Total Clubs</p>
								<p class="text-3xl font-bold text-gray-800"><?= mysqli_num_rows(mysqli_query($connect, "SELECT * FROM CLUB")) ?></p>
								<p class="text-xs text-blue-600 font-medium">+3 new this month</p>
							</div>
						</div>
					</div>
					
					<div class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition-all duration-300 border-l-4 border-l-yellow-500">
						<div class="flex items-center">
							<div class="p-3 bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-xl shadow-lg">
								<i class="fas fa-star text-white text-xl"></i>
							</div>
							<div class="ml-4">
								<p class="text-sm text-gray-600 font-medium">Avg Merit</p>
								<p class="text-3xl font-bold text-gray-800">85.2</p>
								<p class="text-xs text-yellow-600 font-medium">+2.1 pts improvement</p>
							</div>
						</div>
					</div>
				</div>

				<!-- Recent Activity -->
				<div class="bg-white rounded-xl shadow-md p-6">
					<div class="flex items-center justify-between mb-6">
						<h3 class="text-xl font-semibold text-gray-800">Recent Activity</h3>
						<button class="text-sm text-blue-600 hover:text-blue-800 font-medium">View All</button>
					</div>
					<div class="space-y-4">
						<?php if (!empty($recent_activities)): ?>
							<?php foreach ($recent_activities as $activity): ?>
								<?php 
								$color_class = '';
								$bg_class = '';
								$border_class = '';
								
								switch ($activity['color']) {
									case 'green':
										$color_class = 'bg-green-500';
										$bg_class = 'bg-gradient-to-r from-green-50 to-emerald-50';
										$border_class = 'border-green-100';
										break;
									case 'blue':
										$color_class = 'bg-blue-500';
										$bg_class = 'bg-gradient-to-r from-blue-50 to-indigo-50';
										$border_class = 'border-blue-100';
										break;
									case 'purple':
										$color_class = 'bg-purple-500';
										$bg_class = 'bg-gradient-to-r from-purple-50 to-violet-50';
										$border_class = 'border-purple-100';
										break;
									default:
										$color_class = 'bg-gray-500';
										$bg_class = 'bg-gradient-to-r from-gray-50 to-gray-100';
										$border_class = 'border-gray-100';
								}
								
								// Calculate time ago
								$time_ago = '';
								$time_diff = time() - strtotime($activity['time']);
								if ($time_diff < 60) {
									$time_ago = 'Just now';
								} elseif ($time_diff < 3600) {
									$minutes = floor($time_diff / 60);
									$time_ago = $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
								} elseif ($time_diff < 86400) {
									$hours = floor($time_diff / 3600);
									$time_ago = $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
								} else {
									$days = floor($time_diff / 86400);
									$time_ago = $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
								}
								?>
								<div class="flex items-center space-x-4 p-4 <?= $bg_class ?> rounded-xl border <?= $border_class ?>">
									<div class="w-3 h-3 <?= $color_class ?> rounded-full animate-pulse"></div>
									<div class="flex-1">
										<p class="text-gray-800 font-medium"><?= htmlspecialchars($activity['title']) ?></p>
										<p class="text-sm text-gray-500"><?= htmlspecialchars($activity['subtitle']) ?></p>
									</div>
									<span class="text-sm text-gray-400 bg-white px-3 py-1 rounded-full shadow-sm"><?= $time_ago ?></span>
								</div>
							<?php endforeach; ?>
						<?php else: ?>
							<div class="text-center py-8 text-gray-500">
								<i class="fas fa-info-circle text-2xl mb-2"></i>
								<p>No recent activities found</p>
							</div>
						<?php endif; ?>
					</div>
				</div>
				

			</div>

			<!-- Clubs Management Section -->
			<div id="clubs" class="section p-8 hidden">
				<?php 
				$breadcrumb = Breadcrumb::forAdminSection('Manage Clubs', 'fas fa-users');
				echo $breadcrumb->render();
				?>
				<div class="flex justify-between items-center mb-8">
					<div>
						<h2 class="text-4xl font-extrabold text-[#0F172A]">Manage Clubs</h2>
						<div class="w-24 h-1 bg-gradient-to-r from-[#F59E0B] to-[#EF4444] mt-2 rounded"></div>
					</div>
					<button onclick="openAddClubModal()" class="bg-[#F59E0B] text-white px-6 py-3 rounded-lg hover:bg-amber-600 transition-colors duration-200">
						<i class="fas fa-plus mr-2"></i>Add New Club
					</button>
				</div>

				<div class="bg-white rounded-xl shadow-md overflow-hidden table-container hover-lift">
					<div class="overflow-x-auto">
						<table class="w-full">
							<thead class="bg-gradient-to-r from-gray-50 to-gray-100">
								<tr>
									<th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Club Name</th>
									<th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Created Date</th>
									<th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
								</tr>
							</thead>
							<tbody class="bg-white divide-y divide-gray-200">
								<?php if ($clubs_result && mysqli_num_rows($clubs_result) > 0): ?>
									<?php while ($club = mysqli_fetch_assoc($clubs_result)): ?>
									<tr class="hover:bg-gray-50 transition-colors duration-200">
										<td class="px-6 py-4 whitespace-nowrap"><div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($club['name']) ?></div></td>
										<td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-500"><?= date('M d, Y', strtotime($club['created_date'])) ?></div></td>
										<td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
											<form method="POST" class="inline">
												<input type="hidden" name="action" value="edit_club">
												<input type="hidden" name="club_id" value="<?= $club['id'] ?>">
												<input type="text" name="club_name" value="<?= htmlspecialchars($club['name']) ?>" class="px-2 py-1 border rounded text-sm mr-2">
												<button class="text-indigo-600 hover:text-indigo-900 px-3 py-1 rounded-md hover:bg-indigo-50">Save</button>
											</form>
											<form method="POST" class="inline confirm-delete" data-entity="club">
												<input type="hidden" name="action" value="delete_club">
												<input type="hidden" name="club_id" value="<?= $club['id'] ?>">
												<button class="text-red-600 hover:text-red-900 px-3 py-1 rounded-md hover:bg-red-50">Delete</button>
											</form>
										</td>
									</tr>
									<?php endwhile; ?>
								<?php else: ?>
								<tr><td colspan="3" class="px-6 py-8 text-center text-gray-500">No clubs found</td></tr>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>

			<!-- Clubbers Management Section -->
			<div id="clubbers" class="section p-8 hidden">
				<?php 
				$breadcrumb = Breadcrumb::forAdminSection('Manage Clubbers', 'fas fa-user-tie');
				echo $breadcrumb->render();
				?>
				<div class="flex justify-between items-center mb-8">
					<div>
						<h2 class="text-4xl font-extrabold text-[#0F172A]">Manage Clubbers</h2>
						<div class="w-24 h-1 bg-gradient-to-r from-[#F59E0B] to-[#EF4444] mt-2 rounded"></div>
					</div>
					<button onclick="openAddClubberModal()" class="bg-[#F59E0B] text-white px-6 py-3 rounded-lg hover:bg-amber-600 transition-colors duration-200">
						<i class="fas fa-plus mr-2"></i>Add New Clubber
					</button>
				</div>

				<div class="bg-white rounded-xl shadow-md overflow-hidden">
					<div class="overflow-x-auto">
						<table class="w-full">
							<thead class="bg-gradient-to-r from-gray-50 to-gray-100">
								<tr>
									<th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Username</th>
									<th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Club</th>
									<th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Created Date</th>
									<th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
								</tr>
							</thead>
							<tbody class="bg-white divide-y divide-gray-200">
								<?php if ($clubbers_result && mysqli_num_rows($clubbers_result) > 0): ?>
									<?php while ($clubber = mysqli_fetch_assoc($clubbers_result)): ?>
									<tr class="hover:bg-gray-50 transition-colors duration-200">
										<td class="px-6 py-4 whitespace-nowrap"><div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($clubber['username']) ?></div></td>
										<td class="px-6 py-4 whitespace-nowrap"><span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800"><?= htmlspecialchars($clubber['club_name']) ?></span></td>
										<td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-500"><?= date('M d, Y', strtotime($clubber['created_date'])) ?></div></td>
										<td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
											<button type="button" class="text-indigo-600 hover:text-indigo-900 px-3 py-1 rounded-md hover:bg-indigo-50" onclick="showEditClubberModal(<?= $clubber['id'] ?>,'<?= htmlspecialchars($clubber['username'], ENT_QUOTES) ?>',<?= (int)$clubber['club_id'] ?>)">Edit</button>
											<form method="POST" class="inline confirm-delete" data-entity="clubber">
												<input type="hidden" name="action" value="delete_clubber">
												<input type="hidden" name="clubber_id" value="<?= $clubber['id'] ?>">
												<button class="text-red-600 hover:text-red-900 px-3 py-1 rounded-md hover:bg-red-50">Delete</button>
											</form>
										</td>
									</tr>
									<?php endwhile; ?>
								<?php else: ?>
								<tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">No clubbers found</td></tr>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>

			<!-- Student Merit Section -->
			<div id="merit" class="section p-8 hidden">
				<?php 
				$breadcrumb = Breadcrumb::forAdminSection('Student Merit', 'fas fa-star');
				echo $breadcrumb->render();
				?>
				<div class="mb-8 text-center">
					<h2 class="text-4xl font-extrabold text-[#0F172A]">Student Merit Monitoring</h2>
					<div class="w-24 h-1 bg-gradient-to-r from-[#F59E0B] to-[#EF4444] mx-auto mt-3 rounded"></div>
				</div>
				<div class="bg-white rounded-xl shadow-md overflow-hidden">
					<div class="overflow-x-auto">
						<table class="w-full">
							<thead class="bg-gradient-to-r from-gray-50 to-gray-100">
								<tr>
									<th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Student Name</th>
									<th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Email</th>
									<th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Club</th>
									<th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Position</th>
									<th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Merit Points</th>
								</tr>
							</thead>
							<tbody class="bg-white divide-y divide-gray-200">
								<?php if ($merit_result && mysqli_num_rows($merit_result) > 0): ?>
									<?php while ($merit = mysqli_fetch_assoc($merit_result)): ?>
									<tr class="hover:bg-gray-50 transition-colors duration-200">
										<td class="px-6 py-4 whitespace-nowrap"><div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($merit['student_name']) ?></div></td>
										<td class="px-6 py-4 whitespace-nowrap"><div class="text-sm text-gray-600"><?= htmlspecialchars($merit['email']) ?></div></td>
										<td class="px-6 py-4 whitespace-nowrap"><span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800"><?= htmlspecialchars($merit['club_name']) ?></span></td>
										<td class="px-6 py-4 whitespace-nowrap"><span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800"><?= htmlspecialchars($merit['position']) ?></span></td>
										<td class="px-6 py-4 whitespace-nowrap"><span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-gradient-to-r from-green-500 to-emerald-600 text-white"><?= $merit['merit_point'] ?> pts</span></td>
									</tr>
									<?php endwhile; ?>
								<?php else: ?>
								<tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">No merit data found</td></tr>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		

	</div>

	<!-- Add Club Modal -->
	<div id="addClubModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
		<div class="flex items-center justify-center min-h-screen p-4">
			<div class="bg-white rounded-xl shadow-xl max-w-md w-full">
				<div class="p-6">
					<h3 class="text-xl font-semibold text-gray-800 mb-4">Add New Club</h3>
					<form method="POST">
						<input type="hidden" name="action" value="add_club">
						<div class="mb-6">
							<label class="block text-sm font-medium text-gray-700 mb-2">Club Name</label>
							<input type="text" name="club_name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F59E0B] focus:border-transparent">
						</div>
						<div class="flex space-x-3">
							<button type="submit" class="flex-1 bg-[#F59E0B] text-white px-4 py-2 rounded-lg hover:bg-amber-600 transition-colors duration-200">Add Club</button>
							<button type="button" onclick="closeAddClubModal()" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors duration-200">Cancel</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>

	<!-- Add Clubber Modal -->
	<div id="addClubberModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
		<div class="flex items-center justify-center min-h-screen p-4">
			<div class="bg-white rounded-xl shadow-xl max-w-md w-full">
				<div class="p-6">
					<h3 class="text-xl font-semibold text-gray-800 mb-4">Add New Clubber</h3>
					<form method="POST">
						<input type="hidden" name="action" value="add_clubber">
						<div class="mb-4">
							<label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
							<input type="text" name="username" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F59E0B] focus:border-transparent">
						</div>
						<div class="mb-4">
							<label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
							<input type="password" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F59E0B] focus:border-transparent">
						</div>
						<div class="mb-6">
							<label class="block text-sm font-medium text-gray-700 mb-2">Club</label>
							<select name="club_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F59E0B] focus:border-transparent">
								<option value="">Select club</option>
								<?php $clubs = mysqli_query($connect, "SELECT * FROM CLUB ORDER BY name"); while ($club = mysqli_fetch_assoc($clubs)): ?>
								<option value="<?= $club['id'] ?>"><?= htmlspecialchars($club['name']) ?></option>
								<?php endwhile; ?>
							</select>
						</div>
						<div class="flex space-x-3">
							<button type="submit" class="flex-1 bg-[#F59E0B] text-white px-4 py-2 rounded-lg hover:bg-amber-600 transition-colors duration-200">Add Clubber</button>
							<button type="button" onclick="closeAddClubberModal()" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors duration-200">Cancel</button>
						</div>
					</form>
				</div>
			</div>
		</div>

	<!-- Edit Clubber Modal -->
	<div id="editClubberModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
		<div class="flex items-center justify-center min-h-screen p-4">
			<div class="bg-white rounded-xl shadow-xl max-w-md w-full">
				<div class="p-6">
					<h3 class="text-xl font-semibold text-gray-800 mb-4">Edit Clubber</h3>
					<form method="POST">
						<input type="hidden" name="action" value="edit_clubber">
						<input type="hidden" name="clubber_id" id="edit_clubber_id">
						<div class="mb-4">
							<label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
							<input type="text" name="username" id="edit_username" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F59E0B] focus:border-transparent">
						</div>
						<div class="mb-4">
							<label class="block text-sm font-medium text-gray-700 mb-2">New Password (optional)</label>
							<input type="password" name="password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F59E0B] focus:border-transparent">
						</div>
						<div class="mb-6">
							<label class="block text-sm font-medium text-gray-700 mb-2">Club</label>
							<select name="club_id" id="edit_club_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#F59E0B] focus:border-transparent">
								<?php $clubs = mysqli_query($connect, 'SELECT * FROM CLUB ORDER BY name'); while ($c = mysqli_fetch_assoc($clubs)): ?>
								<option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
								<?php endwhile; ?>
							</select>
						</div>
						<div class="flex space-x-3">
							<button type="submit" class="flex-1 bg-[#F59E0B] text-white px-4 py-2 rounded-lg hover:bg-amber-600 transition-colors duration-200">Save Changes</button>
							<button type="button" onclick="closeEditClubberModal()" class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors duration-200">Cancel</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	


	<script>
	function showSection(sectionId) {
		document.querySelectorAll('.section').forEach(section => section.classList.add('hidden'));
		document.getElementById(sectionId).classList.remove('hidden');
		document.querySelectorAll('nav a').forEach(link => link.classList.remove('active-section'));
		event.target.classList.add('active-section');
	}
	function openAddClubModal(){document.getElementById('addClubModal').classList.remove('hidden');}
	function closeAddClubModal(){document.getElementById('addClubModal').classList.add('hidden');}
	function openAddClubberModal(){document.getElementById('addClubberModal').classList.remove('hidden');}
	function closeAddClubberModal(){document.getElementById('addClubberModal').classList.add('hidden');}
	function openEditClubberModal(id, username, clubId){
		document.getElementById('edit_clubber_id').value = id;
		document.getElementById('edit_username').value = username;
		const select = document.getElementById('edit_club_id');
		if (select) select.value = clubId;
		document.getElementById('editClubberModal').classList.remove('hidden');
	}
	function closeEditClubberModal(){document.getElementById('editClubberModal').classList.add('hidden');}


	// SweetAlert2 deletion confirms
	document.addEventListener('click', function(e){
		const form = e.target.closest('form.confirm-delete');
		if (form) {
			e.preventDefault();
			const label = form.getAttribute('data-entity') || 'item';
			Swal.fire({
				title: `Delete this ${label}?`,
				icon: 'warning',
				showCancelButton: true,
				confirmButtonColor: '#d33',
				cancelButtonColor: '#3085d6',
				confirmButtonText: 'Yes, delete it'
			}).then((result)=>{ if(result.isConfirmed){ form.submit(); }});
		}
	});
	</script>

	<style>
	.active-section{background-color:#1E293B}

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
	.section{min-height:calc(100vh - 2rem)}
	.table-container{background:linear-gradient(135deg,#f8fafc 0%,#f1f5f9 100%);border-radius:1rem;box-shadow:0 10px 25px -5px rgba(0,0,0,.1),0 10px 10px -5px rgba(0,0,0,.04)}
	</style>
</body>
</html>
