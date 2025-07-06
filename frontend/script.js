// frontend/script.js

// loadPage
function loadPage(page) {
    switch(page) {
        case 'home':
            window.location.href = 'templates/index.html';
            break;
        case 'login':
            window.location.href = 'templates/login.html';
            break;
        case 'dashboard':
            window.location.href = 'templates/dashboard.html';
            break;
        case 'clubs':
            window.location.href = 'templates/clubs.html';
            break;
        default:
            window.location.href = 'templates/home.html';
    }
}

// handleHashChange
function handleHashChange() {
    const hash = window.location.hash.substring(1);
    const page = hash || 'home';
    loadPage(page);
}

// Initialize routing when page loads
window.addEventListener('load', handleHashChange);

// Listen for hash changes
window.addEventListener('hashchange', handleHashChange);