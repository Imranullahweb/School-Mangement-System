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
    $reg_no = trim($_POST['reg_no'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    $admission_date = $_POST['admission_date'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $father_name = trim($_POST['father_name'] ?? '');
    $dob = $_POST['dob'] ?? '';
    $address = trim($_POST['address'] ?? '');
    $class = trim($_POST['class'] ?? '');
    $religion = trim($_POST['religion'] ?? '');

    if ($reg_no && $student_id && $admission_date && $name && $father_name && $dob && $class) {
        $stmt = $conn->prepare('INSERT INTO students (reg_no, student_id, admission_date, name, father_name, dob, address, class, religion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('sssssssss', $reg_no, $student_id, $admission_date, $name, $father_name, $dob, $address, $class, $religion);
        if ($stmt->execute()) {
            $success = 'Student registered successfully!';
        } else {
            $error = 'Database error. Please try again.';
        }
    } else {
        $error = 'Please fill in all required fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration</title>
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="form-container">
        <h2>Student Registration</h2>
        <?php if ($success): ?>
            <div class="success-msg"><?php echo $success; ?></div>
        <?php elseif ($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <label for="reg_no">Reg No*</label>
            <input type="text" name="reg_no" id="reg_no" required>
            <label for="student_id">Student ID*</label>
            <input type="text" name="student_id" id="student_id" required>
            <label for="admission_date">Admission Date*</label>
            <input type="date" name="admission_date" id="admission_date" required>
            <label for="name">Name*</label>
            <input type="text" name="name" id="name" required>
            <label for="father_name">Father Name*</label>
            <input type="text" name="father_name" id="father_name" required>
            <label for="dob">Date of Birth*</label>
            <input type="date" name="dob" id="dob" required>
            <label for="address">Address</label>
            <input type="text" name="address" id="address">
            <label for="class">Class*</label>
            <input type="text" name="class" id="class" required>
            <label for="religion">Religion</label>
            <input type="text" name="religion" id="religion">
            <button type="submit">Register Student</button>
        </form>
    </div>
</body>
</html> 