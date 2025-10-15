<?php
require_once 'includes/auth.php';
if (!is_admin_logged_in()) {
    header('Location: index.php');
    exit;
}
require_once 'includes/db.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teacher_id = trim($_POST['teacher_id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $father_name = trim($_POST['father_name'] ?? '');
    $designation = trim($_POST['designation'] ?? '');
    $qualification = trim($_POST['qualification'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $salary = trim($_POST['salary'] ?? '');
    $appointment_date = $_POST['appointment_date'] ?? '';
    $leaving_date = $_POST['leaving_date'] ?? null;

    if ($teacher_id && $name && $father_name && $appointment_date) {
        $stmt = $conn->prepare('INSERT INTO teachers (teacher_id, name, father_name, designation, qualification, phone, address, salary, appointment_date, leaving_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('ssssssssss', $teacher_id, $name, $father_name, $designation, $qualification, $phone, $address, $salary, $appointment_date, $leaving_date);
        if ($stmt->execute()) {
            $success = 'Teacher registered successfully!';
        } else {
            $error = 'Database error. Please try again.';
        }
    } else {
        $error = 'Please fill in all required fields (*).';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Registration</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="form-container">
        <h2>Teacher Registration</h2>
        <?php if ($success): ?>
            <div class="success-msg"><?php echo $success; ?></div>
        <?php elseif ($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <label for="teacher_id">Teacher ID*</label>
            <input type="text" name="teacher_id" id="teacher_id" required>
            <label for="name">Name*</label>
            <input type="text" name="name" id="name" required>
            <label for="father_name">Father Name / Husband Name*</label>
            <input type="text" name="father_name" id="father_name" required>
            <label for="designation">Designation</label>
            <input type="text" name="designation" id="designation">
            <label for="qualification">Qualification</label>
            <input type="text" name="qualification" id="qualification">
            <label for="phone">Phone</label>
            <input type="text" name="phone" id="phone">
            <label for="address">Address</label>
            <input type="text" name="address" id="address">
            <label for="salary">Salary</label>
            <input type="number" step="0.01" name="salary" id="salary">
            <label for="appointment_date">Appointment Date*</label>
            <input type="date" name="appointment_date" id="appointment_date" required>
            <label for="leaving_date">Leaving Date (optional)</label>
            <input type="date" name="leaving_date" id="leaving_date">
            <button type="submit">Register Teacher</button>
        </form>
    </div>
</body>
</html> 