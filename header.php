<?php
// header.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/x-icon" href="img/title.png" sizes="32x32 48x48 64x64">
    <style>
        .hover-accent:hover {
            color: #F59E0B;
            transition: color 0.3s ease;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">
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
                    <a href="#" class="px-3 py-2 rounded-md text-sm font-medium hover-accent">Club</a>
                    <a href="#" class="px-3 py-2 rounded-md text-sm font-medium hover-accent">Activity</a>
                    <a href="#about" class="px-3 py-2 rounded-md text-sm font-medium hover-accent">About Us</a>
                    <a href="#contact" class="px-3 py-2 rounded-md text-sm font-medium hover-accent">Contact Us</a>
                    <a href="#" class="px-3 py-2 rounded-md text-sm font-medium bg-[#F59E0B] text-[#0F172A] hover:bg-amber-400 rounded-md transition-colors duration-300">Sign in</a>
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
                    <a href="#about" class="px-3 py-2 rounded-md text-sm font-medium hover-accent">About Us</a>
                    <a href="#contact" class="px-3 py-2 rounded-md text-sm font-medium hover-accent">Contact Us</a>
                    <a href="#" class="px-3 py-2 rounded-md text-base font-medium bg-[#F59E0B] text-[#0F172A] hover:bg-amber-400 rounded-md text-center transition-colors duration-300">Sign in</a>
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
        });
    </script>