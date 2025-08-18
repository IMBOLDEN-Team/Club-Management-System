<?php
session_start();

include 'index.php';
include 'header.php';

$clubId = isset($_GET['club_id']) ? (int)$_GET['club_id'] : 0;
if ($clubId <= 0) {
    echo '<main class="min-h-screen bg-gray-50 py-16"><div class="container mx-auto px-4"><div class="bg-white border rounded-xl p-8 text-center">Invalid club.</div></div></main>';
    include 'footer.php';
    exit;
}

// Club info
$sql = 'SELECT id, name, logo, created_date FROM CLUB WHERE id = ?';
$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, 'i', $clubId);
mysqli_stmt_execute($stmt);
$clubRes = mysqli_stmt_get_result($stmt);
$club = mysqli_fetch_assoc($clubRes);

if (!$club) {
    echo '<main class="min-h-screen bg-gray-50 py-16"><div class="container mx-auto px-4"><div class="bg-white border rounded-xl p-8 text-center">Club not found.</div></div></main>';
    include 'footer.php';
    exit;
}

$userIsStudent = isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'student';
$studentId = $userIsStudent ? (int)$_SESSION['user_id'] : 0;

// Is student a member of this club?
$isMember = false;
if ($userIsStudent) {
    $m = mysqli_prepare($connect, 'SELECT 1 FROM CLUB_PARTICIPANT WHERE student_id = ? AND club_id = ?');
    mysqli_stmt_bind_param($m, 'ii', $studentId, $clubId);
    mysqli_stmt_execute($m);
    $isMember = (bool) mysqli_fetch_row(mysqli_stmt_get_result($m));
}

// Activities
$actStmt = mysqli_prepare($connect, 'SELECT id, `name`, `start`, `end`, merit_point FROM CLUB_ACTIVITY WHERE club_id = ? ORDER BY `start` DESC');
mysqli_stmt_bind_param($actStmt, 'i', $clubId);
mysqli_stmt_execute($actStmt);
$activities = mysqli_stmt_get_result($actStmt);

// Which activities has student joined (map)
$joinedMap = [];
if ($userIsStudent) {
    $jStmt = mysqli_prepare($connect, 'SELECT ap.club_activity_id FROM ACTIVITY_PARTICIPANT ap JOIN CLUB_ACTIVITY ca ON ap.club_activity_id = ca.id WHERE ap.student_id = ? AND ca.club_id = ?');
    mysqli_stmt_bind_param($jStmt, 'ii', $studentId, $clubId);
    mysqli_stmt_execute($jStmt);
    $jr = mysqli_stmt_get_result($jStmt);
    while ($r = mysqli_fetch_assoc($jr)) { $joinedMap[(int)$r['club_activity_id']] = true; }
}
?>

<main class="min-h-screen bg-gray-50 py-10">
  <div class="container mx-auto px-4">
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-[#0F172A] mb-1"><?= htmlspecialchars($club['name']) ?></h1>
      <p class="text-sm text-[#64748B]">Established <?= date('M Y', strtotime($club['created_date'])) ?></p>
      <?php if (isset($_SESSION['flash'])): $f=$_SESSION['flash']; unset($_SESSION['flash']); ?>
        <div id="flash-message" class="mt-4 px-4 py-3 rounded-lg text-white <?= $f['type']==='success' ? 'bg-green-600' : 'bg-red-600' ?>"><?= htmlspecialchars($f['message']) ?></div>
      <?php endif; ?>
    </div>

    <div class="bg-white border rounded-2xl shadow-sm p-6">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-[#0F172A]">Activities</h2>
        <?php if ($userIsStudent): ?>
          <span class="text-xs text-[#64748B]">You are <?= $isMember ? '' : 'not ' ?>a member of this club</span>
        <?php endif; ?>
      </div>

      <?php if ($activities && mysqli_num_rows($activities) > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <?php while ($a = mysqli_fetch_assoc($activities)): $aid=(int)$a['id']; $joined = isset($joinedMap[$aid]); $now = new DateTime('now'); $end = new DateTime($a['end']); $started = (new DateTime($a['start'])) <= $now; $ended = $end <= $now; ?>
            <div class="relative bg-white border border-gray-200 rounded-2xl shadow hover:shadow-lg transition-all duration-200 p-5 overflow-hidden">
              <div class="absolute -right-10 -top-10 w-24 h-24 bg-amber-50 rounded-full"></div>
              <div class="flex items-center justify-between mb-3">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">#<?= $aid ?></span>
                <span class="text-xs text-gray-500"><?= date('M d, Y H:i', strtotime($a['start'])) ?></span>
              </div>
              <h3 class="text-lg font-semibold text-[#0F172A] mb-1"><?= htmlspecialchars($a['name']) ?></h3>
              <div class="text-sm text-[#64748B] mb-3">
                <div>Start: <?= date('Y-m-d H:i', strtotime($a['start'])) ?></div>
                <div>End: <?= date('Y-m-d H:i', strtotime($a['end'])) ?></div>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-xs font-medium px-2 py-1 bg-amber-100 text-amber-800 rounded">Merit: <?= (int)$a['merit_point'] ?></span>
                <?php if ($userIsStudent): ?>
                  <?php if ($joined): ?>
                    <form action="leave_activity.php" method="POST">
                      <input type="hidden" name="club_activity_id" value="<?= $aid ?>">
                      <button class="px-3 py-2 rounded-md bg-red-50 text-red-700 hover:bg-red-100 text-sm" <?= $started ? 'disabled' : '' ?>>Leave</button>
                    </form>
                  <?php else: ?>
                    <form action="join_activity.php" method="POST">
                      <input type="hidden" name="club_activity_id" value="<?= $aid ?>">
                      <button class="px-3 py-2 rounded-md text-white text-sm <?= $ended ? 'bg-gray-400 cursor-not-allowed' : 'bg-amber-500 hover:bg-amber-600' ?>" <?= $ended ? 'disabled' : '' ?>>Join</button>
                    </form>
                  <?php endif; ?>
                <?php endif; ?>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
      <?php else: ?>
        <div class="text-center py-16">
          <div class="text-5xl mb-4">📅</div>
          <p class="text-[#64748B]">No activities available yet.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</main>

<?php include 'footer.php'; ?>


