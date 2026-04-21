<?php
/**
 * Register page - North South University
 */
if (is_logged_in()) {
    header('Location: ' . BASE_URL . 'index.php?page=dashboard');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email    = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm  = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $role     = isset($_POST['role']) ? $_POST['role'] : 'student';

    // Validate role
    if (!in_array($role, array('student', 'faculty', 'staff'))) {
        $role = 'student';
    }

    if ($name === '' || $email === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $result = auth_register($name, $email, $password, $role);
        if ($result['success']) {
            set_flash('success', 'Registration successful! Please log in.');
            header('Location: ' . BASE_URL . 'index.php?page=login');
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
    <title>Register - <?php echo e(APP_NAME); ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <h1><?php echo e(APP_NAME); ?></h1>
        <p class="subtitle">Create your NSU account</p>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo e($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="<?php echo BASE_URL; ?>index.php?page=register">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" class="form-control"
                       value="<?php echo isset($name) ? e($name) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="email">NSU Email</label>
                <input type="email" id="email" name="email" class="form-control"
                       value="<?php echo isset($email) ? e($email) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <select id="role" name="role" class="form-control">
                    <option value="student" <?php echo (isset($role) && $role==='student') ? 'selected' : ''; ?>>Student</option>
                    <option value="faculty" <?php echo (isset($role) && $role==='faculty') ? 'selected' : ''; ?>>Faculty</option>
                    <option value="staff" <?php echo (isset($role) && $role==='staff') ? 'selected' : ''; ?>>Staff</option>
                </select>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Register</button>
        </form>
        <p style="text-align:center; margin-top:1rem; font-size:0.85rem; color:#888;">
            Already have an account? <a href="<?php echo BASE_URL; ?>index.php?page=login">Login here</a>
        </p>
    </div>
</div>
<script src="<?php echo BASE_URL; ?>assets/js/app.js"></script>
</body>
</html>
