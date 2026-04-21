<?php
// header.php - main layout header
$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e(APP_NAME); ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <?php if (is_logged_in()): ?>
    <nav class="navbar">
        <div class="nav-brand">
            <a href="<?php echo BASE_URL; ?>index.php?page=dashboard"><?php echo e(APP_NAME); ?></a>
        </div>
        <div class="nav-links">
            <a href="<?php echo BASE_URL; ?>index.php?page=dashboard" class="<?php echo $current_page === 'dashboard' ? 'active' : ''; ?>">Dashboard</a>
            <a href="<?php echo BASE_URL; ?>index.php?page=create_ticket" class="<?php echo $current_page === 'create_ticket' ? 'active' : ''; ?>">New Ticket</a>
            <?php if (is_staff()): ?>
            <a href="<?php echo BASE_URL; ?>index.php?page=manage_tickets" class="<?php echo $current_page === 'manage_tickets' ? 'active' : ''; ?>">Manage</a>
            <a href="<?php echo BASE_URL; ?>index.php?page=categories" class="<?php echo $current_page === 'categories' ? 'active' : ''; ?>">Categories</a>
            <a href="<?php echo BASE_URL; ?>index.php?page=manage_users" class="<?php echo $current_page === 'manage_users' ? 'active' : ''; ?>">Users</a>
            <?php endif; ?>
            <a href="<?php echo BASE_URL; ?>index.php?page=profile" class="<?php echo $current_page === 'profile' ? 'active' : ''; ?>">Profile</a>
            <a href="<?php echo BASE_URL; ?>index.php?page=logout" class="nav-logout">Logout</a>
        </div>
        <div class="nav-user">
            <span><?php echo e(get_session_user_name()); ?></span>
            <span class="badge role-badge"><?php echo e(ucfirst(get_session_user_role())); ?></span>
        </div>
    </nav>
    <?php endif; ?>

    <main class="container">
        <?php
        $flash = get_flash();
        if ($flash):
        ?>
        <div class="alert alert-<?php echo e($flash['type']); ?>">
            <?php echo e($flash['message']); ?>
            <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
        <?php endif; ?>
