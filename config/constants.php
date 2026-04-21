<?php
// Application constants
define('APP_NAME', 'NSU Helpdesk Ticketing System');
define('APP_VERSION', '1.0.0');
define('BASE_URL', '/Project-cse311/');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5 MB
define('ALLOWED_EXTENSIONS', array('jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt', 'zip'));

// Roles
define('ROLE_STUDENT', 'student');
define('ROLE_FACULTY', 'faculty');
define('ROLE_STAFF', 'staff');

// Ticket statuses
define('STATUS_OPEN', 'open');
define('STATUS_IN_PROGRESS', 'in_progress');
define('STATUS_RESOLVED', 'resolved');
define('STATUS_CLOSED', 'closed');

// Priority levels
define('PRIORITY_LOW', 'low');
define('PRIORITY_MEDIUM', 'medium');
define('PRIORITY_HIGH', 'high');
define('PRIORITY_CRITICAL', 'critical');

// Pagination
define('ITEMS_PER_PAGE', 10);
