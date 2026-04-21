<?php
/**
 * Categories management page (staff only)
 */
auth_require_staff();

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'add') {
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $desc = isset($_POST['description']) ? trim($_POST['description']) : '';
        if ($name !== '') {
            add_category($name, $desc);
            set_flash('success', 'Category added.');
        } else {
            set_flash('danger', 'Category name is required.');
        }
    } elseif ($action === 'delete') {
        $cat_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
        if ($cat_id > 0) {
            delete_category($cat_id);
            set_flash('success', 'Category deleted.');
        }
    }
    header('Location: ' . BASE_URL . 'index.php?page=categories');
    exit;
}

$categories = get_categories();
?>

<div class="card">
    <div class="card-header"><h2>Manage Categories</h2></div>

    <form method="POST" action="<?php echo BASE_URL; ?>index.php?page=categories" style="display:flex; gap:0.75rem; flex-wrap:wrap; align-items:flex-end; margin-bottom:1.5rem;">
        <input type="hidden" name="action" value="add">
        <div class="form-group" style="flex:1; min-width:180px;">
            <label>Name</label>
            <input type="text" name="name" class="form-control" placeholder="Category name" required>
        </div>
        <div class="form-group" style="flex:2; min-width:200px;">
            <label>Description</label>
            <input type="text" name="description" class="form-control" placeholder="Optional description">
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary btn-sm">Add Category</button>
        </div>
    </form>

    <?php if (empty($categories)): ?>
        <p style="color:#888; text-align:center;">No categories yet.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                <tr>
                    <td><?php echo $cat['id']; ?></td>
                    <td><?php echo e($cat['name']); ?></td>
                    <td><?php echo e($cat['description'] ? $cat['description'] : '-'); ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm" data-confirm="Delete this category?">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
