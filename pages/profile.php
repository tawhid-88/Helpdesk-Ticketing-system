<?php
/**
 * Profile page
 */
auth_require_login();

$user = get_user(get_session_user_id());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'update_profile') {
        $name  = isset($_POST['name']) ? trim($_POST['name']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';

        if ($name === '' || $email === '') {
            $error = 'Name and email are required.';
        } else {
            update_user_profile($user['id'], $name, $email);
            $_SESSION['user_name']  = $name;
            $_SESSION['user_email'] = $email;
            set_flash('success', 'Profile updated.');
            header('Location: ' . BASE_URL . 'index.php?page=profile');
            exit;
        }
    } elseif ($action === 'change_password') {
        $current  = isset($_POST['current_password']) ? $_POST['current_password'] : '';
        $new_pass = isset($_POST['new_password']) ? $_POST['new_password'] : '';
        $confirm  = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

        // Verify current password
        $pdo = db_connect();
        $stmt = $pdo->prepare('SELECT password_md5 FROM users WHERE id = ?');
        $stmt->execute(array($user['id']));
        $row = $stmt->fetch();

        if (md5($current) !== $row['password_md5']) {
            $pw_error = 'Current password is incorrect.';
        } elseif (strlen($new_pass) < 6) {
            $pw_error = 'New password must be at least 6 characters.';
        } elseif ($new_pass !== $confirm) {
            $pw_error = 'New passwords do not match.';
        } else {
            update_user_password($user['id'], $new_pass);
            set_flash('success', 'Password changed.');
            header('Location: ' . BASE_URL . 'index.php?page=profile');
            exit;
        }
    }

    // Reload user data
    $user = get_user(get_session_user_id());
}
?>

<div class="card">
    <div class="card-header"><h2>My Profile</h2></div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo e($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="<?php echo BASE_URL; ?>index.php?page=profile">
        <input type="hidden" name="action" value="update_profile">
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" class="form-control"
                   value="<?php echo e($user['name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control"
                   value="<?php echo e($user['email']); ?>" required>
        </div>
        <div class="form-group">
            <label>Role</label>
            <input type="text" class="form-control" value="<?php echo e(ucfirst($user['role'])); ?>" disabled>
        </div>
        <div class="form-group">
            <label>Member Since</label>
            <input type="text" class="form-control" value="<?php echo e($user['created_at']); ?>" disabled>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
</div>

<div class="card">
    <div class="card-header"><h2>Change Password</h2></div>

    <?php if (isset($pw_error)): ?>
        <div class="alert alert-danger"><?php echo e($pw_error); ?></div>
    <?php endif; ?>

    <form method="POST" action="<?php echo BASE_URL; ?>index.php?page=profile">
        <input type="hidden" name="action" value="change_password">
        <div class="form-group">
            <label for="current_password">Current Password</label>
            <input type="password" id="current_password" name="current_password" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Change Password</button>
    </form>
</div>
