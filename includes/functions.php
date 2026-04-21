<?php
/**
 * Utility / helper functions
 */

function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

function redirect($page) {
    header('Location: ' . BASE_URL . 'index.php?page=' . $page);
    exit;
}

function get_priority_badge($priority) {
    $colors = array(
        'low'      => '#28a745',
        'medium'   => '#ffc107',
        'high'     => '#fd7e14',
        'critical' => '#dc3545',
    );
    $color = isset($colors[$priority]) ? $colors[$priority] : '#6c757d';
    return '<span class="badge" style="background:' . $color . ';">' . e(ucfirst($priority)) . '</span>';
}

function get_status_badge($status) {
    $colors = array(
        'open'        => '#17a2b8',
        'in_progress' => '#ffc107',
        'resolved'    => '#28a745',
        'closed'      => '#6c757d',
    );
    $labels = array(
        'open'        => 'Open',
        'in_progress' => 'In Progress',
        'resolved'    => 'Resolved',
        'closed'      => 'Closed',
    );
    $color = isset($colors[$status]) ? $colors[$status] : '#6c757d';
    $label = isset($labels[$status]) ? $labels[$status] : $status;
    return '<span class="badge" style="background:' . $color . ';">' . e($label) . '</span>';
}

function time_ago($datetime) {
    $now  = time();
    $then = strtotime($datetime);
    $diff = $now - $then;

    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('M j, Y', $then);
}

// ---- Ticket functions ----

function create_ticket($user_id, $subject, $description, $category_id, $priority) {
    $pdo = db_connect();
    $stmt = $pdo->prepare('INSERT INTO tickets (user_id, subject, description, category_id, priority) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute(array($user_id, $subject, $description, $category_id ? $category_id : null, $priority));
    return (int)$pdo->lastInsertId();
}

function get_ticket($id) {
    $pdo = db_connect();
    $stmt = $pdo->prepare('
        SELECT t.*, u.name AS creator_name, a.name AS staff_name, c.name AS category_name
        FROM tickets t
        LEFT JOIN users u ON t.user_id = u.id
        LEFT JOIN users a ON t.assigned_staff_id = a.id
        LEFT JOIN categories c ON t.category_id = c.id
        WHERE t.id = ?
    ');
    $stmt->execute(array($id));
    return $stmt->fetch();
}

function get_tickets_for_user($user_id, $filters = array()) {
    return _get_tickets('t.user_id = ?', array($user_id), $filters);
}

function get_all_tickets($filters = array()) {
    return _get_tickets('1=1', array(), $filters);
}

function get_tickets_for_staff($staff_id, $filters = array()) {
    return _get_tickets('t.assigned_staff_id = ?', array($staff_id), $filters);
}

function _get_tickets($base_where, $base_params, $filters) {
    $pdo = db_connect();
    $where = $base_where;
    $params = $base_params;

    if (!empty($filters['status'])) {
        $where .= ' AND t.status = ?';
        $params[] = $filters['status'];
    }
    if (!empty($filters['priority'])) {
        $where .= ' AND t.priority = ?';
        $params[] = $filters['priority'];
    }
    if (!empty($filters['category_id'])) {
        $where .= ' AND t.category_id = ?';
        $params[] = $filters['category_id'];
    }
    if (!empty($filters['search'])) {
        $where .= ' AND (t.subject LIKE ? OR t.description LIKE ?)';
        $term = '%' . $filters['search'] . '%';
        $params[] = $term;
        $params[] = $term;
    }

    $page = isset($filters['page']) ? max(1, (int)$filters['page']) : 1;
    $offset = ($page - 1) * ITEMS_PER_PAGE;

    // Count total
    $count_sql = "SELECT COUNT(*) FROM tickets t WHERE $where";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();

    // Fetch page
    $sql = "SELECT t.*, u.name AS creator_name, a.name AS staff_name, c.name AS category_name
            FROM tickets t
            LEFT JOIN users u ON t.user_id = u.id
            LEFT JOIN users a ON t.assigned_staff_id = a.id
            LEFT JOIN categories c ON t.category_id = c.id
            WHERE $where
            ORDER BY t.created_at DESC
            LIMIT " . (int)ITEMS_PER_PAGE . " OFFSET " . (int)$offset;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    return array(
        'tickets'    => $rows,
        'total'      => $total,
        'page'       => $page,
        'total_pages'=> max(1, (int)ceil($total / ITEMS_PER_PAGE)),
    );
}

function update_ticket_status($ticket_id, $new_status, $changed_by) {
    $pdo = db_connect();
    $old = get_ticket($ticket_id);
    if (!$old) return false;

    $stmt = $pdo->prepare('UPDATE tickets SET status = ? WHERE id = ?');
    $stmt->execute(array($new_status, $ticket_id));

    log_ticket_change($ticket_id, $changed_by, 'status', $old['status'], $new_status);
    return true;
}

function assign_ticket($ticket_id, $staff_id, $changed_by) {
    $pdo = db_connect();
    $old = get_ticket($ticket_id);
    if (!$old) return false;

    $stmt = $pdo->prepare('UPDATE tickets SET assigned_staff_id = ? WHERE id = ?');
    $stmt->execute(array($staff_id ? $staff_id : null, $ticket_id));

    log_ticket_change($ticket_id, $changed_by, 'assigned_staff_id',
        $old['assigned_staff_id'] ? $old['assigned_staff_id'] : 'none',
        $staff_id ? $staff_id : 'none');
    return true;
}

function log_ticket_change($ticket_id, $changed_by, $field, $old_val, $new_val) {
    $pdo = db_connect();
    $stmt = $pdo->prepare('INSERT INTO ticket_history (ticket_id, changed_by, field_changed, old_value, new_value) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute(array($ticket_id, $changed_by, $field, $old_val, $new_val));
}

// ---- Comment functions ----

function add_comment($ticket_id, $user_id, $body, $is_internal = 0) {
    $pdo = db_connect();
    $stmt = $pdo->prepare('INSERT INTO comments (ticket_id, user_id, body, is_internal) VALUES (?, ?, ?, ?)');
    $stmt->execute(array($ticket_id, $user_id, $body, $is_internal ? 1 : 0));
    return (int)$pdo->lastInsertId();
}

function get_comments($ticket_id, $include_internal = false) {
    $pdo = db_connect();
    $sql = 'SELECT cm.*, u.name AS author_name, u.role AS author_role
            FROM comments cm
            LEFT JOIN users u ON cm.user_id = u.id
            WHERE cm.ticket_id = ?';
    if (!$include_internal) {
        $sql .= ' AND cm.is_internal = 0';
    }
    $sql .= ' ORDER BY cm.created_at ASC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array($ticket_id));
    return $stmt->fetchAll();
}

// ---- Attachment functions ----

function save_attachment($ticket_id, $uploaded_by, $file, $comment_id = null) {
    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    if ($file['size'] > MAX_FILE_SIZE) return false;

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) return false;

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    $safe_name = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
    $dest = UPLOAD_DIR . $safe_name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) return false;

    $pdo = db_connect();
    $stmt = $pdo->prepare('INSERT INTO attachments (ticket_id, comment_id, file_name, file_path, uploaded_by) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute(array($ticket_id, $comment_id, $file['name'], $safe_name, $uploaded_by));
    return true;
}

function get_attachments($ticket_id) {
    $pdo = db_connect();
    $stmt = $pdo->prepare('SELECT * FROM attachments WHERE ticket_id = ? ORDER BY created_at ASC');
    $stmt->execute(array($ticket_id));
    return $stmt->fetchAll();
}

// ---- Category functions ----

function get_categories() {
    $pdo = db_connect();
    return $pdo->query('SELECT * FROM categories ORDER BY name ASC')->fetchAll();
}

function add_category($name, $description) {
    $pdo = db_connect();
    $stmt = $pdo->prepare('INSERT INTO categories (name, description) VALUES (?, ?)');
    $stmt->execute(array($name, $description));
    return (int)$pdo->lastInsertId();
}

function delete_category($id) {
    $pdo = db_connect();
    $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
    $stmt->execute(array($id));
}

// ---- User functions ----

function get_user($id) {
    $pdo = db_connect();
    $stmt = $pdo->prepare('SELECT id, name, email, role, created_at FROM users WHERE id = ?');
    $stmt->execute(array($id));
    return $stmt->fetch();
}

function get_staff_members() {
    $pdo = db_connect();
    return $pdo->query("SELECT id, name, email FROM users WHERE role = 'staff' ORDER BY name ASC")->fetchAll();
}

function update_user_profile($id, $name, $email) {
    $pdo = db_connect();
    $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
    $stmt->execute(array($name, $email, $id));
}

function update_user_password($id, $new_password) {
    $pdo = db_connect();
    $hash = md5($new_password);
    $stmt = $pdo->prepare('UPDATE users SET password_md5 = ? WHERE id = ?');
    $stmt->execute(array($hash, $id));
}

function get_all_users($filters = array()) {
    $pdo = db_connect();
    $where = '1=1';
    $params = array();

    if (!empty($filters['role'])) {
        $where .= ' AND role = ?';
        $params[] = $filters['role'];
    }
    if (!empty($filters['search'])) {
        $where .= ' AND (name LIKE ? OR email LIKE ?)';
        $term = '%' . $filters['search'] . '%';
        $params[] = $term;
        $params[] = $term;
    }

    $page = isset($filters['page']) ? max(1, (int)$filters['page']) : 1;
    $offset = ($page - 1) * ITEMS_PER_PAGE;

    // Count total
    $count_sql = "SELECT COUNT(*) FROM users WHERE $where";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();

    // Fetch page
    $sql = "SELECT id, name, email, role, created_at
            FROM users
            WHERE $where
            ORDER BY created_at DESC
            LIMIT " . (int)ITEMS_PER_PAGE . " OFFSET " . (int)$offset;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    return array(
        'users'      => $rows,
        'total'      => $total,
        'page'       => $page,
        'total_pages'=> max(1, (int)ceil($total / ITEMS_PER_PAGE)),
    );
}

function delete_user($id, $current_user_id) {
    if ((int)$id === (int)$current_user_id) {
        return false; // Cannot delete yourself
    }
    $pdo = db_connect();
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
    $stmt->execute(array($id));
    return $stmt->rowCount() > 0;
}

function get_user_stats() {
    $pdo = db_connect();
    $stats = array();
    $result = $pdo->query("SELECT role, COUNT(*) AS cnt FROM users GROUP BY role")->fetchAll();
    foreach ($result as $row) {
        $stats[$row['role']] = (int)$row['cnt'];
    }
    $stats['total'] = array_sum($stats);
    return $stats;
}

function get_ticket_history($ticket_id) {
    $pdo = db_connect();
    $stmt = $pdo->prepare('SELECT th.*, u.name AS changer_name FROM ticket_history th LEFT JOIN users u ON th.changed_by = u.id WHERE th.ticket_id = ? ORDER BY th.created_at DESC');
    $stmt->execute(array($ticket_id));
    return $stmt->fetchAll();
}

// ---- Stats (staff dashboard) ----

function get_ticket_stats() {
    $pdo = db_connect();
    $stats = array();
    $result = $pdo->query("SELECT status, COUNT(*) AS cnt FROM tickets GROUP BY status")->fetchAll();
    foreach ($result as $row) {
        $stats[$row['status']] = (int)$row['cnt'];
    }
    $stats['total'] = array_sum($stats);
    return $stats;
}
