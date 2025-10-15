<?php
require_once 'includes/db.php';

$success = '';
$error = '';
$validKey = '2917020220070101';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enteredKey = trim($_POST['license_key'] ?? '');
    if ($enteredKey === $validKey) {
        $stmt = $conn->prepare('UPDATE settings SET license_key = ?, license_type = "pro" WHERE id = 1');
        $stmt->bind_param('s', $enteredKey);
        if ($stmt->execute()) {
            $success = '🎉 Pro features activated!';
        } else {
            $error = 'Database error. Please try again.';
        }
    } else {
        $error = '❌ Invalid activation key.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activate Pro Features</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="activation-container">
        <h2>Activate Pro Features</h2>
        <?php if ($success): ?>
            <div class="success-msg"><?php echo $success; ?></div>
        <?php elseif ($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <label for="license_key">Activation Key</label>
            <input type="text" name="license_key" id="license_key" required autofocus>
            <button type="submit">Activate</button>
        </form>
    </div>
</body>
</html> 