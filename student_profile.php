<?php
session_start();

// Require student login
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'student') {
	header('Location: login.php');
	exit();
}

require_once __DIR__ . '/config/config.php';

$studentId = (int)($_SESSION['user_id'] ?? 0);

// Fetch current student data (use only existing columns)
$student = [
	'username' => '',
	'email' => $_SESSION['username'] ?? '',
	'name' => $_SESSION['student_name'] ?? '',
	'phone' => '',
	'program' => ''
];

$stmt = mysqli_prepare($connect, 'SELECT username, email, name, phone, program, logo FROM STUDENT WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $studentId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$logoData = null;
$logoSrc = '';
if ($row = mysqli_fetch_assoc($res)) {
	$student = array_merge($student, $row);
	$logoData = $row['logo'] ?? null;
	if ($logoData) {
		$finfo = new finfo(FILEINFO_MIME_TYPE);
		$mime = $finfo ? $finfo->buffer($logoData) : 'image/png';
		if (strpos($mime, 'image/') !== 0) { $mime = 'image/png'; }
		$logoSrc = 'data:' . $mime . ';base64,' . base64_encode($logoData);
	}
}

$flash = null;

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$username = trim($_POST['username'] ?? '');
	$name = trim($_POST['name'] ?? '');
	$email = trim($_POST['email'] ?? '');
	$phone = trim($_POST['phone'] ?? '');
	$program = trim($_POST['program'] ?? '');
	$password = trim($_POST['password'] ?? '');

	$setParts = ['username = ?', 'name = ?', 'email = ?', 'phone = ?', 'program = ?'];
	$params = [$username, $name, $email, $phone, $program];
	$types = 'sssss';

	if ($password !== '') {
		$setParts[] = 'password = ?';
		$params[] = $password; // legacy plain text per existing system
		$types .= 's';
	}

	$avatarIndex = null;
	if (isset($_FILES['avatar']) && is_uploaded_file($_FILES['avatar']['tmp_name'])) {
		$setParts[] = 'logo = ?';
		$types .= 'b';
		// push a placeholder so bind_param has the same number of params as placeholders
		$blobPlaceholder = '';
		$params[] = $blobPlaceholder;
		$avatarIndex = strlen($types) - 1; // zero-based later
	}

	$types .= 'i';
	$params[] = $studentId;

	$sql = 'UPDATE STUDENT SET ' . implode(', ', $setParts) . ' WHERE id = ?';
	$update = mysqli_prepare($connect, $sql);
	// bind_param needs references; build array accordingly
	$bindValues = [];
	$bindValues[] = &$types;
	foreach ($params as $key => $value) {
		$bindValues[] = &$params[$key];
	}
	call_user_func_array([$update, 'bind_param'], $bindValues);

	if ($avatarIndex !== null) {
		$avatarData = file_get_contents($_FILES['avatar']['tmp_name']);
		// send_long_data uses parameter index starting from 0
		mysqli_stmt_send_long_data($update, $avatarIndex, $avatarData);
	}

	if (mysqli_stmt_execute($update)) {
		$flash = ['type' => 'success', 'message' => 'Profile updated successfully.'];
		$student['username'] = $username;
		$student['name'] = $name;
		$student['email'] = $email;
		$student['phone'] = $phone;
		$student['program'] = $program;
		$_SESSION['student_name'] = $name;
		$_SESSION['username'] = $email;
		if (isset($avatarData)) { $logoData = $avatarData; }
	} else {
		$flash = ['type' => 'error', 'message' => 'Failed to update profile.'];
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>My Profile - Club Management System</title>
	<script src="https://cdn.tailwindcss.com"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
	<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
	<link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
	<link rel="dns-prefetch" href="//cdn.jsdelivr.net">
    <style>
        .active-section { background-color: #1E293B; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
	<div class="flex h-screen">
		<!-- Sidebar -->
		<div class="w-64 bg-[#0F172A] text-white shadow-lg">
			<div class="p-6 h-full flex flex-col">
				<div class="flex items-center space-x-3 mb-8">
					<img src="img/logo.png" alt="Logo" class="w-10 h-10" />
					<div>
						<h1 class="text-xl font-bold text-white">Student Panel</h1>
						<p class="text-sm text-gray-400">Profile</p>
					</div>
				</div>
				<nav class="flex-1 space-y-2">
					<a href="student_dashboard.php#dashboard" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-[#1E293B] transition-colors duration-200"><i class="fas fa-tachometer-alt text-gray-400"></i><span class="font-medium">Dashboard</span></a>
					<a href="student_dashboard.php#clubs" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-[#1E293B] transition-colors duration-200"><i class="fas fa-users text-gray-400"></i><span class="font-medium">My Clubs</span></a>
					<a href="student_dashboard.php#activities" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-[#1E293B] transition-colors duration-200"><i class="fas fa-calendar-alt text-gray-400"></i><span class="font-medium">My Activities</span></a>
					<a href="student_profile.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors duration-200 active-section"><i class="fas fa-user text-gray-400"></i><span class="font-medium">My Profile</span></a>
				</nav>
				<div class="mt-auto">
					<a href="logout.php" class="flex items-center justify-center space-x-3 px-4 py-3 rounded-lg bg-red-600 hover:bg-red-700 transition-colors duration-200 text-white"><i class="fas fa-sign-out-alt"></i><span class="font-medium">Logout</span></a>
				</div>
			</div>
		</div>

		<!-- Main content -->
		<div class="flex-1 overflow-auto">
			<div class="p-6 border-b bg-white">
				<div class="flex items-center justify-between">
					<div>
						<h2 class="text-2xl font-bold text-gray-800">Edit Profile</h2>
						<p class="text-gray-500 text-sm">Update your information</p>
					</div>
					<div class="hidden sm:flex gap-3">
						<a href="student_dashboard.php" class="px-4 py-2 rounded-lg bg-gray-600 hover:bg-gray-700 text-white text-sm">Back to Dashboard</a>
					</div>
				</div>
			</div>

			<main class="p-8">
				<?php if ($flash): ?>
				<script>
					Swal.fire({ icon: '<?= $flash['type'] ?>', title: '<?= $flash['type'] === 'success' ? 'Success' : 'Error' ?>', text: '<?= htmlspecialchars($flash['message'], ENT_QUOTES) ?>', timer: 3000, showConfirmButton: false });
				</script>
				<?php endif; ?>

				<div class="bg-white shadow rounded-2xl border border-gray-100">
					<div class="px-6 py-4 border-b flex items-center justify-between">
						<h3 class="text-lg font-semibold text-gray-800">Profile Information</h3>
						<p class="text-xs text-gray-500">Fields marked with * are required</p>
					</div>
					<form method="POST" enctype="multipart/form-data" class="p-6">
						<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
							<!-- Left: avatar and summary -->
							<div class="lg:col-span-1">
								<div class="rounded-xl border bg-gray-50 p-4 text-center">
									<!-- Clickable avatar -->
									<label for="avatar-input" class="group relative inline-flex items-center justify-center cursor-pointer rounded-full">
										<?php if ($logoSrc): ?>
											<img id="avatar-preview" src="<?= $logoSrc ?>" alt="Avatar" class="block h-28 w-28 rounded-full object-cover border shadow-sm group-hover:opacity-80 transition"/>
										<?php else: ?>
											<div class="h-28 w-28 rounded-full border bg-white flex items-center justify-center text-gray-400 shadow-sm group-hover:opacity-80 transition">
												<i class="fas fa-user text-3xl"></i>
											</div>
										<?php endif; ?>
										<span class="absolute inset-0 flex items-center justify-center rounded-full text-xs font-medium text-white bg-black/40 opacity-0 group-hover:opacity-100 transition">Change</span>
									</label>
									<p class="mt-3 text-xs text-gray-500">Click the avatar to upload a new image.</p>
									<input id="avatar-input" type="file" name="avatar" accept="image/*" class="hidden" />
								</div>
							</div>

							<!-- Right: form fields -->
							<div class="lg:col-span-2">
								<div class="grid md:grid-cols-2 gap-6">
									<div>
										<label class="block text-sm font-medium text-gray-700">Name *</label>
										<input type="text" name="name" value="<?= htmlspecialchars($student['name']) ?>" class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
									</div>
									<div>
										<label class="block text-sm font-medium text-gray-700">Username *</label>
										<input type="text" name="username" value="<?= htmlspecialchars($student['username']) ?>" class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
									</div>
									<div class="md:col-span-2">
										<label class="block text-sm font-medium text-gray-700">Email *</label>
										<input type="email" name="email" value="<?= htmlspecialchars($student['email']) ?>" class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
									</div>
									<div>
										<label class="block text-sm font-medium text-gray-700">Phone</label>
										<input type="text" name="phone" value="<?= htmlspecialchars($student['phone']) ?>" class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g. 0123456789">
									</div>
									<div>
										<label class="block text-sm font-medium text-gray-700">Program</label>
										<input type="text" name="program" value="<?= htmlspecialchars($student['program']) ?>" class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g. Diploma In Computer Science">
									</div>
									<div class="md:col-span-2">
										<label class="block text-sm font-medium text-gray-700">Password</label>
										<input type="password" name="password" placeholder="Leave blank to keep current" class="mt-1 block w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
										<p class="mt-1 text-xs text-gray-500">Leave this blank if you don't want to change the password.</p>
									</div>
								</div>

								<div class="mt-8 flex items-center justify-end gap-3">
									<a href="student_dashboard.php" class="px-4 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</a>
									<button type="submit" class="px-5 py-2.5 rounded-md bg-blue-600 hover:bg-blue-700 text-white shadow">Update</button>
								</div>
							</div>
						</div>
					</form>
					<script>
					(function(){
						var input = document.getElementById('avatar-input');
						var preview = document.getElementById('avatar-preview');
						if (!input) return;
						input.addEventListener('change', function(){
							if (this.files && this.files[0] && preview) {
								var reader = new FileReader();
								reader.onload = function(e){ if (preview) { preview.src = e.target.result; } };
								reader.readAsDataURL(this.files[0]);
							}
						});
					})();
					</script>
				</div>
			</main>
		</div>
	</div>
</body>
</html>


