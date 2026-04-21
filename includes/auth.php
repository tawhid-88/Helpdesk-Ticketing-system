<?php
/**
 * Authentication functions
 */

function auth_register($name, $email, $password, $role = 'student') {
    $pdo = db_connect();

    // Check if email exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute(array($email));
    if ($stmt->fetch()) {
        return array('success' => false, 'error' => 'Email already registered.');
    }

    $hash = md5($password);
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password_md5, role) VALUES (?, ?, ?, ?)');
    $stmt->execute(array($name, $email, $hash, $role));

    return array('success' => true, 'user_id' => (int)$pdo->lastInsertId());
}

function auth_login($email, $password) {
    $pdo = db_connect();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute(array($email));
    $user = $stmt->fetch();

    if (!$user || md5($password) !== $user['password_md5']) {
        return array('success' => false, 'error' => 'Invalid email or password.');
    }

    set_session_user($user);
    return array('success' => true, 'user' => $user);
}

function auth_require_login() {
    if (!is_logged_in()) {
        set_flash('warning', 'Please log in to continue.');
        header('Location: ' . BASE_URL . 'index.php?page=login');
        exit;
    }
}

function auth_require_staff() {
    auth_require_login();
    if (!is_staff()) {
        set_flash('danger', 'Access denied. Staff only.');
        header('Location: ' . BASE_URL . 'index.php?page=dashboard');
        exit;
    }
}
