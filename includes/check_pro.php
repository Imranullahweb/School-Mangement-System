<?php
require_once __DIR__ . '/functions.php';
$license = getLicenseType();
if ($license !== 'pro') {
    die('🔒 This is a Pro feature. Please activate to access.');
}
?> 