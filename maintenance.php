<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Maintenance - Club Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fade-in-up {
            animation: fadeInUp 0.8s ease-out;
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }
        
        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-2xl mx-auto text-center animate-fade-in-up">
        <!-- Maintenance Icon -->
        <div class="mb-8">
            <div class="w-32 h-32 mx-auto bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center shadow-2xl animate-pulse">
                <i class="fas fa-tools text-white text-5xl"></i>
            </div>
        </div>

        <!-- Main Content -->
        <div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-2xl p-8 border border-white/50">
            <h1 class="text-4xl font-bold text-gray-800 mb-4">
                System Maintenance
            </h1>
            
            <p class="text-xl text-gray-600 mb-6 leading-relaxed">
                We're currently performing scheduled maintenance to improve your experience. 
                Please check back soon.
            </p>

            <!-- Status Indicators -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                    <i class="fas fa-clock text-yellow-600 text-2xl mb-2"></i>
                    <p class="text-sm font-medium text-yellow-800">Estimated Time</p>
                    <p class="text-lg font-bold text-yellow-900">TBD</p>
                </div>
                
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                    <i class="fas fa-calendar text-blue-600 text-2xl mb-2"></i>
                    <p class="text-sm font-medium text-blue-800">Started At</p>
                    <p class="text-lg font-bold text-blue-900"><?= date('M d, g:i A') ?></p>
                </div>
                
                <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                    <i class="fas fa-check-circle text-green-600 text-2xl mb-2"></i>
                    <p class="text-sm font-medium text-green-800">Status</p>
                    <p class="text-lg font-bold text-green-900">In Progress</p>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="mb-8">
                <div class="flex justify-between text-sm text-gray-600 mb-2">
                    <span>Progress</span>
                    <span>In Progress</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="bg-gradient-to-r from-blue-500 to-purple-600 h-3 rounded-full transition-all duration-1000 ease-out animate-pulse"></div>
                </div>
            </div>

            <!-- What We're Doing -->
            <div class="bg-gray-50 rounded-xl p-6 mb-8 text-left">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-list-check text-blue-600 mr-2"></i>
                    What We're Working On
                </h3>
                <ul class="space-y-2 text-gray-600">
                    <li class="flex items-center">
                        <i class="fas fa-spinner text-blue-500 mr-2 animate-spin"></i>
                        System maintenance in progress
                    </li>
                </ul>
            </div>

            <!-- Contact Information -->
            <div class="border-t border-gray-200 pt-6">
                <p class="text-gray-600 mb-4">
                    Need immediate assistance? Contact our support team:
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="#" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        <i class="fas fa-envelope mr-2"></i>
                        Contact Support
                    </a>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-gray-500">
            <p class="text-sm">
                <i class="fas fa-heart text-red-500 mr-1"></i>
                Thank you for your patience. We're working hard to serve you better.
            </p>
            <p class="text-xs mt-2">
                System Maintenance
            </p>
        </div>

        <!-- Auto-refresh notice -->
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-500">
                <i class="fas fa-sync-alt mr-1"></i>
                This page will automatically refresh every 5 minutes
            </p>
        </div>
    </div>

    <script>
        // Auto-refresh functionality
        let refreshCountdown = 300; // 5 minutes in seconds
        
        function updateCountdown() {
            const minutes = Math.floor(refreshCountdown / 60);
            const seconds = refreshCountdown % 60;
            
            if (refreshCountdown <= 0) {
                location.reload();
            } else {
                refreshCountdown--;
                setTimeout(updateCountdown, 1000);
            }
        }
        
        // Start countdown
        updateCountdown();
        
        // Check system status every 30 seconds
        setInterval(() => {
            fetch('config/status_check.php')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'online') {
                        location.reload();
                    }
                })
                .catch(error => {
                    console.log('Status check failed, continuing maintenance mode');
                });
        }, 30000);
    </script>
</body>
</html>
