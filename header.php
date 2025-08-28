

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="shortcut icon" href="img/title.png" type="image/png">
    <style>
        .hover-accent:hover {
            color: #F59E0B;
            transition: color 0.3s ease;
        }
        
        /* Dropdown animations */
        .profile-dropdown {
            transform-origin: top right;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .profile-dropdown.hidden {
            opacity: 0;
            visibility: hidden;
            transform: scale(0.95) translateY(-10px);
        }
        
        .profile-dropdown.visible {
            opacity: 1;
            visibility: visible;
            transform: scale(1) translateY(0);
        }
        
        /* Ensure dropdown appears above other content */
        .profile-dropdown {
            z-index: 9999;
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
    
</head>
<body class="bg-gray-50 font-sans">
    <?php 
    // Include notification system
    if (file_exists(__DIR__ . '/components/notification.php')) {
        require_once __DIR__ . '/components/notification.php';
        
        // Check for logout messages
        if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
            Notification::showLogout();
        }
        
        // Check for login messages
        if (isset($_GET['login']) && $_GET['login'] === 'success') {
            Notification::showLogin();
        }
        
        // Check for registration messages
        if (isset($_GET['register']) && $_GET['register'] === 'success') {
            Notification::showRegistration();
        }
        
        // Render notifications
        $notification = Notification::getInstance();
        echo $notification->render();
    }
    ?>
    
    <header class="bg-[#0F172A] text-white shadow-md sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <!-- Logo on the right side -->
                <div class="flex-shrink-0">
                    <a href="home.php">
                        <img src="img/logo.png" 
                             alt="KPM Logo" 
                             class="h-16 w-16 object-contain md:h-20 md:w-20">
                    </a>
                </div>
                
                <!-- Navigation links on the left side -->
                <nav class="hidden md:flex space-x-1 lg:space-x-4">
                    <a href="club_list.php" class="px-3 py-2 rounded-md text-sm font-medium hover-accent">Club</a>
                    <a href="#" class="px-3 py-2 rounded-md text-sm font-medium hover-accent">Activity</a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Profile Dropdown -->
                        <div class="relative group">
                            <button class="flex items-center space-x-2 px-3 py-2 rounded-md text-sm font-medium text-[#F1F5F9] hover:text-[#F59E0B] transition-colors duration-300 focus:outline-none">
                                <div class="w-8 h-8 rounded-full overflow-hidden border-2 border-white/20">
                                    <img src="img/default-profile.jpg" 
                                         alt="Profile Picture" 
                                         class="w-full h-full object-cover"
                                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIHZpZXdCb3g9IjAgMCAzMiAzMiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjMyIiBoZWlnaHQ9IjMyIiBmaWxsPSIjNjc3NDhCIi8+CjxjaXJjbGUgY3g9IjE2IiBjeT0iMTIiIHI9IjQiIGZpbGw9IiNGRkYiLz4KPHBhdGggZD0iTTggMjhDOCAyMy41ODE3IDEyLjU4MTcgMTkgMTcgMTlIMTVDMTkuNDE4MyAxOSAyNCAyMy41ODE3IDI0IDI4IiBzdHJva2U9IiNGRkYiIHN0cm9rZS13aWR0aD0iMiIvPgo8L3N2Zz4K'">
                                </div>
                                <span><?= htmlspecialchars($_SESSION['username']) ?></span>
                                <svg class="w-4 h-4 transition-transform duration-200 group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-2xl border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 transform origin-top scale-95 group-hover:scale-100 z-50">
                                <div class="py-2">
                                    <!-- User Info -->
                                    <div class="px-4 py-3 border-b border-gray-100">
                                        <p class="text-sm font-medium text-[#0F172A]"><?= htmlspecialchars($_SESSION['username']) ?></p>
                                        <p class="text-xs text-[#64748B] capitalize"><?= $_SESSION['user_type'] ?></p>
                                        <?php if (isset($_SESSION['email'])): ?>
                                            <p class="text-xs text-[#64748B]"><?= htmlspecialchars($_SESSION['email']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Menu Items -->
                                    <a href="student_profile.php" class="flex items-center px-4 py-2 text-sm text-[#64748B] hover:bg-[#F1F5F9] hover:text-[#0F172A] transition-colors duration-200">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        Profile
                                    </a>
                                    
                                    <a href="settings.php" class="flex items-center px-4 py-2 text-sm text-[#64748B] hover:bg-[#F1F5F9] hover:text-[#0F172A] transition-colors duration-200">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        Settings
                                    </a>
                                    
                                    <!-- Divider -->
                                    <div class="border-t border-gray-100 my-1"></div>
                                    
                                    <!-- Logout -->
                                    <a href="logout.php" class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-200">
                                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                        Logout
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="px-3 py-2 rounded-md text-sm font-medium bg-[#F59E0B] text-[#0F172A] hover:bg-amber-400 rounded-md transition-colors duration-300">Sign in</a>
                    <?php endif; ?>
                </nav>
                
                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button type="button" class="text-white hover:text-[#F59E0B] focus:outline-none" id="mobile-menu-button">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Mobile menu (hidden by default) -->
            <div class="md:hidden hidden" id="mobile-menu">
                <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 flex flex-col">
                    <a href="#" class="px-3 py-2 rounded-md text-base font-medium hover-accent">Club</a>
                    <a href="#" class="px-3 py-2 rounded-md text-base font-medium hover-accent">Activity</a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Mobile User Info -->
                        <div class="px-3 py-3 border-t border-gray-700">
                            <div class="flex items-center space-x-3 mb-3">
                                <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-white/20">
                                    <img src="img/default-profile.jpg" 
                                         alt="Profile Picture" 
                                         class="w-full h-full object-cover"
                                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIHZpZXdCb3g9IjAgMCAzMiAzMiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjMyIiBoZWlnaHQ9IjMyIiBmaWxsPSIjNjc3NDhCIi8+CjxjaXJjbGUgY3g9IjE2IiBjeT0iMTIiIHI9IjQiIGZpbGw9IiNGRkYiLz4KPHBhdGggZD0iTTggMjhDOCAyMy41ODE3IDEyLjU4MTcgMTkgMTcgMTlIMTVDMTkuNDE4MyAxOSAyNCAyMy41ODE3IDI0IDI4IiBzdHJva2U9IiNGRkYiIHN0cm9rZS13aWR0aD0iMiIvPgo8L3N2Zz4K'">
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-[#F1F5F9]"><?= htmlspecialchars($_SESSION['username']) ?></p>
                                    <p class="text-xs text-[#CBD5E1] capitalize"><?= $_SESSION['user_type'] ?></p>
                                </div>
                            </div>
                            
                            <!-- Mobile Menu Items -->
                            <a href="student_profile.php" class="flex items-center px-3 py-2 text-base font-medium text-[#CBD5E1] hover:text-[#F59E0B] transition-colors duration-200">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Profile
                            </a>
                            
                            <a href="settings.php" class="flex items-center px-3 py-2 text-base font-medium text-[#CBD5E1] hover:text-[#F59E0B] transition-colors duration-200">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Settings
                            </a>
                            
                            <a href="logout.php" class="flex items-center px-3 py-2 text-base font-medium text-red-400 hover:text-red-300 transition-colors duration-200">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                Logout
                            </a>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="px-3 py-2 rounded-md text-base font-medium bg-[#F59E0B] text-[#0F172A] hover:bg-amber-400 rounded-md text-center transition-colors duration-300">Sign in</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <script>
        // Mobile menu toggle functionality
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const mobileMenu = document.getElementById('mobile-menu');
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            
            if (!mobileMenu.contains(event.target) && !mobileMenuButton.contains(event.target)) {
                mobileMenu.classList.add('hidden');
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Smooth scrolling for all anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const targetId = this.getAttribute('href');
                    if (targetId === '#') return;
                    
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        targetElement.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                        
                        // Update URL without jumping
                        if (history.pushState) {
                            history.pushState(null, null, targetId);
                        } else {
                            window.location.hash = targetId;
                        }
                    }
                });
            });
            
            // Close dropdown when clicking outside (for mobile)
            document.addEventListener('click', function(event) {
                const profileDropdowns = document.querySelectorAll('.group');
                profileDropdowns.forEach(dropdown => {
                    if (!dropdown.contains(event.target)) {
                        // This will close the dropdown on mobile devices
                        // The hover effect on desktop will still work
                    }
                });
            });
        });
    </script>