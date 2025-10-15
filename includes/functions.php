<?php
// includes/functions.php - Common Functions
require_once __DIR__ . '/db.php';

// Helper: Create initial admin user (run once, then remove or comment out)
function create_initial_admin($username, $password) {
    global $conn;
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare('INSERT INTO settings (admin_username, admin_password, license_type) VALUES (?, ?, "free")');
    $stmt->bind_param('ss', $username, $hash);
    return $stmt->execute();
}

/*
// Example usage (uncomment and run once):
// if (create_initial_admin('admin', 'yourpassword')) {
//     echo 'Admin user created!';
// } else {
//     echo 'Failed to create admin user.';
// }
*/

// Get license type from settings
function getLicenseType() {
    global $conn;
    $result = $conn->query('SELECT license_type FROM settings WHERE id = 1 LIMIT 1');
    if ($result && $row = $result->fetch_assoc()) {
        return $row['license_type'];
    }
    return 'free';
}

// TEMP: Create admin user 'learningkids' with password 'learningkids202526'
// Uncomment the following lines, load this file once, then comment/remove them for security.

if (create_initial_admin('learningkids', 'learningkids202526')) {
    echo 'Admin user created!';
} else {
    echo 'Failed to create admin user.';
}

?> 