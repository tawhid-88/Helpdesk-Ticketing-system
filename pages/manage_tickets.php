<?php
/**
 * Manage Tickets page (staff only)
 */
auth_require_staff();

$filters = array(
    'status'      => isset($_GET['status']) ? $_GET['status'] : '',
    'priority'    => isset($_GET['priority']) ? $_GET['priority'] : '',
    'category_id' => isset($_GET['category_id']) ? $_GET['category_id'] : '',
    'search'      => isset($_GET['search']) ? $_GET['search'] : '',
    'page'        => isset($_GET['p']) ? (int)$_GET['p'] : 1,
);

$data = get_all_tickets($filters);
$tickets     = $data['tickets'];
$total_pages = $data['total_pages'];
$cur_page    = $data['page'];
$categories  = get_categories();

// Handle quick assign / status via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tid    = isset($_POST['ticket_id']) ? (int)$_POST['ticket_id'] : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'quick_assign' && $tid > 0) {
        $staff_id = isset($_POST['staff_id']) ? (int)$_POST['staff_id'] : 0;
        assign_ticket($tid, $staff_id > 0 ? $staff_id : null, get_session_user_id());
        set_flash('success', 'Ticket #' . $tid . ' assigned.');
    } elseif ($action === 'quick_status' && $tid > 0) {
        $new_status = isset($_POST['status']) ? $_POST['status'] : '';
        if (in_array($new_status, array('open','in_progress','resolved','closed'))) {
            update_ticket_status($tid, $new_status, get_session_user_id());
            set_flash('success', 'Ticket #' . $tid . ' status updated.');
        }
    }
    header('Location: ' . BASE_URL . 'index.php?page=manage_tickets');
    exit;
}
?>

<div class="card">
    <div class="card-header">
        <h2>Manage All Tickets</h2>
    </div>

    <form method="GET" action="<?php echo BASE_URL; ?>index.php" class="filters-bar">
        <input type="hidden" name="page" value="manage_tickets">
        <div class="form-group">
            <label>Search</label>
            <input type="text" name="search" class="form-control" placeholder="Keyword..."
                   value="<?php echo e($filters['search']); ?>">
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="">All</option>
                <option value="open" <?php echo $filters['status']==='open'?'selected':''; ?>>Open</option>
                <option value="in_progress" <?php echo $filters['status']==='in_progress'?'selected':''; ?>>In Progress</option>
                <option value="resolved" <?php echo $filters['status']==='resolved'?'selected':''; ?>>Resolved</option>
                <option value="closed" <?php echo $filters['status']==='closed'?'selected':''; ?>>Closed</option>
            </select>
        </div>
        <div class="form-group">
            <label>Priority</label>
            <select name="priority" class="form-control">
                <option value="">All</option>
                <option value="low" <?php echo $filters['priority']==='low'?'selected':''; ?>>Low</option>
                <option value="medium" <?php echo $filters['priority']==='medium'?'selected':''; ?>>Medium</option>
                <option value="high" <?php echo $filters['priority']==='high'?'selected':''; ?>>High</option>
                <option value="critical" <?php echo $filters['priority']==='critical'?'selected':''; ?>>Critical</option>
            </select>
        </div>
        <div class="form-group">
            <label>&nbsp;</label>
            <button type="submit" class="btn btn-secondary btn-sm">Filter</button>
        </div>
    </form>

    <?php if (empty($tickets)): ?>
        <p style="color:#888; text-align:center; padding:2rem 0;">No tickets found.</p>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Subject</th>
                    <th>User</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Assigned</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tickets as $t): ?>
                <tr>
                    <td>#<?php echo $t['id']; ?></td>
                    <td><a href="<?php echo BASE_URL; ?>index.php?page=view_ticket&id=<?php echo $t['id']; ?>"><?php echo e($t['subject']); ?></a></td>
                    <td><?php echo e($t['creator_name']); ?></td>
                    <td><?php echo get_status_badge($t['status']); ?></td>
                    <td><?php echo get_priority_badge($t['priority']); ?></td>
                    <td><?php echo e($t['staff_name'] ? $t['staff_name'] : '-'); ?></td>
                    <td><?php echo time_ago($t['created_at']); ?></td>
                    <td>
                        <a href="<?php echo BASE_URL; ?>index.php?page=view_ticket&id=<?php echo $t['id']; ?>" class="btn btn-secondary btn-sm">View</a>
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
