<?php
session_start();

// Only students can access
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: login.php');
    exit;
}

include 'index.php';
$studentId = (int)$_SESSION['user_id'];

// Joined activities
$joinedSql = "
    SELECT ca.id, ca.name, ca.start, ca.end, ca.merit_point, cl.name AS club_name
    FROM ACTIVITY_PARTICIPANT ap
    JOIN CLUB_ACTIVITY ca ON ap.club_activity_id = ca.id
    JOIN CLUB cl ON ca.club_id = cl.id
    WHERE ap.student_id = ?
    ORDER BY ca.start DESC
";
$stmt = mysqli_prepare($connect, $joinedSql);
mysqli_stmt_bind_param($stmt, 'i', $studentId);
mysqli_stmt_execute($stmt);
$joined = mysqli_stmt_get_result($stmt);

// Available activities (from clubs the student joined, not yet joined the activity)
$availableSql = "
    SELECT ca.id, ca.name, ca.start, ca.end, ca.merit_point, cl.name AS club_name
    FROM CLUB_PARTICIPANT cp
    JOIN CLUB_ACTIVITY ca ON cp.club_id = ca.club_id
    JOIN CLUB cl ON cl.id = cp.club_id
    LEFT JOIN ACTIVITY_PARTICIPANT ap
      ON ap.student_id = cp.student_id AND ap.club_activity_id = ca.id
    WHERE cp.student_id = ? AND ap.student_id IS NULL AND ca.end > NOW()
    ORDER BY ca.start ASC
";
$stmt2 = mysqli_prepare($connect, $availableSql);
mysqli_stmt_bind_param($stmt2, 'i', $studentId);
mysqli_stmt_execute($stmt2);
$available = mysqli_stmt_get_result($stmt2);

include 'header.php';
?>

<main class="min-h-screen bg-gray-50 py-10">
  <div class="container mx-auto px-4">
    <div class="mb-6">
      <h1 class="text-3xl font-bold text-[#0F172A]">Student Dashboard</h1>
      <p class="text-[#64748B]">Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'student') ?>.</p>
      <?php if (isset($_SESSION['flash'])): $f=$_SESSION['flash']; unset($_SESSION['flash']); ?>
        <div id="flash-message" class="mt-4 px-4 py-3 rounded-lg text-white <?= $f['type']==='success' ? 'bg-green-600' : 'bg-red-600' ?>"><?= htmlspecialchars($f['message']) ?></div>
      <?php endif; ?>
    </div>

    <!-- Joined Activities -->
    <section class="mb-10">
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-xl font-semibold text-[#0F172A]">Joined Activities</h2>
      </div>
      <?php if ($joined && mysqli_num_rows($joined) > 0): ?>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Activity</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Club</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Start</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">End</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Merit</th>
                  <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Action</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-100">
                <?php while ($row = mysqli_fetch_assoc($joined)): $aid=(int)$row['id']; $startTs=strtotime($row['start']); ?>
                  <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                      <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($row['name']) ?></div>
                      <div class="text-xs text-gray-400">ID #<?= $aid ?></div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($row['club_name']) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= date('Y-m-d H:i', $startTs) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= date('Y-m-d H:i', strtotime($row['end'])) ?></td>
                    <td class="px-6 py-4">
                      <span class="text-xs font-semibold px-2 py-1 rounded bg-amber-100 text-amber-800"><?= (int)$row['merit_point'] ?> pts</span>
                    </td>
                    <td class="px-6 py-4 text-right">
                      <form action="leave_activity.php" method="POST">
                        <input type="hidden" name="club_activity_id" value="<?= $aid ?>">
                        <button type="submit" class="inline-flex items-center px-3 py-2 rounded-md bg-red-50 text-red-700 hover:bg-red-100 text-sm" <?= (time() >= $startTs) ? 'disabled' : '' ?>>Leave</button>
                      </form>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php else: ?>
        <div class="bg-white border rounded-xl p-6 text-[#64748B]">You haven't joined any activities yet.</div>
      <?php endif; ?>
    </section>

    <!-- Available Activities -->
    <section>
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-xl font-semibold text-[#0F172A]">Available Activities</h2>
      </div>
      <?php if ($available && mysqli_num_rows($available) > 0): ?>
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Activity</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Club</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Start</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">End</th>
                  <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Merit</th>
                  <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 uppercase tracking-wider">Action</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-100">
                <?php while ($a = mysqli_fetch_assoc($available)): $aid=(int)$a['id']; $endTs=strtotime($a['end']); ?>
                  <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                      <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($a['name']) ?></div>
                      <div class="text-xs text-gray-400">ID #<?= $aid ?></div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($a['club_name']) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= date('Y-m-d H:i', strtotime($a['start'])) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-600"><?= date('Y-m-d H:i', $endTs) ?></td>
                    <td class="px-6 py-4">
                      <span class="text-xs font-semibold px-2 py-1 rounded bg-amber-100 text-amber-800"><?= (int)$a['merit_point'] ?> pts</span>
                    </td>
                    <td class="px-6 py-4 text-right">
                      <form action="join_activity.php" method="POST">
                        <input type="hidden" name="club_activity_id" value="<?= $aid ?>">
                        <button class="inline-flex items-center px-3 py-2 rounded-md text-white text-sm <?= (time() >= $endTs) ? 'bg-gray-400 cursor-not-allowed' : 'bg-amber-500 hover:bg-amber-600' ?>" <?= (time() >= $endTs) ? 'disabled' : '' ?>>Join</button>
                      </form>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php else: ?>
        <div class="bg-white border rounded-xl p-6 text-[#64748B]">No available activities right now.</div>
      <?php endif; ?>
    </section>
  </div>
</main>

<?php include 'footer.php'; ?>
