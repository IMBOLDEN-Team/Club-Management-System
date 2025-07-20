
<?php include "header.php" ?>

<main class="min-h-screen">

    <!-- Scroll to top button -->
    <button id="scrollToTopBtn" class="fixed bottom-6 right-6 bg-[#0F172A] text-white p-3 rounded-full shadow-lg opacity-0 invisible transition-all duration-300 hover:bg-[#334155] z-50">
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

    <section class="min-h-screen bg-gradient-to-br from-[#F1F5F9] via-[#E2E8F0] to-[#CBD5E1] py-16 px-4 sm:px-6 lg:px-8 flex items-center relative overflow-hidden z-0">
    <!-- Animated background elements with icons -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <!-- Floating background blobs -->
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-gradient-to-r from-blue-200/20 to-purple-200/20 rounded-full blur-3xl animate-float"></div>
        <div class="absolute -bottom-32 -right-32 w-80 h-80 bg-gradient-to-r from-orange-200/20 to-pink-200/20 rounded-full blur-3xl animate-float-delayed"></div>
        <div class="absolute top-1/2 left-1/4 w-64 h-64 bg-gradient-to-r from-green-200/15 to-blue-200/15 rounded-full blur-2xl animate-float-slow"></div>
        
        <!-- Animated icons -->
        <div class="absolute top-20 left-10 text-4xl opacity-20 animate-bounce-icon">🎯</div>
        <div class="absolute top-40 right-20 text-3xl opacity-25 animate-float-icon" style="animation-delay: 1s">🏆</div>
        <div class="absolute bottom-40 left-20 text-5xl opacity-15 animate-spin-slow">⚡</div>
        <div class="absolute bottom-20 right-40 text-3xl opacity-20 animate-pulse-icon" style="animation-delay: 2s">🚀</div>
        <div class="absolute top-1/3 left-1/2 text-4xl opacity-10 animate-float-icon" style="animation-delay: 3s">💡</div>
        <div class="absolute top-2/3 right-1/4 text-3xl opacity-25 animate-bounce-icon" style="animation-delay: 1.5s">🎨</div>
        <div class="absolute top-1/4 right-1/3 text-2xl opacity-20 animate-float-icon" style="animation-delay: 4s">📚</div>
        <div class="absolute bottom-1/3 left-1/3 text-4xl opacity-15 animate-pulse-icon" style="animation-delay: 2.5s">🌟</div>
    </div>
    
    <div class="max-w-7xl mx-auto relative">
        <!-- Enhanced header -->
        <div class="text-center mb-16">
            <h2 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-[#0F172A] to-[#334155] bg-clip-text text-transparent mb-4 animate-fade-in">
                Latest Activities
            </h2>
            <div class="w-24 h-1 bg-gradient-to-r from-[#F59E0B] to-[#EF4444] mx-auto rounded-full"></div>
            <p class="text-[#64748B] mt-4 text-lg">Discover what's happening in our community</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <?php include "index.php" ?>
            <?php
            $hasActivities = false;
            
            if (isset($connect)) {
                $query = "SELECT ca.name, ca.start, ca.end, ca.merit_point, c.name AS club_name 
                        FROM CLUB_ACTIVITY ca 
                        JOIN CLUB c ON ca.club_id = c.id 
                        WHERE ca.end >= NOW()
                        ORDER BY ca.start ASC 
                        LIMIT 3";
                $result = mysqli_query($connect, $query);
                
                if ($result && mysqli_num_rows($result) > 0) {
                    $hasActivities = true;
                    $cardIndex = 0;
                    while ($row = mysqli_fetch_assoc($result)) {
                        $start_date = date('M j, Y', strtotime($row['start']));
                        $end_date = date('M j, Y', strtotime($row['end']));
                        $cardIndex++;
                        ?>
                        <div class="group bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 border border-white/20 hover:border-[#F59E0B]/30 animate-slide-up relative overflow-hidden" style="animation-delay: <?= $cardIndex * 0.2 ?>s">
                            <!-- Card gradient overlay -->
                            <div class="absolute inset-0 bg-gradient-to-br from-transparent to-[#F59E0B]/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                            
                            <div class="relative z-10">
                                <!-- Header with fixed merit badge -->
                                <div class="flex justify-between items-start mb-6">
                                    <h3 class="text-xl font-bold text-[#0F172A] group-hover:text-[#F59E0B] transition-colors duration-300 flex-1 pr-4">
                                        <?= htmlspecialchars($row['name']) ?>
                                    </h3>
                                    <div class="flex-shrink-0">
                                        <span class="inline-flex items-center bg-gradient-to-r from-[#F59E0B] to-[#EF4444] text-white px-3 py-1.5 rounded-full text-sm font-bold shadow-lg hover:shadow-xl transition-all duration-300 whitespace-nowrap">
                                            <?= htmlspecialchars($row['merit_point']) ?> pts
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Club info with icon -->
                                <div class="flex items-center mb-4 text-[#64748B]">
                                    <div class="w-2 h-2 bg-[#F59E0B] rounded-full mr-3 animate-pulse flex-shrink-0"></div>
                                    <p class="font-medium">by <?= htmlspecialchars($row['club_name']) ?></p>
                                </div>
                                
                                <!-- Date with enhanced styling -->
                                <div class="bg-[#F8FAFC] p-3 rounded-lg mb-6 border-l-4 border-[#F59E0B]">
                                    <div class="flex items-center text-[#475569] font-medium">
                                        <span class="mr-2">📅</span>
                                        <span class="text-sm"><?= $start_date ?> - <?= $end_date ?></span>
                                    </div>
                                </div>
                                
                                <!-- Enhanced CTA button -->
                                <button class="group/btn w-full bg-gradient-to-r from-[#0F172A] to-[#334155] text-white px-6 py-3 rounded-xl font-semibold hover:from-[#F59E0B] hover:to-[#EF4444] transition-all duration-300 transform hover:scale-105 active:scale-95 shadow-lg hover:shadow-xl relative overflow-hidden">
                                    <span class="relative z-10">View Details</span>
                                    <div class="absolute inset-0 bg-white/20 transform scale-x-0 group-hover/btn:scale-x-100 transition-transform duration-500 origin-left"></div>
                                </button>
                            </div>
                        </div>
                        <?php
                    }
                }
            }
            
            if (!$hasActivities): ?>
                <div class="col-span-3 text-center py-16">
                    <div class="max-w-md mx-auto">
                        <!-- Empty state with animation -->
                        <div class="relative mb-8">
                            <div class="w-32 h-32 mx-auto bg-gradient-to-br from-[#F59E0B]/20 to-[#EF4444]/20 rounded-full flex items-center justify-center animate-bounce-slow">
                                <div class="text-6xl">📅</div>
                            </div>
                            <div class="absolute inset-0 bg-gradient-to-br from-[#F59E0B]/10 to-[#EF4444]/10 rounded-full blur-xl animate-pulse"></div>
                        </div>
                        
                        <h3 class="text-3xl font-bold bg-gradient-to-r from-[#0F172A] to-[#64748B] bg-clip-text text-transparent mb-4">
                            No Activities Yet
                        </h3>
                        <p class="text-xl text-[#64748B] mb-6">
                            Something exciting is brewing! Stay tuned for upcoming events.
                        </p>
                        
                        
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<style>
@keyframes fade-in {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes slide-up {
    from { opacity: 0; transform: translateY(40px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes bounce-slow {
    0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-10px); }
    60% { transform: translateY(-5px); }
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

@keyframes float-slow {
    0%, 100% { transform: translateY(0px) translateX(0px); }
    50% { transform: translateY(-30px) translateX(20px); }
}

.animate-fade-in {
    animation: fade-in 1s ease-out;
}

.animate-slide-up {
    animation: slide-up 0.8s ease-out forwards;
    opacity: 0;
}

.animate-bounce-slow {
    animation: bounce-slow 3s ease-in-out infinite;
}

.animate-float {
    animation: float 6s ease-in-out infinite;
}

.animate-float-delayed {
    animation: float-delayed 8s ease-in-out infinite;
}

.animate-float-slow {
    animation: float-slow 10s ease-in-out infinite;
}

@keyframes bounce-icon {
    0%, 20%, 50%, 80%, 100% { transform: translateY(0) scale(1); }
    40% { transform: translateY(-15px) scale(1.1); }
    60% { transform: translateY(-5px) scale(1.05); }
}

@keyframes float-icon {
    0%, 100% { transform: translateY(0px) rotate(0deg) scale(1); }
    33% { transform: translateY(-20px) rotate(5deg) scale(1.1); }
    66% { transform: translateY(10px) rotate(-3deg) scale(0.95); }
}

@keyframes pulse-icon {
    0%, 100% { transform: scale(1); opacity: 0.2; }
    50% { transform: scale(1.2); opacity: 0.4; }
}

@keyframes spin-slow {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.animate-bounce-icon {
    animation: bounce-icon 4s ease-in-out infinite;
}

.animate-float-icon {
    animation: float-icon 6s ease-in-out infinite;
}

.animate-pulse-icon {
    animation: pulse-icon 3s ease-in-out infinite;
}

.animate-spin-slow {
    animation: spin-slow 20s linear infinite;
}
</style>

    <!-- About Us Section -->
<section id="about" class="min-h-screen bg-gradient-to-br from-white via-[#F8FAFC] to-[#F1F5F9] py-16 px-4 sm:px-6 lg:px-8 flex items-center relative overflow-hidden">
    <!-- Animated background elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-10 w-64 h-64 bg-gradient-to-r from-blue-100/30 to-purple-100/30 rounded-full blur-3xl animate-float"></div>
        <div class="absolute bottom-20 right-10 w-80 h-80 bg-gradient-to-r from-orange-100/30 to-pink-100/30 rounded-full blur-3xl animate-float-delayed"></div>
        
        <!-- Animated icons -->
        <div class="absolute top-32 right-20 text-3xl opacity-10 animate-float-icon">🎓</div>
        <div class="absolute bottom-40 left-20 text-4xl opacity-15 animate-pulse-icon">👥</div>
        <div class="absolute top-1/2 left-10 text-3xl opacity-10 animate-bounce-icon">💼</div>
        <div class="absolute top-1/3 right-1/4 text-2xl opacity-15 animate-float-icon" style="animation-delay: 2s">🌟</div>
    </div>
    
    <div class="max-w-7xl mx-auto relative z-10">
        <!-- Enhanced header -->
        <div class="text-center mb-16">
            <h2 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-[#0F172A] to-[#334155] bg-clip-text text-transparent mb-4 animate-fade-in">
                Our Story
            </h2>
            <div class="w-24 h-1 bg-gradient-to-r from-[#F59E0B] to-[#EF4444] mx-auto rounded-full mb-4"></div>
            <p class="text-[#64748B] text-lg max-w-2xl mx-auto">Meet the passionate developers behind this innovative club management system</p>
        </div>
        
        <!-- Developer Cards with enhanced styling -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-16">
            <div class="group bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-3 border border-white/50 hover:border-[#F59E0B]/30 animate-slide-up relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-[#F59E0B]/5 to-[#EF4444]/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-2xl"></div>
                <div class="relative z-10">
                    <div class="w-16 h-16 bg-gradient-to-br from-[#F59E0B] to-[#EF4444] rounded-full flex items-center justify-center mb-4 mx-auto group-hover:scale-110 transition-transform duration-300">
                        <span class="text-white font-bold text-xl">AA</span>
                    </div>
                    <h3 class="text-lg font-bold text-[#0F172A] mb-2 text-center group-hover:text-[#F59E0B] transition-colors duration-300">
                        Asmawi Aiman Mohd Sani
                    </h3>
                    <p class="text-[#64748B] text-center font-medium">ICS23-08-017</p>
                    <div class="mt-4 text-center">
                        <span class="inline-block bg-[#F1F5F9] text-[#334155] px-3 py-1 rounded-full text-sm font-medium">Developer</span>
                    </div>
                </div>
            </div>
            
            <div class="group bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-3 border border-white/50 hover:border-[#F59E0B]/30 animate-slide-up relative overflow-hidden" style="animation-delay: 0.2s">
                <div class="absolute inset-0 bg-gradient-to-br from-[#F59E0B]/5 to-[#EF4444]/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-2xl"></div>
                <div class="relative z-10">
                    <div class="w-16 h-16 bg-gradient-to-br from-[#10B981] to-[#059669] rounded-full flex items-center justify-center mb-4 mx-auto group-hover:scale-110 transition-transform duration-300">
                        <span class="text-white font-bold text-xl">MF</span>
                    </div>
                    <h3 class="text-lg font-bold text-[#0F172A] mb-2 text-center group-hover:text-[#F59E0B] transition-colors duration-300">
                        Muhammad Farid Bin Abu Samah
                    </h3>
                    <p class="text-[#64748B] text-center font-medium">ICS23-08-032</p>
                    <div class="mt-4 text-center">
                        <span class="inline-block bg-[#F1F5F9] text-[#334155] px-3 py-1 rounded-full text-sm font-medium">Developer</span>
                    </div>
                </div>
            </div>
            
            <div class="group bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-3 border border-white/50 hover:border-[#F59E0B]/30 animate-slide-up relative overflow-hidden" style="animation-delay: 0.4s">
                <div class="absolute inset-0 bg-gradient-to-br from-[#F59E0B]/5 to-[#EF4444]/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-2xl"></div>
                <div class="relative z-10">
                    <div class="w-16 h-16 bg-gradient-to-br from-[#3B82F6] to-[#1E40AF] rounded-full flex items-center justify-center mb-4 mx-auto group-hover:scale-110 transition-transform duration-300">
                        <span class="text-white font-bold text-xl">MH</span>
                    </div>
                    <h3 class="text-lg font-bold text-[#0F172A] mb-2 text-center group-hover:text-[#F59E0B] transition-colors duration-300">
                        Muhammad Hazim Iqwan Bin Azman
                    </h3>
                    <p class="text-[#64748B] text-center font-medium">ICS23-08-001</p>
                    <div class="mt-4 text-center">
                        <span class="inline-block bg-[#F1F5F9] text-[#334155] px-3 py-1 rounded-full text-sm font-medium">Developer</span>
                    </div>
                </div>
            </div>
            
            <div class="group bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-3 border border-white/50 hover:border-[#F59E0B]/30 animate-slide-up relative overflow-hidden" style="animation-delay: 0.6s">
                <div class="absolute inset-0 bg-gradient-to-br from-[#F59E0B]/5 to-[#EF4444]/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-2xl"></div>
                <div class="relative z-10">
                    <div class="w-16 h-16 bg-gradient-to-br from-[#8B5CF6] to-[#7C3AED] rounded-full flex items-center justify-center mb-4 mx-auto group-hover:scale-110 transition-transform duration-300">
                        <span class="text-white font-bold text-xl">MI</span>
                    </div>
                    <h3 class="text-lg font-bold text-[#0F172A] mb-2 text-center group-hover:text-[#F59E0B] transition-colors duration-300">
                        Mohamad Ikmal Hakim Bin Amran
                    </h3>
                    <p class="text-[#64748B] text-center font-medium">ICS23-08-021</p>
                    <div class="mt-4 text-center">
                        <span class="inline-block bg-[#F1F5F9] text-[#334155] px-3 py-1 rounded-full text-sm font-medium">Developer</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Enhanced Mission Section -->
        <div class="max-w-4xl mx-auto">
            <div class="bg-white/90 backdrop-blur-sm p-8 md:p-12 rounded-3xl shadow-2xl border border-white/50 relative overflow-hidden group">
                <!-- Background pattern -->
                <div class="absolute inset-0 bg-gradient-to-br from-[#F59E0B]/5 via-transparent to-[#EF4444]/5 opacity-50"></div>
                <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-bl from-[#F59E0B]/10 to-transparent rounded-full blur-3xl"></div>
                
                <div class="relative z-10">
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-[#F59E0B] to-[#EF4444] rounded-full mb-6">
                            <span class="text-2xl">🎯</span>
                        </div>
                        <h3 class="text-3xl font-bold bg-gradient-to-r from-[#0F172A] to-[#334155] bg-clip-text text-transparent mb-4">
                            Our Mission
                        </h3>
                        <div class="w-16 h-1 bg-gradient-to-r from-[#F59E0B] to-[#EF4444] mx-auto rounded-full"></div>
                    </div>
                    
                    <p class="text-[#475569] text-lg md:text-xl leading-relaxed text-center mb-8">
                        We aim to create a transparent platform where students can easily view all club activities, 
                        track the merit points they can earn from each activity, and maximize their chances of 
                        securing college hostel accommodations through active participation.
                    </p>
                    
                    <!-- Key features -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                        <div class="text-center p-4 rounded-xl bg-gradient-to-br from-[#F1F5F9] to-white border border-[#E2E8F0] hover:border-[#F59E0B]/30 transition-all duration-300 group/feature">
                            <div class="text-3xl mb-3 group-hover/feature:scale-110 transition-transform duration-300">📊</div>
                            <h4 class="font-semibold text-[#0F172A] mb-2">Transparent Tracking</h4>
                            <p class="text-sm text-[#64748B]">Monitor your merit points in real-time</p>
                        </div>
                        
                        <div class="text-center p-4 rounded-xl bg-gradient-to-br from-[#F1F5F9] to-white border border-[#E2E8F0] hover:border-[#F59E0B]/30 transition-all duration-300 group/feature">
                            <div class="text-3xl mb-3 group-hover/feature:scale-110 transition-transform duration-300">🎯</div>
                            <h4 class="font-semibold text-[#0F172A] mb-2">Easy Discovery</h4>
                            <p class="text-sm text-[#64748B]">Find activities that match your interests</p>
                        </div>
                        
                        <div class="text-center p-4 rounded-xl bg-gradient-to-br from-[#F1F5F9] to-white border border-[#E2E8F0] hover:border-[#F59E0B]/30 transition-all duration-300 group/feature">
                            <div class="text-3xl mb-3 group-hover/feature:scale-110 transition-transform duration-300">🏠</div>
                            <h4 class="font-semibold text-[#0F172A] mb-2">Hostel Priority</h4>
                            <p class="text-sm text-[#64748B]">Maximize your accommodation chances</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

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
    animation: fade-in 1s ease-out;
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
</style>

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

