
<?php include "header.php" ?>

<main class="min-h-screen">

    <!-- Scroll to top button -->
    <button id="scrollToTopBtn" class="fixed bottom-6 right-6 bg-[#0F172A] text-white p-3 rounded-full shadow-lg opacity-0 invisible transition-all duration-300 hover:bg-[#334155]">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
        </svg>
    </button>

    <!-- Overview Section with Custom Background Image -->
    <section id="overview" class="min-h-screen flex items-center justify-center bg-cover bg-center bg-no-repeat px-4 sm:px-6 lg:px-8" style="background-image: linear-gradient(rgba(15, 23, 42, 0.6), rgba(15, 23, 42, 0.6)), url('img/bg.png');">
        <div class="max-w-7xl mx-auto text-center text-white py-16">
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold mb-6">Club Management System</h1>
            <p class="text-lg sm:text-xl max-w-3xl mx-auto text-[#F1F5F9]">
                Welcome to our comprehensive platform for managing college clubs and activities. 
                Track events, earn merit points, and enhance your college experience.
            </p>
        </div>
    </section>
    <!-- bg-[#0F172A] -->
    <!-- Latest Activities Section -->
    <section class="min-h-screen bg-[#F1F5F9] py-16 px-4 sm:px-6 lg:px-8 flex items-center">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-3xl font-bold text-[#0F172A] mb-12 text-center">Latest Activities</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php include "index.php" ?>
                <?php
                $hasActivities = false;
                
                // Only try database operations if connection exists
                if (isset($connect)) {
                    $query = "SELECT ca.name, ca.start, ca.end, ca.merit_point, c.name AS club_name 
                            FROM CLUB_ACTIVITY ca 
                            JOIN CLUB c ON ca.club_id = c.id 
                            ORDER BY ca.start DESC 
                            LIMIT 3";
                    $result = mysqli_query($connect, $query);
                    
                    if ($result && mysqli_num_rows($result) > 0) {
                        $hasActivities = true;
                        while ($row = mysqli_fetch_assoc($result)) {
                            $start_date = date('M j, Y', strtotime($row['start']));
                            $end_date = date('M j, Y', strtotime($row['end']));
                            ?>
                            <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300">
                                <div class="flex justify-between items-start mb-4">
                                    <h3 class="text-xl font-semibold text-[#0F172A]"><?= htmlspecialchars($row['name']) ?></h3>
                                    <span class="bg-[#F59E0B] text-[#0F172A] px-3 py-1 rounded-full text-sm font-medium">
                                        <?= htmlspecialchars($row['merit_point']) ?> points
                                    </span>
                                </div>
                                <p class="text-[#334155] mb-2">Organized by: <?= htmlspecialchars($row['club_name']) ?></p>
                                <p class="text-[#334155]">
                                    <span class="font-medium">Date:</span> <?= $start_date ?> - <?= $end_date ?>
                                </p>
                                <button class="mt-4 bg-[#0F172A] text-white px-4 py-2 rounded-md hover:bg-[#334155] transition-colors duration-300 w-full">
                                    View Details
                                </button>
                            </div>
                            <?php
                        }
                    }
                }
                
                if (!$hasActivities): ?>
                    <div class="col-span-3 text-center">
                        <p class="text-3xl font-bold text-black mb-4">No activities for the time being.</p>
                        <p class="text-xl text-[#0F172A]">Stay tuned for upcoming events!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- About Us Section -->
    <section id="about" class="min-h-screen bg-white py-16 px-4 sm:px-6 lg:px-8 flex items-center">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-3xl font-bold text-[#0F172A] mb-12 text-center">Our Story</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
                <!-- Developer Cards -->
                <div class="bg-[#F1F5F9] p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold text-[#0F172A] mb-2">Asmawi Aiman Mohd Sani</h3>
                    <p class="text-[#334155]">ICS23-08-017</p>
                </div>
                
                <div class="bg-[#F1F5F9] p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold text-[#0F172A] mb-2">Muhammad Farid Bin Abu Samah</h3>
                    <p class="text-[#334155]">ICS23-08-032</p>
                </div>
                
                <div class="bg-[#F1F5F9] p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold text-[#0F172A] mb-2">Muhammad Hazim Iqwan Bin Azman</h3>
                    <p class="text-[#334155]">ICS23-08-001</p>
                </div>
                
                <div class="bg-[#F1F5F9] p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold text-[#0F172A] mb-2">Mohamad Ikmal Hakim Bin Amran</h3>
                    <p class="text-[#334155]">ICS23-08-021</p>
                </div>
            </div>
            
            <div class="max-w-3xl mx-auto bg-[#F1F5F9] p-8 rounded-lg shadow-md">
                <h3 class="text-2xl font-semibold text-[#0F172A] mb-4 text-center">Our Mission</h3>
                <p class="text-[#334155] text-lg">
                    We aim to create a transparent platform where students can easily view all club activities, 
                    track the merit points they can earn from each activity, and maximize their chances of 
                    securing college hostel accommodations through active participation.
                </p>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact">
        <?php include "footer.php" ?>
    </section>
</main>

<script>
// Scroll to top button functionality
document.addEventListener('DOMContentLoaded', function() {
    const scrollToTopBtn = document.getElementById('scrollToTopBtn');
    
    // Show/hide button based on scroll position
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            scrollToTopBtn.classList.remove('opacity-0', 'invisible');
            scrollToTopBtn.classList.add('opacity-100', 'visible');
        } else {
            scrollToTopBtn.classList.remove('opacity-100', 'visible');
            scrollToTopBtn.classList.add('opacity-0', 'invisible');
        }
    });
    
    // Smooth scroll to top when clicked
    scrollToTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
});
</script>

