<?php
session_start();

// Store logout message in session before destroying
$_SESSION['logout_message'] = 'You have been logged out successfully. Thank you for using our system!';

// Destroy all session data
session_destroy();

// Redirect to home page
header('Location: home.php?logout=success');
exit;
?>
