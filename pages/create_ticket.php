<?php
/**
 * Create Ticket page
 */
auth_require_login();

$categories = get_categories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject     = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $priority    = isset($_POST['priority']) ? $_POST['priority'] : 'medium';

    if ($subject === '' || $description === '') {
        $error = 'Subject and description are required.';
    } else {
        $ticket_id = create_ticket(
            get_session_user_id(),
            $subject,
            $description,
            $category_id > 0 ? $category_id : null,
            $priority
        );

        // Handle file attachment
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
            save_attachment($ticket_id, get_session_user_id(), $_FILES['attachment']);
        }

        set_flash('success', 'Ticket #' . $ticket_id . ' created successfully.');
        header('Location: ' . BASE_URL . 'index.php?page=view_ticket&id=' . $ticket_id);
        exit;
    }
}
?>

<div class="card">
    <div class="card-header">
        <h2>Create New Ticket</h2>
        <a href="<?php echo BASE_URL; ?>index.php?page=dashboard" class="btn btn-secondary btn-sm">Back</a>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo e($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="<?php echo BASE_URL; ?>index.php?page=create_ticket" enctype="multipart/form-data">
        <div class="form-group">
            <label for="subject">Subject</label>
            <input type="text" id="subject" name="subject" class="form-control"
                   value="<?php echo isset($subject) ? e($subject) : ''; ?>" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="form-control" rows="5" required><?php echo isset($description) ? e($description) : ''; ?></textarea>
        </div>

        <div style="display:flex; gap:1rem; flex-wrap:wrap;">
            <div class="form-group" style="flex:1; min-width:200px;">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id" class="form-control">
                    <option value="0">-- Select --</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>"
                        <?php echo (isset($category_id) && $category_id == $cat['id']) ? 'selected' : ''; ?>>
                        <?php echo e($cat['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="flex:1; min-width:200px;">
                <label for="priority">Priority</label>
                <select id="priority" name="priority" class="form-control">
                    <option value="low" <?php echo (isset($priority) && $priority==='low') ? 'selected' : ''; ?>>Low</option>
                    <option value="medium" <?php echo (!isset($priority) || $priority==='medium') ? 'selected' : ''; ?>>Medium</option>
                    <option value="high" <?php echo (isset($priority) && $priority==='high') ? 'selected' : ''; ?>>High</option>
                    <option value="critical" <?php echo (isset($priority) && $priority==='critical') ? 'selected' : ''; ?>>Critical</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="attachment">Attachment (optional)</label>
            <input type="file" id="attachment" name="attachment" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Submit Ticket</button>
    </form>
</div>
