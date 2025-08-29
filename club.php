<?php
session_start();

include 'index.php';
$minimal = isset($_GET['embed']) && $_GET['embed'] === '1';
if (!$minimal) {
include 'header.php';
}

$clubId = isset($_GET['club_id']) ? (int)$_GET['club_id'] : 0;
if ($clubId <= 0) {
    echo '<main class="min-h-screen bg-gray-50 py-16"><div class="container mx-auto px-4"><div class="bg-white border rounded-xl p-8 text-center">Invalid club.</div></div></main>';
    include 'footer.php';
    exit;
}

// Club info
$sql = 'SELECT id, name, logo, created_date, member_limit FROM CLUB WHERE id = ?';
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

// Get current member count
$memberCountStmt = mysqli_prepare($connect, 'SELECT COUNT(*) as member_count FROM CLUB_PARTICIPANT WHERE club_id = ?');
mysqli_stmt_bind_param($memberCountStmt, 'i', $clubId);
mysqli_stmt_execute($memberCountStmt);
$memberCountRes = mysqli_stmt_get_result($memberCountStmt);
$memberCount = mysqli_fetch_assoc($memberCountRes)['member_count'];

// Check if club is at capacity
$isAtCapacity = $club['member_limit'] !== null && $memberCount >= $club['member_limit'];
?>

<main class="min-h-screen bg-gray-50 py-10">
  <div class="container mx-auto px-4">
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-[#0F172A] mb-1"><?= htmlspecialchars($club['name']) ?></h1>
      <p class="text-sm text-[#64748B]">Established <?= date('M Y', strtotime($club['created_date'])) ?></p>
      
      <!-- Member Limit Information -->
      <div class="mt-4 flex flex-wrap gap-4 items-center">
        <div class="flex items-center space-x-2 px-4 py-2 bg-blue-50 rounded-full border border-blue-200">
          <i class="fas fa-users text-blue-600"></i>
          <span class="text-blue-700 font-medium"><?= $memberCount ?> member<?= $memberCount !== 1 ? 's' : '' ?></span>
        </div>
        
        <?php if ($club['member_limit'] !== null): ?>
          <div class="flex items-center space-x-2 px-4 py-2 <?= $isAtCapacity ? 'bg-red-50 border-red-200' : 'bg-green-50 border-green-200' ?> rounded-full border">
            <i class="fas <?= $isAtCapacity ? 'fa-exclamation-triangle text-red-600' : 'fa-user-plus text-green-600' ?>"></i>
            <span class="<?= $isAtCapacity ? 'text-red-700' : 'text-green-700' ?> font-medium">
              <?= $memberCount ?>/<?= $club['member_limit'] ?> limit
              <?php if ($isAtCapacity): ?>
                - Club is full!
              <?php else: ?>
                (<?= $club['member_limit'] - $memberCount ?> spots left)
              <?php endif; ?>
            </span>
          </div>
        <?php else: ?>
          <div class="flex items-center space-x-2 px-4 py-2 bg-gray-50 rounded-full border border-gray-200">
            <i class="fas fa-infinity text-gray-600"></i>
            <span class="text-gray-700 font-medium">Unlimited capacity</span>
          </div>
        <?php endif; ?>
      </div>
      
      <?php if (isset($_SESSION['flash'])): $f=$_SESSION['flash']; unset($_SESSION['flash']); ?>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
          (function(){
            Swal.fire({
              icon: '<?= $f['type']==='success' ? 'success' : 'error' ?>',
              title: '<?= $f['type']==='success' ? 'Success' : 'Error' ?>',
              text: '<?= htmlspecialchars($f['message'], ENT_QUOTES) ?>',
              confirmButtonText: 'OK'
            });
          })();
        </script>
      <?php endif; ?>
    </div>

    <div class="bg-white border rounded-2xl shadow-sm p-6">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-[#0F172A]">Activities</h2>
        <?php if ($userIsStudent): ?>
          <span class="text-xs text-[#64748B]">You are <?= $isMember ? '' : 'not ' ?>a member of this club</span>
        <?php endif; ?>
      </div>
      
      <?php if ($userIsStudent && !$isMember && $isAtCapacity): ?>
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
          <div class="flex items-center space-x-2">
            <i class="fas fa-exclamation-triangle text-red-600"></i>
            <span class="text-red-700 font-medium">Club is at capacity</span>
          </div>
          <p class="text-red-600 text-sm mt-1">This club has reached its member limit. You cannot join activities until spots become available.</p>
        </div>
      <?php endif; ?>

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
              <div class="text-xs text-[#64748B] mb-1">By <?= htmlspecialchars($club['name']) ?></div>
              <div class="text-sm text-[#64748B] mb-3">
                <div>Start: <?= date('Y-m-d H:i', strtotime($a['start'])) ?></div>
                <div>End: <?= date('Y-m-d H:i', strtotime($a['end'])) ?></div>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-xs font-medium px-2 py-1 bg-amber-100 text-amber-800 rounded">Merit: <?= (int)$a['merit_point'] ?></span>
                <?php if ($userIsStudent): ?>
                  <?php if ($joined): ?>
                    <form action="leave_activity.php" method="POST" class="leave-form">
                      <input type="hidden" name="club_activity_id" value="<?= $aid ?>">
                      <button class="px-3 py-2 rounded-md bg-red-50 text-red-700 hover:bg-red-100 text-sm" <?= $ended ? 'disabled' : '' ?>>Leave</button>
                    </form>
                  <?php else: ?>
                    <form action="join_activity.php" method="POST">
                      <input type="hidden" name="club_activity_id" value="<?= $aid ?>">
                      <button class="px-3 py-2 rounded-md text-white text-sm <?= ($ended || (!$isMember && $isAtCapacity)) ? 'bg-gray-400 cursor-not-allowed' : 'bg-amber-500 hover:bg-amber-600' ?>" <?= ($ended || (!$isMember && $isAtCapacity)) ? 'disabled' : '' ?>>
                        <?php if (!$isMember && $isAtCapacity): ?>
                          Club Full
                        <?php else: ?>
                          Join
                        <?php endif; ?>
                      </button>
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

<?php if (!$minimal) { include 'footer.php'; } ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  // Confirm before leaving an activity
  document.querySelectorAll('form.leave-form').forEach(function(form){
    form.addEventListener('submit', function(e){
      e.preventDefault();
      Swal.fire({
        title: 'Leave this activity?',
        text: 'You can re-join later if the activity has not ended.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, leave',
      }).then(function(result){
        if (result.isConfirmed) form.submit();
      });
    });
  });
</script>


