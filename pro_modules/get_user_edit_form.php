<?php
require_once '../includes/check_pro.php';
require_once '../includes/db.php';

// Check if required parameters are provided
if (!isset($_GET['type']) || !isset($_GET['id'])) {
    die('Missing required parameters');
}

$type = $_GET['type'];
$id = $_GET['id'];
$user = null;

// Fetch user data based on type
if ($type === 'teacher') {
    $stmt = $conn->prepare('SELECT * FROM teachers WHERE teacher_id = ?');
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user) {
        ?>
        <div class="form-group">
            <label for="name">Teacher Name</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="father_name">Father/Husband Name</label>
            <input type="text" class="form-control" id="father_name" name="father_name" value="<?php echo htmlspecialchars($user['father_name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="designation">Designation</label>
            <input type="text" class="form-control" id="designation" name="designation" value="<?php echo htmlspecialchars($user['designation']); ?>" required>
        </div>
        <div class="form-group">
            <label for="address">Address</label>
            <textarea class="form-control" id="address" name="address" rows="2" required><?php echo htmlspecialchars($user['address']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="phone">Contact Number</label>
            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
        </div>
        <?php
    }
} else {
    // Student
    $stmt = $conn->prepare('SELECT * FROM students WHERE student_id = ?');
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user) {
        ?>
        <div class="form-group">
            <label for="name">Student Name</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="father_name">Father's Name</label>
            <input type="text" class="form-control" id="father_name" name="father_name" value="<?php echo htmlspecialchars($user['father_name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="class">Class</label>
            <input type="text" class="form-control" id="class" name="class" value="<?php echo htmlspecialchars($user['class']); ?>" required>
        </div>
        <div class="form-group">
            <label for="dob">Date of Birth</label>
            <input type="date" class="form-control" id="dob" name="dob" value="<?php echo htmlspecialchars($user['dob']); ?>" required>
        </div>
        <div class="form-group">
            <label for="address">Address</label>
            <textarea class="form-control" id="address" name="address" rows="2" required><?php echo htmlspecialchars($user['address']); ?></textarea>
        </div>
        <style>
            .form-group {
                margin-bottom: 1.2em;
            }
            .form-group label {
                display: block;
                margin-bottom: 0.4em;
                font-weight: 500;
                color: #333;
            }
            .form-control {
                width: 100%;
                padding: 0.6em 0.8em;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 1em;
                transition: border-color 0.2s;
            }
            .form-control:focus {
                border-color: #3498db;
                outline: none;
                box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
            }
            textarea.form-control {
                min-height: 80px;
                resize: vertical;
            }
        </style>
        <?php
    }
}

if (!$user) {
    echo '<p>User not found.</p>';
}
?>
