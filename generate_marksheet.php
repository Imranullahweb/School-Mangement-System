<?php
require_once 'includes/auth.php';
if (!is_admin_logged_in()) {
    header('Location: index.php');
    exit;
}
require_once 'includes/db.php';

$success = '';
$error = '';
$student = null;
$marksheet = null;
$subjects = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fetch_student'])) {
    $student_id = trim($_POST['student_id'] ?? '');
    if ($student_id) {
        $stmt = $conn->prepare('SELECT * FROM students WHERE student_id = ? LIMIT 1');
        $stmt->bind_param('s', $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $student = $result->fetch_assoc();
        if (!$student) {
            $error = 'Student not found.';
        }
    } else {
        $error = 'Please enter a student ID.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_marksheet'])) {
    // Handle optional student image upload
    $student_image_url = '';
    if (isset($_FILES['student_image']) && $_FILES['student_image']['error'] === UPLOAD_ERR_OK) {
        $imageTmpPath = $_FILES['student_image']['tmp_name'];
        $imageName = basename($_FILES['student_image']['name']);
        $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($imageExt, $allowedExt)) {
            $newImageName = 'student_' . uniqid() . '.' . $imageExt;
            $uploadDir = 'assets/uploads/';
            $destPath = $uploadDir . $newImageName;
            if (move_uploaded_file($imageTmpPath, $destPath)) {
                $student_image_url = $destPath;
            }
        }
    }

    $student_id = trim($_POST['student_id'] ?? '');
    $exam_name = trim($_POST['exam_name'] ?? '');
    $roll_no = trim($_POST['roll_no'] ?? '');
    $reg_no = trim($_POST['reg_no'] ?? '');
    $institute = trim($_POST['institute'] ?? '');
    $subject_names = $_POST['subject_name'] ?? [];
    $total_marks_arr = $_POST['total_marks'] ?? [];
    $obtained_marks_arr = $_POST['obtained_marks'] ?? [];

    $total = 0;
    $obtained = 0;
    $subjects = [];
    for ($i = 0; $i < count($subject_names); $i++) {
        $subjects[] = [
            'name' => $subject_names[$i],
            'total' => intval($total_marks_arr[$i]),
            'obtained' => intval($obtained_marks_arr[$i])
        ];
        $total += intval($total_marks_arr[$i]);
        $obtained += intval($obtained_marks_arr[$i]);
    }
    $percentage = $total > 0 ? round(($obtained / $total) * 100, 2) : 0;
    if ($percentage >= 80) $grade = 'A+';
    elseif ($percentage >= 70) $grade = 'A';
    elseif ($percentage >= 60) $grade = 'B';
    elseif ($percentage >= 50) $grade = 'C';
    elseif ($percentage >= 40) $grade = 'D';
    else $grade = 'F';

    // Save marksheet
    $stmt = $conn->prepare('INSERT INTO marksheets (student_id, exam_name, total_marks, obtained_marks, percentage, grade) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('ssidds', $student_id, $exam_name, $total, $obtained, $percentage, $grade);
    if ($stmt->execute()) {
        $marksheet_id = $conn->insert_id;
        // Save subjects
        for ($i = 0; $i < count($subjects); $i++) {
            $stmt2 = $conn->prepare('INSERT INTO marksheet_subjects (marksheet_id, subject_name, total_marks, obtained_marks) VALUES (?, ?, ?, ?)');
            $stmt2->bind_param('isii', $marksheet_id, $subjects[$i]['name'], $subjects[$i]['total'], $subjects[$i]['obtained']);
            $stmt2->execute();
        }
        // Fetch student for display
        $stmt3 = $conn->prepare('SELECT * FROM students WHERE student_id = ? LIMIT 1');
        $stmt3->bind_param('s', $student_id);
        $stmt3->execute();
        $result3 = $stmt3->get_result();
        $student = $result3->fetch_assoc();
        $marksheet = [
            'exam_name' => $exam_name,
            'roll_no' => $roll_no,
            'reg_no' => $reg_no,
            'institute' => $institute,
            'subjects' => $subjects,
            'total' => $total,
            'obtained' => $obtained,
            'percentage' => $percentage,
            'grade' => $grade
        ];
        $success = 'Marksheet generated below. Print or export as needed.';
    } else {
        $error = 'Database error. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marksheet (DMC) Generation</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .marksheet-table { width: 100%; border-collapse: collapse; margin-top: 1em; }
        .marksheet-table th, .marksheet-table td { border: 1px solid #ccc; padding: 0.5em; text-align: center; }
        .marksheet-table th { background: #f5f5f5; }
        .print-area { background: #fff; padding: 2em; margin: 2em auto; max-width: 900px; border: 1px solid #ccc; border-radius: 8px; }
        @media print {
            body * { visibility: hidden; }
            .print-area, .print-area * { visibility: visible; }
            .print-area { position: absolute; left: 0; top: 0; width: 100%; }
        }
    </style>
    <script>
    function addSubjectRow() {
        const table = document.getElementById('subjectsTable');
        const row = table.insertRow(-1);
        row.innerHTML = `<td><input type="text" name="subject_name[]" required></td><td><input type="number" name="total_marks[]" required></td><td><input type="number" name="obtained_marks[]" required></td><td><button type="button" onclick="this.closest('tr').remove()">Remove</button></td>`;
    }
    </script>
</head>
<body>
    <div class="form-container">
        <h2>Marksheet (DMC) Generation</h2>
        <?php if ($success): ?>
            <div class="success-msg"><?php echo $success; ?></div>
        <?php elseif ($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <label for="student_id">Student ID*</label>
            <input type="text" name="student_id" id="student_id" value="<?php echo htmlspecialchars($_POST['student_id'] ?? ''); ?>" required>
            <button type="submit" name="fetch_student">Fetch Student</button>
        </form>
        <?php if ($student): ?>
            <form method="post" enctype="multipart/form-data" autocomplete="off">
                <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($student['student_id']); ?>">
                <label for="exam_name">Exam Name*</label>
                <input type="text" name="exam_name" id="exam_name" required>
                <label for="roll_no">Roll Number*</label>
                <input type="text" name="roll_no" id="roll_no" required>
                <label for="reg_no">Registration Number*</label>
                <input type="text" name="reg_no" id="reg_no" required>
                <label for="institute">Institute/College*</label>
                <input type="text" name="institute" id="institute" required>
                <table class="marksheet-table" id="subjectsTable">
                    <tr><th>Subject Name</th><th>Total Marks</th><th>Obtained Marks</th><th></th></tr>
                    <tr>
                        <td><input type="text" name="subject_name[]" required></td>
                        <td><input type="number" name="total_marks[]" required></td>
                        <td><input type="number" name="obtained_marks[]" required></td>
                        <td><button type="button" onclick="this.closest('tr').remove()">Remove</button></td>
                    </tr>
                </table>
                <button type="button" onclick="addSubjectRow()">Add Subject</button>
                <label for="student_image">Upload Student Image</label>
<input type="file" name="student_image" id="student_image" accept="image/*">
<button type="submit" name="generate_marksheet">Generate Marksheet</button>
            </form>
        <?php endif; ?>
    </div>
    <?php if ($marksheet && $student): ?>
        <div class="nav-buttons" style="text-align:center; margin-bottom:1.2em;">
    <a href="generate_marksheet.php" class="nav-btn">Marksheet</a>
    <a href="student_certificate.php" class="nav-btn">Student Leaving Certificate</a>
    <a href="teacher_experience.php" class="nav-btn">Teacher Experience Certificate</a>
</div>
<div class="print-area marksheet-official watermark" id="printArea">
  <div class="marksheet-official-content">
    <div style="text-align:center; margin-bottom:0.8em;">
      <img src="assets/img/logo.png" alt="School Logo" style="width:80px; margin-bottom:0.2em;">
      <div style="font-family:'Merriweather', 'Roboto Slab', serif; font-size:1.4em; font-weight:800; letter-spacing:1.2px; color:#1a237e; margin-bottom:0.15em; text-transform:uppercase;">
        <?php echo htmlspecialchars($marksheet['institute']); ?>
      </div>
      <div style="font-size:1em; font-weight:700; color:#444; margin-bottom:0.35em; letter-spacing:0.7px;">DETAIL MARKS CERTIFICATE</div>
      <div style="font-size:0.95em; font-weight:500; color:#555; margin-bottom:0.9em;">Session: <?php echo htmlspecialchars($marksheet['exam_name']); ?> | Year: <?php echo date('Y'); ?></div>
    </div>
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.9em;">
      <div style="flex:1;">
        <table style="width:100%; border:none; font-size:1.08em;">
          <tr><td style="padding:0.3em 0.5em;"><b>Name of Candidate:</b></td><td class="uline-cell"><?php echo htmlspecialchars($student['name']); ?></td></tr>
          <tr><td style="padding:0.3em 0.5em;"><b>Father's Name:</b></td><td class="uline-cell"><?php echo htmlspecialchars($student['father_name']); ?></td></tr>
          <tr><td style="padding:0.3em 0.5em;"><b>Class:</b></td><td class="uline-cell"><?php echo htmlspecialchars($student['class']); ?></td></tr>
          <tr><td style="padding:0.3em 0.5em;"><b>Roll No.:</b></td><td class="uline-cell"><?php echo htmlspecialchars($marksheet['roll_no']); ?></td></tr>
          <tr><td style="padding:0.3em 0.5em;"><b>Reg. No.:</b></td><td class="uline-cell"><?php echo htmlspecialchars($marksheet['reg_no']); ?></td></tr>
          <tr><td style="padding:0.3em 0.5em;"><b>Institute/College:</b></td><td class="uline-cell"><?php echo htmlspecialchars($marksheet['institute']); ?></td></tr>
        </table>
      </div>
      <?php if (!empty($student_image_url)): ?>
      <div style="margin-left:2em;">
        <img src="<?php echo htmlspecialchars($student_image_url); ?>" alt="Student Image" style="width:90px; height:110px; object-fit:cover; border-radius:6px; border:2px solid #ccc;">
      </div>
      <?php endif; ?>
    </div>
    <table class="marksheet-table-official" style="width:100%; border-collapse:collapse; margin-bottom:1.2em;">
      <tr style="background:#f3f6fa;">
        <th style="padding:0.6em; border:1px solid #bbb; text-align:center;">Subject</th>
        <th style="padding:0.6em; border:1px solid #bbb; text-align:center;">Total Marks</th>
        <th style="padding:0.6em; border:1px solid #bbb; text-align:center;">Obtained Marks</th>
      </tr>
      <?php foreach ($marksheet['subjects'] as $sub): ?>
      <tr>
        <td style="padding:0.6em; border:1px solid #bbb; text-align:center;"> <?php echo htmlspecialchars($sub['name']); ?> </td>
        <td style="padding:0.6em; border:1px solid #bbb; text-align:center;"> <?php echo htmlspecialchars($sub['total']); ?> </td>
        <td style="padding:0.6em; border:1px solid #bbb; text-align:center;"> <?php echo htmlspecialchars($sub['obtained']); ?> </td>
      </tr>
      <?php endforeach; ?>
      <tr style="background:#f3f6fa; font-weight:700;">
        <td style="padding:0.6em; border:1px solid #bbb; text-align:center;">Total</td>
        <td style="padding:0.6em; border:1px solid #bbb; text-align:center;"> <?php echo htmlspecialchars($marksheet['total']); ?> </td>
        <td style="padding:0.6em; border:1px solid #bbb; text-align:center;"> <?php echo htmlspecialchars($marksheet['obtained']); ?> </td>
      </tr>
    </table>
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.1em;">
      <div style="font-size:1.07em;"><b>Percentage:</b> <?php echo htmlspecialchars($marksheet['percentage']); ?>%<br><b>Grade:</b> <?php echo htmlspecialchars($marksheet['grade']); ?></div>
      <div style="font-size:1.07em;"><b>Total in Words:</b> <span class="uline-cell">-</span></div>
    </div>
    <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-top:2em;">
      <div style="font-size:1em;">
        <b>Prepared by:</b> <span class="uline-cell">-</span><br>
        <b>Checked by:</b> <span class="uline-cell">-</span>
      </div>
      <div style="text-align:right; font-size:1em;">
        <b>Signature:</b> <span style="display:inline-block; border-bottom:1.5px solid #222; min-width:180px; margin-left:0.8em;"></span>
      </div>
    </div>
  </div>
</div>
<style>
@import url('https://fonts.googleapis.com/css2?family=Merriweather:wght@400;700;900&family=Roboto+Slab:wght@400;700&display=swap');
.marksheet-official {
  background:#fff;
  border-radius:10px;
  box-shadow:0 4px 24px #0002;
  padding:2.2em 1.5em;
  margin:1.5em auto;
  max-width:950px;
  font-family:'Merriweather','Roboto Slab',serif;
  position:relative;
}
.marksheet-official-content {
  position:relative;
  z-index:2;
}
.marksheet-table-official th, .marksheet-table-official td {
  font-size:1.04em;
  font-family:'Roboto Slab',serif;
}
.uline-cell {
  border-bottom:1.2px solid #444;
  min-width:120px;
  display:inline-block;
  padding:0 0.2em;
  margin:0 0.1em;
}
</style>
            <div style="display:flex;justify-content:space-between;align-items:center;">
  <img src="assets/img/logo.png" alt="School Logo" style="width:80px;">
  <?php if (!empty($student_image_url)): ?>
    <img src="<?php echo htmlspecialchars($student_image_url); ?>" alt="Student Image" style="width:80px;object-fit:cover;border-radius:8px;">
  <?php endif; ?>
</div>
            <h3>Marksheet (DMC)</h3>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($student['name']); ?></p>
            <p><strong>Father Name:</strong> <?php echo htmlspecialchars($student['father_name']); ?></p>
            <p><strong>Class:</strong> <?php echo htmlspecialchars($student['class']); ?></p>
            <p><strong>Exam:</strong> <?php echo htmlspecialchars($marksheet['exam_name']); ?></p>
            <table class="marksheet-table">
                <tr><th>Subject</th><th>Total Marks</th><th>Obtained Marks</th></tr>
                <?php foreach ($marksheet['subjects'] as $sub): ?>
                <tr>
                    <td><?php echo htmlspecialchars($sub['name']); ?></td>
                    <td><?php echo htmlspecialchars($sub['total']); ?></td>
                    <td><?php echo htmlspecialchars($sub['obtained']); ?></td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <th>Total</th>
                    <th><?php echo htmlspecialchars($marksheet['total']); ?></th>
                    <th><?php echo htmlspecialchars($marksheet['obtained']); ?></th>
                </tr>
            </table>
            <p><strong>Percentage:</strong> <?php echo htmlspecialchars($marksheet['percentage']); ?>%</p>
            <p><strong>Grade:</strong> <?php echo htmlspecialchars($marksheet['grade']); ?></p>
            <div style="margin-top:2em;">
                <strong>Signature: ________________________</strong>
            </div>
        </div>
        <div style="text-align:center; margin:2em; display:flex; gap:1em; justify-content:center; align-items:center;">
            <button onclick="window.print()">Print Marksheet</button>
            <?php require_once 'includes/functions.php'; $license_type = getLicenseType(); ?>
            <?php if ($license_type === 'pro'): ?>
                <button id="savePngBtn" style="background:#0090d0;color:#fff;position:relative;">Save as PNG <span class="pro-badge" style="background:#b48e00;color:#fff;font-size:0.85em;margin-left:0.4em;">Pro</span></button>
            <?php else: ?>
                <button disabled title="Pro feature" style="background:#eee;color:#bbb;cursor:not-allowed;position:relative;">Save as PNG <span class="pro-badge" style="background:#b48e00;color:#fff;font-size:0.85em;margin-left:0.4em;">Pro</span> <span style="margin-left:0.3em;">🔒</span></button>
            <?php endif; ?>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var btn = document.getElementById('savePngBtn');
            if (btn) {
                btn.addEventListener('click', function() {
                    var card = document.getElementById('printArea');
                    if (!card) return;
                    html2canvas(card, {backgroundColor: '#fff', scale: 2}).then(function(canvas) {
                        var link = document.createElement('a');
                        link.download = 'marksheet.png';
                        link.href = canvas.toDataURL('image/png');
                        link.click();
                    });
                });
            }
        });
        </script>
    <?php endif; ?>
</body>
</html> 