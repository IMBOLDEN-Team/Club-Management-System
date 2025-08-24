
<?php 
session_start();
include "header.php" 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    
 <?php include "index.php" ?>
    
    <!-- Main Content Section -->
    <main class="bg-gradient-to-br from-[#F1F5F9] via-[#E2E8F0] to-[#CBD5E1] min-h-screen relative overflow-hidden">
        
        <!-- Background Animations for Main Content Only -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-gradient-to-r from-blue-200/20 to-purple-200/20 rounded-full blur-3xl animate-float"></div>
            <div class="absolute -bottom-32 -right-32 w-80 h-80 bg-gradient-to-r from-orange-200/20 to-pink-200/20 rounded-full blur-3xl animate-float-delayed"></div>
            
            <!-- Animated icons -->
            <div class="absolute top-20 right-20 text-4xl opacity-10 animate-float-icon">🏛️</div>
            <div class="absolute bottom-40 left-20 text-3xl opacity-15 animate-pulse-icon">⭐</div>
            <div class="absolute top-1/2 left-10 text-4xl opacity-10 animate-bounce-icon">🎯</div>
        </div>
        
        <div class="container mx-auto px-4 py-8 relative z-10">
            
            <!-- Flash Messages -->
            <?php if (isset($_SESSION['flash'])): ?>
                <?php $flash = $_SESSION['flash']; unset($_SESSION['flash']); ?>
                <div class="max-w-2xl mx-auto mb-6 p-4 rounded-xl <?= $flash['type'] === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700' ?> animate-fade-in">
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>
            
            <!-- Header Section -->
            <div class="text-center mb-12">
                <h1 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-[#0F172A] to-[#334155] bg-clip-text text-transparent mb-4">
                    Club Directory
                </h1>
                <div class="w-24 h-1 bg-gradient-to-r from-[#F59E0B] to-[#EF4444] mx-auto rounded-full mb-4"></div>
                <p class="text-[#64748B] text-lg max-w-2xl mx-auto">
                    Discover and join the clubs that match your interests and passions
                </p>
            </div>

            <!-- Search Bar -->
            <div class="max-w-2xl mx-auto mb-12">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-[#64748B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        id="searchInput"
                        class="block w-full pl-10 pr-3 py-4 border border-[#E2E8F0] rounded-2xl leading-5 bg-white/80 backdrop-blur-sm placeholder-[#64748B] focus:outline-none focus:ring-2 focus:ring-[#F59E0B] focus:border-transparent transition-all duration-300 text-lg"
                        placeholder="Search clubs..."
                        onkeyup="searchClubs()"
                    >
                </div>
                <div id="searchResults" class="text-center mt-4">
                    <span id="resultCount" class="text-[#64748B] font-medium"></span>
                </div>
            </div>

            <!-- Club Cards Container -->
            <div id="clubsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                <?php
                if (isset($connect)) {
                    $query = "SELECT id, name, logo, created_date FROM CLUB ORDER BY name ASC";
                    $result = mysqli_query($connect, $query);
                    
                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($club = mysqli_fetch_assoc($result)) {
                            $club_id = $club['id'];
                            $club_name = htmlspecialchars($club['name']);
                            $created_date = date('M Y', strtotime($club['created_date']));
                            
                            // Check student's status for this club
                            $join_status = 'none';
                            $join_button_text = 'Join Club';
                            $join_button_class = 'bg-gradient-to-r from-[#F59E0B] to-[#EF4444] hover:from-[#EF4444] hover:to-[#F59E0B]';
                            $join_button_disabled = false;
                            
                            if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'student') {
                                $student_id = $_SESSION['user_id'];
                                
                                // Ensure CLUB_JOIN_REQUEST table exists
                                $create_table_query = "CREATE TABLE IF NOT EXISTS CLUB_JOIN_REQUEST (
                                    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                    student_id INT NOT NULL,
                                    club_id INT NOT NULL,
                                    status ENUM('pending','approved','rejected') DEFAULT 'pending',
                                    requested_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                                    responded_at DATETIME NULL,
                                    UNIQUE KEY uniq_request (student_id, club_id)
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
                                mysqli_query($connect, $create_table_query);
                                
                                // Check if already a member
                                $member_query = "SELECT * FROM CLUB_PARTICIPANT WHERE student_id = ? AND club_id = ?";
                                $member_stmt = mysqli_prepare($connect, $member_query);
                                mysqli_stmt_bind_param($member_stmt, 'ii', $student_id, $club_id);
                                mysqli_stmt_execute($member_stmt);
                                $member_result = mysqli_stmt_get_result($member_stmt);
                                
                                if (mysqli_num_rows($member_result) > 0) {
                                    $join_status = 'member';
                                    $join_button_text = 'Member';
                                    $join_button_class = 'bg-gray-400 cursor-not-allowed';
                                    $join_button_disabled = true;
                                } else {
                                    // Check if there's a pending request
                                    $request_query = "SELECT * FROM CLUB_JOIN_REQUEST WHERE student_id = ? AND club_id = ? AND status = 'pending'";
                                    $request_stmt = mysqli_prepare($connect, $request_query);
                                    mysqli_stmt_bind_param($request_stmt, 'ii', $student_id, $club_id);
                                    mysqli_stmt_execute($request_stmt);
                                    $request_result = mysqli_stmt_get_result($request_stmt);
                                    
                                    if (mysqli_num_rows($request_result) > 0) {
                                        $join_status = 'pending';
                                        $join_button_text = 'Pending Approval';
                                        $join_button_class = 'bg-yellow-500 cursor-not-allowed';
                                        $join_button_disabled = true;
                                    }
                                }
                            }
                            
                            // Handle logo - convert BLOB to base64 if exists
                            $logo_src = '';
                            if (!empty($club['logo'])) {
                                $logo_src = 'data:image/jpeg;base64,' . base64_encode($club['logo']);
                            }
                            ?>
                            <div class="club-card group bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 border border-white/50 hover:border-[#F59E0B]/30 overflow-hidden" data-club-name="<?= strtolower($club_name) ?>">
                                
                                <!-- Club Logo -->
                                <div class="relative h-48 bg-gradient-to-br from-[#F59E0B]/10 to-[#EF4444]/10 flex items-center justify-center overflow-hidden">
                                    <?php if (!empty($logo_src)): ?>
                                        <img src="<?= $logo_src ?>" alt="<?= $club_name ?> Logo" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                    <?php else: ?>
                                        <div class="w-24 h-24 bg-gradient-to-br from-[#F59E0B] to-[#EF4444] rounded-full flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                            <span class="text-white font-bold text-2xl">
                                                <?= strtoupper(substr($club_name, 0, 2)) ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Overlay gradient -->
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                </div>
                                
                                <!-- Card Content -->
                                <div class="p-6">
                                    <h3 class="text-xl font-bold text-[#0F172A] mb-2 group-hover:text-[#F59E0B] transition-colors duration-300 line-clamp-2">
                                        <?= $club_name ?>
                                    </h3>
                                    
                                    <div class="flex items-center text-[#64748B] mb-4">
                                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="text-sm">Est. <?= $created_date ?></span>
                                    </div>
                                    
                                    <!-- Action Buttons -->
                                    <div class="space-y-3">
                                        <!-- View Details Button -->
                                        <button 
                                            onclick="viewClubDetails(<?= $club_id ?>)"
                                            class="w-full bg-gradient-to-r from-[#0F172A] to-[#334155] text-white px-6 py-3 rounded-xl font-semibold hover:from-[#F59E0B] hover:to-[#EF4444] transition-all duration-300 transform hover:scale-105 active:scale-95 shadow-lg hover:shadow-xl relative overflow-hidden group/btn"
                                        >
                                            <span class="relative z-10">View Details</span>
                                            <div class="absolute inset-0 bg-white/20 transform scale-x-0 group-hover/btn:scale-x-100 transition-transform duration-500 origin-left"></div>
                                        </button>
                                        
                                        <!-- Join Club Button (only for students) -->
                                        <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'student'): ?>
                                            <?php if ($join_status === 'none'): ?>
                                                <form method="POST" action="join_club.php" class="w-full">
                                                    <input type="hidden" name="club_id" value="<?= $club_id ?>">
                                                    <button 
                                                        type="submit"
                                                        class="w-full <?= $join_button_class ?> text-white px-6 py-3 rounded-xl font-semibold transition-all duration-300 transform hover:scale-105 active:scale-95 shadow-lg hover:shadow-xl relative overflow-hidden group/btn"
                                                    >
                                                        <span class="relative z-10"><?= $join_button_text ?></span>
                                                        <div class="absolute inset-0 bg-white/20 transform scale-x-0 group-hover/btn:scale-x-100 transition-transform duration-500 origin-left"></div>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <button 
                                                    disabled
                                                    class="w-full <?= $join_button_class ?> text-white px-6 py-3 rounded-xl font-semibold transition-all duration-300 shadow-lg relative overflow-hidden"
                                                >
                                                    <span class="relative z-10"><?= $join_button_text ?></span>
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<div class="col-span-full text-center py-16">
                                <div class="text-6xl mb-4">🏛️</div>
                                <h3 class="text-2xl font-bold text-[#0F172A] mb-2">No Clubs Found</h3>
                                <p class="text-[#64748B]">No clubs are registered in the system yet.</p>
                              </div>';
                    }
                } else {
                    echo '<div class="col-span-full text-center py-16">
                            <div class="text-6xl mb-4">⚠️</div>
                            <h3 class="text-2xl font-bold text-[#EF4444] mb-2">Connection Error</h3>
                            <p class="text-[#64748B]">Unable to connect to the database.</p>
                          </div>';
                }
                ?>
            </div>

            <!-- No Results Message -->
            <div id="noResults" class="hidden text-center py-16">
                <div class="text-6xl mb-4">🔍</div>
                <h3 class="text-2xl font-bold text-[#0F172A] mb-2">No Results Found</h3>
                <p class="text-[#64748B]">Try adjusting your search terms or browse all clubs.</p>
            </div>
        </div>
    </main>
    
    <!-- Footer Section -->
    <?php include "footer.php" ?>

    <script>
        // Search functionality
        function searchClubs() {
            const searchInput = document.getElementById('searchInput');
            const searchTerm = searchInput.value.toLowerCase();
            const clubCards = document.querySelectorAll('.club-card');
            const noResults = document.getElementById('noResults');
            const resultCount = document.getElementById('resultCount');
            const clubsContainer = document.getElementById('clubsContainer');
            
            let visibleCount = 0;
            
            clubCards.forEach(card => {
                const clubName = card.getAttribute('data-club-name');
                
                if (clubName.includes(searchTerm)) {
                    card.style.display = 'block';
                    card.classList.add('animate-fade-in');
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                    card.classList.remove('animate-fade-in');
                }
            });
            
            // Update result count
            if (searchTerm === '') {
                resultCount.textContent = '';
                noResults.classList.add('hidden');
                clubsContainer.classList.remove('hidden');
            } else {
                resultCount.textContent = `${visibleCount} club${visibleCount !== 1 ? 's' : ''} found`;
                
                if (visibleCount === 0) {
                    noResults.classList.remove('hidden');
                    clubsContainer.classList.add('hidden');
                } else {
                    noResults.classList.add('hidden');
                    clubsContainer.classList.remove('hidden');
                }
            }
        }
        
        // Redirect to club details
        function viewClubDetails(clubId) {
            window.location.href = `club.php?club_id=${clubId}`;
        }
        
        // Add entrance animations
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.club-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.classList.add('animate-slide-up');
            });
        });
    </script>

    <style>
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slide-up {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

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

        @keyframes float-icon {
            0%, 100% { transform: translateY(0px) rotate(0deg) scale(1); }
            33% { transform: translateY(-15px) rotate(5deg) scale(1.1); }
            66% { transform: translateY(8px) rotate(-3deg) scale(0.95); }
        }

        @keyframes bounce-icon {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0) scale(1); }
            40% { transform: translateY(-10px) scale(1.1); }
            60% { transform: translateY(-3px) scale(1.05); }
        }

        @keyframes pulse-icon {
            0%, 100% { transform: scale(1); opacity: 0.15; }
            50% { transform: scale(1.2); opacity: 0.3; }
        }

        .animate-fade-in {
            animation: fade-in 0.5s ease-out;
        }

        .animate-slide-up {
            animation: slide-up 0.8s ease-out forwards;
            opacity: 0;
        }

        .animate-float {
            animation: float 8s ease-in-out infinite;
        }

        .animate-float-delayed {
            animation: float-delayed 10s ease-in-out infinite;
        }

        .animate-float-icon {
            animation: float-icon 6s ease-in-out infinite;
        }

        .animate-bounce-icon {
            animation: bounce-icon 4s ease-in-out infinite;
        }

        .animate-pulse-icon {
            animation: pulse-icon 3s ease-in-out infinite;
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</body>
</html>