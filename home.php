
<?php 
include "header.php";
require_once __DIR__ . '/components/breadcrumb.php';
?>

<main class="min-h-screen">

    <!-- Login/Logout Messages -->
    <?php if (isset($_GET['login']) && $_GET['login'] === 'success'): ?>
        <div class="fixed top-20 left-1/2 transform -translate-x-1/2 z-50 bg-green-100 border border-green-400 text-green-700 px-6 py-3 rounded-lg shadow-lg animate-fade-in">
            <?php if (isset($_GET['new_user']) && $_GET['new_user'] === '1'): ?>
                Welcome! Your account has been created successfully.
            <?php else: ?>
                Welcome back! You have been logged in successfully.
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php 
    require_once __DIR__ . '/components/notification.php';
    
    if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
        Notification::showLogout();
    }
    
    if (isset($_GET['login']) && $_GET['login'] === 'success') {
        Notification::showLogin();
    }
    
    if (isset($_GET['register']) && $_GET['register'] === 'success') {
        Notification::showRegistration();
    }
    
    $notification = Notification::getInstance();
    echo $notification->render();
    ?>

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

    // Auto-hide notification messages
    const notifications = document.querySelectorAll('.fixed.top-20');
    notifications.forEach(notification => {
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    });
});
</script>

