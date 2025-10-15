<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';
$license_type = getLicenseType();

if ($license_type === 'pro') {
    // Create backup directory if not exists
    $backupDir = __DIR__ . '/backups';
    if (!is_dir($backupDir)) mkdir($backupDir, 0777, true);
    $dbHost = $host;
    $dbUser = $user;
    $dbPass = $pass;
    $dbName = $dbname;
    $timestamp = date('Ymd_His');
    $backupFile = $backupDir . "/backup_{$timestamp}.sql";
    // Use mysqldump if available
    $cmd = "mysqldump -u{$dbUser} -p{$dbPass} {$dbName} > \"{$backupFile}\"";
    @exec($cmd);
    // Optionally, could add error handling/logging
}
admin_logout();
header('Location: index.php');
exit;
?>