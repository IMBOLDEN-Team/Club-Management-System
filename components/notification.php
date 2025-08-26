<?php
class Notification {
    private static $instance = null;
    private $messages = [];

    private function __construct() {
        // Private constructor for singleton pattern
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function addMessage($type, $title, $text, $icon, $bgColor, $borderColor, $textColor) {
        $this->messages[] = [
            'type' => $type,
            'title' => $title,
            'text' => $text,
            'icon' => $icon,
            'bgColor' => $bgColor,
            'borderColor' => $borderColor,
            'textColor' => $textColor
        ];
    }

    public static function showSuccess($text, $title = 'Success!') {
        $instance = self::getInstance();
        $instance->addMessage(
            'success',
            $title,
            $text,
            'fas fa-check-circle',
            'bg-green-50',
            'border-green-200',
            'text-green-800'
        );
    }

    public static function showError($text, $title = 'Error!') {
        $instance = self::getInstance();
        $instance->addMessage(
            'error',
            $title,
            $text,
            'fas fa-exclamation-circle',
            'bg-red-50',
            'border-red-200',
            'text-red-800'
        );
    }

    public static function showWarning($text, $title = 'Warning!') {
        $instance = self::getInstance();
        $instance->addMessage(
            'warning',
            $title,
            $text,
            'fas fa-exclamation-triangle',
            'bg-yellow-50',
            'border-yellow-200',
            'text-yellow-800'
        );
    }

    public static function showInfo($text, $title = 'Info') {
        $instance = self::getInstance();
        $instance->addMessage(
            'info',
            $title,
            $text,
            'fas fa-info-circle',
            'bg-blue-50',
            'border-blue-200',
            'text-blue-800'
        );
    }

    public static function showLogout($text = 'You have been logged out successfully. Thank you for using our system!', $title = 'Logged Out') {
        $instance = self::getInstance();
        $instance->addMessage(
            'logout',
            $title,
            $text,
            'fas fa-sign-out-alt',
            'bg-green-50',
            'border-green-200',
            'text-green-800'
        );
    }

    public static function showLogin($text = 'You have been logged in successfully. Welcome back!', $title = 'Logged In') {
        $instance = self::getInstance();
        $instance->addMessage(
            'login',
            $title,
            $text,
            'fas fa-sign-in-alt',
            'bg-green-50',
            'border-green-200',
            'text-green-800'
        );
    }

    public static function showRegistration($text = 'Your account has been created successfully. Welcome!', $title = 'Account Created') {
        $instance = self::getInstance();
        $instance->addMessage(
            'registration',
            $title,
            $text,
            'fas fa-user-plus',
            'bg-green-50',
            'border-green-200',
            'text-green-800'
        );
    }

    public function render() {
        if (empty($this->messages)) {
            return '';
        }

        $output = '';
        foreach ($this->messages as $msg) {
            $output .= "
            <div class=\"fixed top-20 left-1/2 transform -translate-x-1/2 z-[1000] {$msg['bgColor']} {$msg['borderColor']} {$msg['textColor']} px-6 py-3 rounded-lg shadow-lg animate-slide-down flex items-center space-x-3 border\" role=\"alert\" id=\"notification-{$msg['type']}\">
                <i class=\"{$msg['icon']} text-xl\"></i>
                <div>
                    <p class=\"font-bold\">{$msg['title']}</p>
                    <p class=\"text-sm\">{$msg['text']}</p>
                </div>
                <button type=\"button\" class=\"ml-auto -mx-1.5 -my-1.5 bg-transparent text-current rounded-lg focus:ring-2 focus:ring-current p-1.5 hover:bg-opacity-20 inline-flex h-8 w-8\" aria-label=\"Close\" onclick=\"this.closest('.fixed').remove();\">
                    <span class=\"sr-only\">Close</span>
                    <svg class=\"w-5 h-5\" fill=\"currentColor\" viewBox=\"0 0 20 20\" xmlns=\"http://www.w3.org/2000/svg\"><path fill-rule=\"evenodd\" d=\"M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z\" clip-rule=\"evenodd\"></path></svg>
                </button>
            </div>";
        }
        
        // Add JavaScript to auto-hide notifications after 1 second
        $output .= "
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const notifications = document.querySelectorAll('.fixed[role=\"alert\"]');
                notifications.forEach(function(notification) {
                    notification.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
                    notification.style.opacity = '0';
                    notification.style.transform = 'translateY(-100%) translateX(-50%)';
                    setTimeout(function() {
                        if (notification.parentNode) {
                            notification.parentNode.removeChild(notification);
                        }
                    }, 500);
                });
            }, 1000);
        });
        </script>";
        
        // Clear messages after rendering
        $this->messages = [];
        
        return $output;
    }
}
?>
