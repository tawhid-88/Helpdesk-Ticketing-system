<?php
/**
 * View single ticket page
 */
auth_require_login();

$ticket_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$ticket = get_ticket($ticket_id);

if (!$ticket) {
    set_flash('danger', 'Ticket not found.');
    redirect('dashboard');
}

// Students/faculty can only view their own tickets
if (!is_staff() && $ticket['user_id'] != get_session_user_id()) {
    set_flash('danger', 'Access denied.');
    redirect('dashboard');
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'comment') {
        $body = isset($_POST['body']) ? trim($_POST['body']) : '';
        $is_internal = isset($_POST['is_internal']) ? 1 : 0;
        if ($body !== '') {
            $comment_id = add_comment($ticket_id, get_session_user_id(), $body, $is_internal);
            if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
                save_attachment($ticket_id, get_session_user_id(), $_FILES['attachment'], $comment_id);
            }
            set_flash('success', 'Comment added.');
        }
    } elseif ($action === 'update_status' && is_staff()) {
        $new_status = isset($_POST['status']) ? $_POST['status'] : '';
        if (in_array($new_status, array('open','in_progress','resolved','closed'))) {
            update_ticket_status($ticket_id, $new_status, get_session_user_id());
            set_flash('success', 'Status updated.');
        }
    } elseif ($action === 'assign' && is_staff()) {
        $staff_id = isset($_POST['staff_id']) ? (int)$_POST['staff_id'] : 0;
        assign_ticket($ticket_id, $staff_id > 0 ? $staff_id : null, get_session_user_id());
        set_flash('success', 'Ticket assigned.');
    }

    header('Location: ' . BASE_URL . 'index.php?page=view_ticket&id=' . $ticket_id);
    exit;
}

// Load data
$ticket = get_ticket($ticket_id);
$comments    = get_comments($ticket_id, is_staff());
$attachments = get_attachments($ticket_id);
$history     = get_ticket_history($ticket_id);
$staff_list  = is_staff() ? get_staff_members() : array();
?>

<div class="card">
    <div class="card-header">
        <h2>Ticket #<?php echo $ticket['id']; ?>: <?php echo e($ticket['subject']); ?></h2>
        <a href="<?php echo BASE_URL; ?>index.php?page=dashboard" class="btn btn-secondary btn-sm">Back</a>
    </div>

    <div class="ticket-meta">
        <div class="meta-item">
            <span class="meta-label">Status</span>
            <?php echo get_status_badge($ticket['status']); ?>
        </div>
        <div class="meta-item">
            <span class="meta-label">Priority</span>
            <?php echo get_priority_badge($ticket['priority']); ?>
        </div>
        <div class="meta-item">
            <span class="meta-label">Category</span>
            <?php echo e($ticket['category_name'] ? $ticket['category_name'] : 'None'); ?>
        </div>
        <div class="meta-item">
            <span class="meta-label">Created By</span>
            <?php echo e($ticket['creator_name']); ?>
        </div>
        <div class="meta-item">
            <span class="meta-label">Assigned To</span>
            <?php echo e($ticket['staff_name'] ? $ticket['staff_name'] : 'Unassigned'); ?>
        </div>
        <div class="meta-item">
            <span class="meta-label">Created</span>
            <?php echo e($ticket['created_at']); ?>
        </div>
    </div>

    <div style="margin-bottom:1.5rem; line-height:1.7;">
        <?php echo nl2br(e($ticket['description'])); ?>
    </div>

    <?php if (!empty($attachments)): ?>
    <h3 style="font-size:0.95rem; color:#8ab4f8; margin-bottom:0.5rem;">Attachments</h3>
    <ul class="attachment-list">
        <?php foreach ($attachments as $att): ?>
        <li><a href="<?php echo BASE_URL . 'uploads/' . e($att['file_path']); ?>" target="_blank"><?php echo e($att['file_name']); ?></a></li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
</div>

<!-- Staff Actions -->
<?php if (is_staff()): ?>
<div class="card">
    <div class="card-header"><h2>Actions</h2></div>
    <div style="display:flex; gap:1rem; flex-wrap:wrap;">
        <form method="POST" style="display:flex; gap:0.5rem; align-items:flex-end;">
            <input type="hidden" name="action" value="update_status">
            <div class="form-group">
                <label>Update Status</label>
                <select name="status" class="form-control">
                    <option value="open" <?php echo $ticket['status']==='open'?'selected':''; ?>>Open</option>
                    <option value="in_progress" <?php echo $ticket['status']==='in_progress'?'selected':''; ?>>In Progress</option>
                    <option value="resolved" <?php echo $ticket['status']==='resolved'?'selected':''; ?>>Resolved</option>
                    <option value="closed" <?php echo $ticket['status']==='closed'?'selected':''; ?>>Closed</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success btn-sm">Update</button>
        </form>

        <form method="POST" style="display:flex; gap:0.5rem; align-items:flex-end;">
            <input type="hidden" name="action" value="assign">
            <div class="form-group">
                <label>Assign Staff</label>
                <select name="staff_id" class="form-control">
                    <option value="0">Unassigned</option>
                    <?php foreach ($staff_list as $s): ?>
                    <option value="<?php echo $s['id']; ?>" <?php echo $ticket['assigned_staff_id']==$s['id']?'selected':''; ?>>
                        <?php echo e($s['name']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-success btn-sm">Assign</button>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Comments -->
<div class="card">
    <div class="card-header"><h2>Comments (<?php echo count($comments); ?>)</h2></div>

    <?php if (empty($comments)): ?>
        <p style="color:#888; padding:1rem 0;">No comments yet.</p>
    <?php else: ?>
        <?php foreach ($comments as $cm): ?>
        <div class="comment <?php echo $cm['is_internal'] ? 'internal' : ''; ?>">
            <div class="comment-header">
                <span><span class="author"><?php echo e($cm['author_name']); ?></span>
                (<?php echo e(ucfirst($cm['author_role'])); ?>)
                <?php if ($cm['is_internal']): ?> <span class="badge" style="background:#ffc107; color:#000; font-size:0.65rem;">Internal</span><?php endif; ?>
                </span>
                <span><?php echo time_ago($cm['created_at']); ?></span>
            </div>
            <div><?php echo nl2br(e($cm['body'])); ?></div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($ticket['status'] !== 'closed'): ?>
    <form method="POST" enctype="multipart/form-data" style="margin-top:1rem;">
        <input type="hidden" name="action" value="comment">
        <div class="form-group">
            <label>Add Comment</label>
            <textarea name="body" class="form-control" rows="3" placeholder="Write your reply..." required></textarea>
        </div>
        <div style="display:flex; gap:1rem; align-items:center; flex-wrap:wrap;">
            <div class="form-group" style="flex:1; min-width:200px;">
                <input type="file" name="attachment" class="form-control">
            </div>
            <?php if (is_staff()): ?>
            <label style="font-size:0.85rem; color:#aaa; cursor:pointer;">
                <input type="checkbox" name="is_internal" value="1"> Internal note
            </label>
            <?php endif; ?>
            <button type="submit" class="btn btn-primary btn-sm">Post Comment</button>
        </div>
    </form>
    <?php endif; ?>
</div>

<!-- History -->
<?php if (is_staff() && !empty($history)): ?>
<div class="card">
    <div class="card-header"><h2>History</h2></div>
    <?php foreach ($history as $h): ?>
    <div class="history-item">
        <strong><?php echo e($h['changer_name']); ?></strong> changed
        <em><?php echo e($h['field_changed']); ?></em>
        from "<?php echo e($h['old_value']); ?>"
        to "<?php echo e($h['new_value']); ?>"
        -- <?php echo time_ago($h['created_at']); ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
