<?php
/**
 * index.php - Entry point / router
 */

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

session_init();

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Public pages (no login required)
$public_pages = array('login', 'register');

// If not logged in and page is not public, redirect to login
if (!is_logged_in() && !in_array($page, $public_pages)) {
    $page = 'login';
}

// Route to the correct page
$allowed_pages = array(
    'login', 'register', 'logout', 'dashboard',
    'create_ticket', 'view_ticket', 'manage_tickets',
    'profile', 'categories', 'manage_users'
);

if (!in_array($page, $allowed_pages)) {
    $page = 'dashboard';
}

// Handle logout
if ($page === 'logout') {
    session_destroy_all();
    session_init();
    set_flash('success', 'You have been logged out.');
    header('Location: ' . BASE_URL . 'index.php?page=login');
    exit;
}

// Include layout for auth pages (no navbar)
if (in_array($page, $public_pages)) {
    require_once __DIR__ . '/pages/' . $page . '.php';
} else {
    require_once __DIR__ . '/includes/header.php';
    require_once __DIR__ . '/pages/' . $page . '.php';
    require_once __DIR__ . '/includes/footer.php';
}
