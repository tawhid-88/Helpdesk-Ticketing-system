<?php
/**
 * Session management functions
 */

function session_init() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function set_session_user($user) {
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['logged_in'] = true;
}

function get_session_user_id() {
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
}

function get_session_user_name() {
    return isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
}

function get_session_user_role() {
    return isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
}

function is_logged_in() {
    return !empty($_SESSION['logged_in']) && !empty($_SESSION['user_id']);
}

function is_staff() {
    return get_session_user_role() === ROLE_STAFF;
}

function is_faculty() {
    return get_session_user_role() === ROLE_FACULTY;
}

function is_student() {
    return get_session_user_role() === ROLE_STUDENT;
}

function session_destroy_all() {
    $_SESSION = array();
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

function set_flash($type, $message) {
    $_SESSION['flash'] = array('type' => $type, 'message' => $message);
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
