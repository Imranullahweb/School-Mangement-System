<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    die('Please log in first.');
}

// Get license type from database
$license_type = getLicenseType();
$is_pro = $license_type === 'pro';

// Get current session status
$session_is_pro = isset($_SESSION['is_pro']) && $_SESSION['is_pro'] === true;

echo "<h2>Pro Status Check</h2>";
echo "<p>Database License Type: <strong>" . htmlspecialchars($license_type) . "</strong></p>";
echo "<p>Is Pro (from database): <strong>" . ($is_pro ? 'YES' : 'NO') . "</strong></p>";
echo "<p>Session is_pro: <strong>" . ($session_is_pro ? 'true' : 'false') . "</strong></p>";

// Show all session data (for debugging)
echo "<h3>Session Data:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Show all settings from database
$result = $conn->query('SELECT * FROM settings WHERE id = 1');
if ($row = $result->fetch_assoc()) {
    echo "<h3>Database Settings:</h3>";
    echo "<pre>";
    print_r($row);
    echo "</pre>";
} else {
    echo "<p>No settings found in database.</p>";
}
?>
