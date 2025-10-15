<?php
// includes/auth.php - Admin Authentication Functions
session_start();
require_once __DIR__ . '/db.php';

// Login function
function admin_login($username, $password) {
    global $conn;
    $stmt = $conn->prepare('SELECT id, admin_password FROM settings WHERE admin_username = ? LIMIT 1');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $hash);
        $stmt->fetch();
        if (password_verify($password, $hash)) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $id;
            return true;
        }
    }
    return false;
}

// Check if admin is logged in
function is_admin_logged_in() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

// Logout function
function admin_logout() {
    session_unset();
    session_destroy();
}
?> 