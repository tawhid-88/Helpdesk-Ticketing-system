<?php
/**
 * User management page (staff only)
 * Allows staff to create and delete user accounts.
 */
auth_require_staff();

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'create') {
        $name     = isset($_POST['name']) ? trim($_POST['name']) : '';
        $email    = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $role     = isset($_POST['role']) ? $_POST['role'] : 'student';

        // Validate role
        if (!in_array($role, array('student', 'faculty', 'staff'))) {
            $role = 'student';
        }

        if ($name === '' || $email === '' || $password === '') {
            set_flash('danger', 'All fields are required.');
        } elseif (strlen($password) < 6) {
            set_flash('danger', 'Password must be at least 6 characters.');
        } else {
            $result = auth_register($name, $email, $password, $role);
            if ($result['success']) {
                set_flash('success', 'User "' . $name . '" created successfully.');
            } else {
                set_flash('danger', $result['error']);
            }
        }
        header('Location: ' . BASE_URL . 'index.php?page=manage_users');
        exit;

    } elseif ($action === 'delete') {
        $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
        if ($user_id > 0) {
            if (delete_user($user_id, get_session_user_id())) {
                set_flash('success', 'User deleted successfully.');
            } else {
                set_flash('danger', 'Cannot delete this user. You cannot delete your own account.');
            }
        }
        header('Location: ' . BASE_URL . 'index.php?page=manage_users');
        exit;
    }
}

// Build filters
$filters = array(
    'role'   => isset($_GET['role']) ? $_GET['role'] : '',
    'search' => isset($_GET['search']) ? $_GET['search'] : '',
    'page'   => isset($_GET['p']) ? (int)$_GET['p'] : 1,
);

$data        = get_all_users($filters);
$users       = $data['users'];
$total_pages = $data['total_pages'];
$cur_page    = $data['page'];
$user_stats  = get_user_stats();
?>

<!-- User Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number"><?php echo isset($user_stats['total']) ? $user_stats['total'] : 0; ?></div>
        <div class="stat-label">Total Users</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?php echo isset($user_stats['student']) ? $user_stats['student'] : 0; ?></div>
        <div class="stat-label">Students</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?php echo isset($user_stats['faculty']) ? $user_stats['faculty'] : 0; ?></div>
        <div class="stat-label">Faculty</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?php echo isset($user_stats['staff']) ? $user_stats['staff'] : 0; ?></div>
        <div class="stat-label">Staff</div>
    </div>
</div>

<!-- Create User Form -->
<div class="card">
    <div class="card-header"><h2>Create New User</h2></div>

    <form method="POST" action="<?php echo BASE_URL; ?>index.php?page=manage_users" style="display:flex; gap:0.75rem; flex-wrap:wrap; align-items:flex-end;">
        <input type="hidden" name="action" value="create">
        <div class="form-group" style="flex:1; min-width:160px;">
            <label for="create-name">Full Name</label>
            <input type="text" id="create-name" name="name" class="form-control" placeholder="Full Name" required>
        </div>
        <div class="form-group" style="flex:1.5; min-width:200px;">
            <label for="create-email">Email</label>
            <input type="email" id="create-email" name="email" class="form-control" placeholder="user@northsouth.edu" required>
        </div>
        <div class="form-group" style="flex:1; min-width:140px;">
            <label for="create-password">Password</label>
            <input type="password" id="create-password" name="password" class="form-control" placeholder="Min 6 chars" required>
        </div>
        <div class="form-group" style="min-width:120px;">
            <label for="create-role">Role</label>
            <select id="create-role" name="role" class="form-control">
                <option value="student">Student</option>
                <option value="faculty">Faculty</option>
                <option value="staff">Staff</option>
            </select>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-sm">Create User</button>
        </div>
    </form>
</div>

<!-- User List -->
<div class="card">
    <div class="card-header">
        <h2>All Users <span style="font-size:0.8rem; color:#888; font-weight:400;">(<?php echo $data['total']; ?> total)</span></h2>
    </div>

    <!-- Filters -->
    <form method="GET" action="<?php echo BASE_URL; ?>index.php" class="filters-bar">
        <input type="hidden" name="page" value="manage_users">
        <div class="form-group">
            <label>Search</label>
            <input type="text" name="search" class="form-control" placeholder="Name or email..."
                   value="<?php echo e($filters['search']); ?>">
        </div>
        <div class="form-group">
            <label>Role</label>
            <select name="role" class="form-control">
                <option value="">All Roles</option>
                <option value="student" <?php echo $filters['role']==='student'?'selected':''; ?>>Student</option>
                <option value="faculty" <?php echo $filters['role']==='faculty'?'selected':''; ?>>Faculty</option>
                <option value="staff" <?php echo $filters['role']==='staff'?'selected':''; ?>>Staff</option>
            </select>
        </div>
        <div class="form-group">
            <label>&nbsp;</label>
            <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
        </div>
    </form>

    <?php if (empty($users)): ?>
        <p style="color:#888; text-align:center; padding:2rem 0;">No users found.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><?php echo $u['id']; ?></td>
                    <td><?php echo e($u['name']); ?></td>
                    <td><?php echo e($u['email']); ?></td>
                    <td>
                        <?php
                        $role_colors = array('student' => '#17a2b8', 'faculty' => '#ffc107', 'staff' => '#e94560');
                        $rc = isset($role_colors[$u['role']]) ? $role_colors[$u['role']] : '#6c757d';
                        ?>
                        <span class="badge" style="background:<?php echo $rc; ?>;"><?php echo e(ucfirst($u['role'])); ?></span>
                    </td>
                    <td><?php echo time_ago($u['created_at']); ?></td>
                    <td>
                        <?php if ((int)$u['id'] !== get_session_user_id()): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm" data-confirm="Delete user '<?php echo e($u['name']); ?>'? This will also delete all their tickets and comments.">Delete</button>
                        </form>
                        <?php else: ?>
                        <span style="color:#555; font-size:0.8rem;">You</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <?php $qs = $_GET; $qs['p'] = $i; $url = BASE_URL . 'index.php?' . http_build_query($qs); ?>
            <?php if ($i === $cur_page): ?>
                <span class="current"><?php echo $i; ?></span>
            <?php else: ?>
                <a href="<?php echo e($url); ?>"><?php echo $i; ?></a>
            <?php endif; ?>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>
