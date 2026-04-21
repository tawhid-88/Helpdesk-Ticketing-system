<?php
/**
 * Dashboard page
 */
auth_require_login();

$user_id = get_session_user_id();
$role    = get_session_user_role();

// Build filters from query string
$filters = array(
    'status'      => isset($_GET['status']) ? $_GET['status'] : '',
    'priority'    => isset($_GET['priority']) ? $_GET['priority'] : '',
    'category_id' => isset($_GET['category_id']) ? $_GET['category_id'] : '',
    'search'      => isset($_GET['search']) ? $_GET['search'] : '',
    'page'        => isset($_GET['p']) ? (int)$_GET['p'] : 1,
);

if (is_staff()) {
    $data = get_all_tickets($filters);
    $stats = get_ticket_stats();
} else {
    // Students and faculty see only their own tickets
    $data = get_tickets_for_user($user_id, $filters);
    $stats = null;
}

$tickets     = $data['tickets'];
$total_pages = $data['total_pages'];
$cur_page    = $data['page'];
$categories  = get_categories();
?>

<?php if (is_staff() && $stats): ?>
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number"><?php echo isset($stats['total']) ? $stats['total'] : 0; ?></div>
        <div class="stat-label">Total Tickets</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?php echo isset($stats['open']) ? $stats['open'] : 0; ?></div>
        <div class="stat-label">Open</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?php echo isset($stats['in_progress']) ? $stats['in_progress'] : 0; ?></div>
        <div class="stat-label">In Progress</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?php echo isset($stats['resolved']) ? $stats['resolved'] : 0; ?></div>
        <div class="stat-label">Resolved</div>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2><?php echo is_staff() ? 'All Tickets' : 'My Tickets'; ?></h2>
        <a href="<?php echo BASE_URL; ?>index.php?page=create_ticket" class="btn btn-primary btn-sm">+ New Ticket</a>
    </div>

    <form method="GET" action="<?php echo BASE_URL; ?>index.php" id="searchForm" class="filters-bar">
        <input type="hidden" name="page" value="dashboard">
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
            <label>Category</label>
            <select name="category_id" class="form-control">
                <option value="">All</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['id']; ?>" <?php echo $filters['category_id']===$cat['id']?'selected':''; ?>><?php echo e($cat['name']); ?></option>
                <?php endforeach; ?>
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
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Category</th>
                    <?php if (is_staff()): ?><th>Created By</th><?php endif; ?>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tickets as $t): ?>
                <tr>
                    <td>#<?php echo $t['id']; ?></td>
                    <td><a href="<?php echo BASE_URL; ?>index.php?page=view_ticket&id=<?php echo $t['id']; ?>"><?php echo e($t['subject']); ?></a></td>
                    <td><?php echo get_status_badge($t['status']); ?></td>
                    <td><?php echo get_priority_badge($t['priority']); ?></td>
                    <td><?php echo e($t['category_name'] ? $t['category_name'] : '-'); ?></td>
                    <?php if (is_staff()): ?><td><?php echo e($t['creator_name']); ?></td><?php endif; ?>
                    <td><?php echo time_ago($t['created_at']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <?php
            $qs = $_GET;
            $qs['p'] = $i;
            $url = BASE_URL . 'index.php?' . http_build_query($qs);
            ?>
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
