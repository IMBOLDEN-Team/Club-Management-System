<?php
session_start();

// Require student login
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'student') {
	header('Location: login.php');
	exit();
}

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/components/breadcrumb.php';

$student_id = (int)$_SESSION['user_id'];
$student_email = $_SESSION['username'] ?? '';
$student_name = $_SESSION['student_name'] ?? 'Student';

// Flash helper
function set_flash($msg, $type = 'success') {
	$_SESSION['flash'] = ['message' => $msg, 'type' => $type];
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Fetch whether password exists
$hasPassword = false;
$ps = mysqli_prepare($connect, 'SELECT password IS NOT NULL AND password <> "" AS has_pwd FROM STUDENT WHERE id = ?');
mysqli_stmt_bind_param($ps, 'i', $student_id);
mysqli_stmt_execute($ps);
$r = mysqli_fetch_assoc(mysqli_stmt_get_result($ps));
$hasPassword = !empty($r['has_pwd']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$action = $_POST['action'] ?? '';
	$new = trim($_POST['new_password'] ?? '');
	$confirm = trim($_POST['confirm_password'] ?? '');
	$old = trim($_POST['current_password'] ?? '');

	if ($new === '' || $confirm === '') {
		set_flash('Please fill in all required fields.', 'error');
		header('Location: student_profile.php');
		exit();
	}
	if (strlen($new) < 6) {
		set_flash('Password must be at least 6 characters.', 'error');
		header('Location: student_profile.php');
		exit();
	}
	if ($new !== $confirm) {
		set_flash('New password and confirmation do not match.', 'error');
		header('Location: student_profile.php');
		exit();
	}

	if ($hasPassword) {
		// Verify old password matches (legacy: plain text storage, so compare directly)
		$vs = mysqli_prepare($connect, 'SELECT password FROM STUDENT WHERE id = ?');
		mysqli_stmt_bind_param($vs, 'i', $student_id);
		mysqli_stmt_execute($vs);
		$vr = mysqli_fetch_assoc(mysqli_stmt_get_result($vs));
		$stored = $vr ? (string)$vr['password'] : '';
		if ($stored !== $old) {
			set_flash('Current password is incorrect.', 'error');
			header('Location: student_profile.php');
			exit();
		}
	}

	$us = mysqli_prepare($connect, 'UPDATE STUDENT SET password = ? WHERE id = ?');
	mysqli_stmt_bind_param($us, 'si', $new, $student_id);
	if (mysqli_stmt_execute($us)) {
		set_flash($hasPassword ? 'Password updated successfully.' : 'Password set successfully. You can now login with email + password.', 'success');
	} else {
		set_flash('Failed to update password. Please try again.', 'error');
	}
	header('Location: student_profile.php');
	exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>My Profile - Club Management System</title>
	<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-slate-50 via-slate-100 to-slate-200 min-h-screen">
	<!-- Breadcrumb Navigation -->
	<div class="absolute top-4 left-4 z-10">
		<?php 
		$breadcrumb = new Breadcrumb();
		$breadcrumb->addItem('Student Portal', '#', 'fas fa-user-graduate');
		$breadcrumb->addItem('Dashboard', 'student_dashboard.php', 'fas fa-tachometer-alt');
		$breadcrumb->addItem('My Profile', null, 'fas fa-user');
		echo $breadcrumb->render();
		?>
	</div>
	
	<header class="bg-[#0F172A] text-white shadow-md sticky top-0 z-50">
		<div class="container mx-auto px-4 py-3 flex items-center justify-between">
			<a href="student_dashboard.php" class="flex items-center space-x-3">
				<img src="img/logo.png" alt="Logo" class="h-10 w-10">
				<span class="font-semibold">Back to Dashboard</span>
			</a>
			<div class="flex items-center gap-4">
				<span class="text-sm opacity-90">Logged in as <?= htmlspecialchars($student_email) ?></span>
				<a href="logout.php" class="px-4 py-2 bg-[#F59E0B] text-white rounded-lg hover:bg-[#EF4444] transition-colors duration-300">Logout</a>
			</div>
		</div>
	</header>

	<main class="container mx-auto px-4 py-10 max-w-3xl">
		<h1 class="text-3xl font-bold text-slate-800 mb-6">My Profile</h1>

		<?php if ($flash): ?>
			<div class="mb-6 p-4 rounded-xl <?= $flash['type'] === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200' ?>">
				<?= htmlspecialchars($flash['message']) ?>
			</div>
		<?php endif; ?>

		<div class="grid grid-cols-1 gap-6">
			<!-- Basic info -->
			<div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
				<h2 class="text-xl font-semibold text-slate-800 mb-4">Basic Information</h2>
				<div class="grid md:grid-cols-2 gap-4 text-sm">
					<div>
						<label class="text-slate-500">Name</label>
						<div class="mt-1 font-medium text-slate-800"><?= htmlspecialchars($student_name) ?></div>
					</div>
					<div>
						<label class="text-slate-500">Email</label>
						<div class="mt-1 font-medium text-slate-800"><?= htmlspecialchars($student_email) ?></div>
					</div>
				</div>
			</div>

			<!-- Password section -->
			<div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
				<h2 class="text-xl font-semibold text-slate-800 mb-2">Password</h2>
				<p class="text-slate-600 text-sm mb-4">
					<?= $hasPassword ? 'Change your password below.' : 'You don\'t have a password yet. Set one to enable email + password login in addition to Google.' ?>
				</p>

				<form method="POST" class="grid grid-cols-1 gap-4 max-w-lg">
					<?php if ($hasPassword): ?>
					<div>
						<label class="block text-sm font-medium text-slate-700 mb-1">Current Password</label>
						<input type="password" name="current_password" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent" required>
					</div>
					<?php endif; ?>

					<div>
						<label class="block text-sm font-medium text-slate-700 mb-1">New Password</label>
						<input type="password" name="new_password" minlength="6" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent" required>
					</div>
					<div>
						<label class="block text-sm font-medium text-slate-700 mb-1">Confirm New Password</label>
						<input type="password" name="confirm_password" minlength="6" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent" required>
					</div>

					<div class="pt-2">
						<button type="submit" name="action" value="set_password" class="px-5 py-2.5 bg-gradient-to-r from-amber-500 to-rose-500 text-white rounded-lg hover:from-amber-600 hover:to-rose-600 transition-colors">Save Password</button>
						<a href="student_dashboard.php" class="ml-3 text-slate-600 hover:text-slate-800">Cancel</a>
					</div>
				</form>
			</div>
		</div>
	</main>
</body>
</html>
