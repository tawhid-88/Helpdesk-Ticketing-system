<?php
/**
 * Login page
 */
if (is_logged_in()) {
    header('Location: ' . BASE_URL . 'index.php?page=dashboard');
    exit;
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($email === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } else {
        $result = auth_login($email, $password);
        if ($result['success']) {
            header('Location: ' . BASE_URL . 'index.php?page=dashboard');
            exit;
        } else {
            $error = $result['error'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo e(APP_NAME); ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <h1><?php echo e(APP_NAME); ?></h1>
        <p class="subtitle">Sign in to your NSU account</p>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo e($error); ?></div>
        <?php endif; ?>

        <?php $flash = get_flash(); if ($flash): ?>
            <div class="alert alert-<?php echo e($flash['type']); ?>"><?php echo e($flash['message']); ?></div>
        <?php endif; ?>

        <form method="POST" action="<?php echo BASE_URL; ?>index.php?page=login">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control"
                       value="<?php echo isset($email) ? e($email) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
        <p style="text-align:center; margin-top:1rem; font-size:0.85rem; color:#888;">
            Don't have an account? <a href="<?php echo BASE_URL; ?>index.php?page=register">Register here</a>
        </p>
    </div>
</div>
<script src="<?php echo BASE_URL; ?>assets/js/app.js"></script>
</body>
</html>
