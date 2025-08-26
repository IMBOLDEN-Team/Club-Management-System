<?php
/**
 * Simple Breadcrumb Navigation Component
 * Provides clean and minimal breadcrumb navigation across the system
 */

class Breadcrumb {
    private $items = [];
    
    public function __construct() {
        // Always start with Home
        $this->addItem('Home', 'home.php', 'fas fa-home');
    }
    
    /**
     * Add a breadcrumb item
     * @param string $title The display text
     * @param string $url The link URL (optional for current page)
     * @param string $icon FontAwesome icon class (optional)
     */
    public function addItem($title, $url = null, $icon = null) {
        $this->items[] = [
            'title' => $title,
            'url' => $url,
            'icon' => $icon,
            'isActive' => $url === null
        ];
    }
    
    /**
     * Render the simple breadcrumb HTML
     */
    public function render() {
        if (empty($this->items)) {
            return '';
        }
        
        $html = '<nav class="flex items-center space-x-2 text-sm mb-6" aria-label="Breadcrumb">';
        
        foreach ($this->items as $index => $item) {
            // Add separator (except for first item)
            if ($index > 0) {
                $html .= '<span class="text-gray-400">></span>';
            }
            
            if ($item['isActive']) {
                // Current/active page (no link)
                $html .= '<span class="flex items-center space-x-1 text-orange-600 font-semibold">';
                if ($item['icon']) {
                    $html .= '<i class="' . htmlspecialchars($item['icon']) . '"></i>';
                }
                $html .= '<span>' . htmlspecialchars($item['title']) . '</span>';
                $html .= '</span>';
            } else {
                // Clickable link
                $html .= '<a href="' . htmlspecialchars($item['url']) . '" class="flex items-center space-x-1 text-blue-600 hover:text-blue-800 transition-colors duration-200">';
                if ($item['icon']) {
                    $html .= '<i class="' . htmlspecialchars($item['icon']) . '"></i>';
                }
                $html .= '<span>' . htmlspecialchars($item['title']) . '</span>';
                $html .= '</a>';
            }
        }
        
        $html .= '</nav>';
        
        return $html;
    }
    
    /**
     * Quick setup for common pages
     */
    public static function forClubberDashboard($clubName) {
        $breadcrumb = new self();
        $breadcrumb->addItem('Club Management', '#', 'fas fa-users');
        $breadcrumb->addItem($clubName, null, 'fas fa-tachometer-alt');
        return $breadcrumb;
    }
    
    public static function forAdminDashboard() {
        $breadcrumb = new self();
        $breadcrumb->addItem('Dashboard', null, 'fas fa-tachometer-alt');
        return $breadcrumb;
    }
    
    public static function forAdminSection($sectionName, $sectionIcon = 'fas fa-cog') {
        $breadcrumb = new self();
        $breadcrumb->addItem('Dashboard', '#dashboard', 'fas fa-tachometer-alt');
        $breadcrumb->addItem($sectionName, null, $sectionIcon);
        return $breadcrumb;
    }
    
    public static function forStudentDashboard() {
        $breadcrumb = new self();
        $breadcrumb->addItem('Student Portal', '#', 'fas fa-user-graduate');
        $breadcrumb->addItem('Dashboard', null, 'fas fa-tachometer-alt');
        return $breadcrumb;
    }
    
    public static function forActivities() {
        $breadcrumb = new self();
        $breadcrumb->addItem('Club Management', '#', 'fas fa-users');
        $breadcrumb->addItem('Activities', null, 'fas fa-calendar-alt');
        return $breadcrumb;
    }
    
    public static function forMembers() {
        $breadcrumb = new self();
        $breadcrumb->addItem('Club Management', '#', 'fas fa-users');
        $breadcrumb->addItem('Members', null, 'fas fa-users');
        return $breadcrumb;
    }
    
    public static function forHierarchy() {
        $breadcrumb = new self();
        $breadcrumb->addItem('Club Management', '#', 'fas fa-users');
        $breadcrumb->addItem('Hierarchy', null, 'fas fa-sitemap');
        return $breadcrumb;
    }
    
    public static function forClubDirectory() {
        $breadcrumb = new self();
        $breadcrumb->addItem('Club Directory', null, 'fas fa-users');
        return $breadcrumb;
    }
    
    public static function forProfile() {
        $breadcrumb = new self();
        $breadcrumb->addItem('Profile', null, 'fas fa-user');
        return $breadcrumb;
    }
    
    public static function forSettings() {
        $breadcrumb = new self();
        $breadcrumb->addItem('Settings', null, 'fas fa-cog');
        return $breadcrumb;
    }
    
    public static function forNotificationListing() {
        $breadcrumb = new self();
        $breadcrumb->addItem('Notification Listing', null, 'fas fa-bell');
        return $breadcrumb;
    }
}
?>
