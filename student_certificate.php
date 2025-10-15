<?php
require_once 'includes/auth.php';
if (!is_admin_logged_in()) {
    header('Location: index.php');
    exit;
}
require_once 'includes/db.php';
require_once 'includes/functions.php';
$license_type = getLicenseType();

$success = '';
$error = '';
$student = null;
$certificate = null;

// Fetch students for dropdown
$students = [];
$res = $conn->query('SELECT id, student_id, name, class FROM students ORDER BY name');
while ($row = $res->fetch_assoc()) {
    $students[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    $student_id = $_POST['student_id'] ?? '';
    $class = trim($_POST['class'] ?? '');
    $conduct = trim($_POST['conduct'] ?? '');
    $remarks = trim($_POST['remarks'] ?? '');
    $leaving_class = trim($_POST['leaving_class'] ?? '');
    $leaving_date = $_POST['leaving_date'] ?? '';
    $prepared_by = trim($_POST['prepared_by'] ?? '');

    // Fetch student details
    $stmt = $conn->prepare('SELECT * FROM students WHERE student_id = ? LIMIT 1');
    $stmt->bind_param('s', $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();

    if ($student && $class && $conduct && $leaving_date && $leaving_class) {
        // Save certificate
        $stmt2 = $conn->prepare('INSERT INTO student_certificates (student_id, class, conduct, remarks, leaving_date, prepared_by) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt2->bind_param('ssssss', $student_id, $class, $conduct, $remarks, $leaving_date, $prepared_by);
        if ($stmt2->execute()) {
            $success = 'Certificate generated below. Print or export as needed.';
            $certificate = [
                'student' => $student,
                'class' => $class,
                'conduct' => $conduct,
                'remarks' => $remarks,
                'leaving_class' => $leaving_class,
                'leaving_date' => $leaving_date,
                'prepared_by' => $prepared_by
            ];
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
    <title>Student Leaving Certificate</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .print-area { background: #fff; padding: 2em; margin: 2em auto; max-width: 600px; border: 1px solid #ccc; border-radius: 8px; }
        .school-logo { width: 80px; }
        .certificate-title { font-size: 1.5em; font-weight: bold; margin: 1em 0; }
        .signature-area { margin-top: 2em; }
        @media print {
            body * { visibility: hidden; }
            .print-area, .print-area * { visibility: visible; }
            .print-area { position: absolute; left: 0; top: 0; width: 100%; }
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="form-container">
        <h2>Student Leaving Certificate</h2>
        <?php if ($success): ?>
            <div class="success-msg"><?php echo $success; ?></div>
        <?php elseif ($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data" autocomplete="off">
            <label for="student_id">Select Student*</label>
            <select name="student_id" id="student_id" required>
                <option value="">-- Select --</option>
                <?php foreach ($students as $s): ?>
                    <option value="<?php echo htmlspecialchars($s['student_id']); ?>" <?php if (!empty($_POST['student_id']) && $_POST['student_id'] == $s['student_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($s['name'] . ' (' . $s['student_id'] . ', Class ' . $s['class'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label for="class">Class*</label>
            <input type="text" name="class" id="class" value="<?php echo htmlspecialchars($_POST['class'] ?? ''); ?>" required>
            <label for="conduct">Conduct*</label>
            <input type="text" name="conduct" id="conduct" value="<?php echo htmlspecialchars($_POST['conduct'] ?? ''); ?>" required>
            <label for="remarks">Special Remarks</label>
            <input type="text" name="remarks" id="remarks" value="<?php echo htmlspecialchars($_POST['remarks'] ?? ''); ?>">
            <label for="leaving_class">Leaving Class*</label>
            <input type="text" name="leaving_class" id="leaving_class" value="<?php echo htmlspecialchars($_POST['leaving_class'] ?? ''); ?>" required>
            <label for="leaving_date">Leaving Date*</label>
            <input type="date" name="leaving_date" id="leaving_date" value="<?php echo htmlspecialchars($_POST['leaving_date'] ?? ''); ?>" required>
            <label for="prepared_by">Prepared By</label>
            <input type="text" name="prepared_by" id="prepared_by" value="<?php echo htmlspecialchars($_POST['prepared_by'] ?? ''); ?>">
            <label for="student_image">Upload Student Image</label>
<input type="file" name="student_image" id="student_image" accept="image/*">
<button type="submit">Generate Certificate</button>
        </form>
    </div>
    <?php if ($certificate): ?>
        <div class="nav-buttons" style="text-align:center; margin-bottom:1.2em;">
    <a href="generate_marksheet.php" class="nav-btn">Marksheet</a>
    <a href="student_certificate.php" class="nav-btn">Student Leaving Certificate</a>
    <a href="teacher_experience.php" class="nav-btn">Teacher Experience Certificate</a>
</div>
<div class="print-area classic-certificate watermark" id="printArea">
    <div class="print-area-content">
        <div style="text-align:center; margin-bottom:1.5em;">
        </div>
        <div class="print-area classic-certificate watermark" id="printArea">
            <div class="print-area-content">
                <div style="text-align:center; margin-bottom:1.5em;">
                    <img src="assets/img/logo.png" alt="School Logo" style="width:100px; margin-bottom:0.5em;">
                    <div style="font-size:1.6em; font-weight:700; letter-spacing:2px; text-transform:uppercase; color:#1a237e; margin-bottom:0.3em;">
                        Learning Kids School System
                    </div>
                    <div style="font-size:1.15em; font-weight:600; margin-bottom:0.7em;">STUDENT LEAVING CERTIFICATE</div>
                </div>
                <div style="padding:2em 1.5em; background:#fff;">
                    <div style="margin-bottom:1.2em; font-size:1.1em;">
                        This is to certify that <span class="uline"><b><?php echo htmlspecialchars($certificate['student']['name']); ?></b></span>,
                        son/daughter of <span class="uline"><b><?php echo htmlspecialchars($certificate['student']['father_name']); ?></b></span>,
                        has been a bonafide student of this institution, enrolled in class <span class="uline"><b><?php echo htmlspecialchars($certificate['class']); ?></b></span>.
                        During his/her tenure at this school, he/she has maintained a satisfactory record in both academics and conduct. The student is leaving the school due to personal/family reasons and has cleared all dues and obligations to the institution. We wish him/her success in all future endeavors.
                    </div>
                    <div style="margin-bottom:0.7em;">
                        <b>Conduct:</b> <span class="uline"> <?php echo htmlspecialchars($certificate['conduct']); ?> </span>
                    </div>
                    <div style="margin-bottom:0.7em;">
                        <b>Special Remarks:</b> <span class="uline"> <?php echo htmlspecialchars($certificate['remarks']); ?> </span>
                    </div>
                    <div style="margin-bottom:0.7em;">
                        <b>Date of Birth:</b> <span class="uline"> <?php echo htmlspecialchars($certificate['student']['dob']); ?> </span>
                    </div>
                    <div style="margin-bottom:0.7em;">
                        <b>Admission Date:</b> <span class="uline"> <?php echo htmlspecialchars($certificate['student']['admission_date']); ?> </span>
                    </div>
                    <div style="margin-bottom:0.7em;">
                        <b>Leaving Class:</b> <span class="uline"> <?php echo htmlspecialchars($certificate['leaving_class']); ?> </span>
                    </div>
                    <div style="margin-bottom:0.7em;">
                        <b>Leaving Date:</b> <span class="uline"> <?php echo htmlspecialchars($certificate['leaving_date']); ?> </span>
                    </div>
                    <?php if (!empty($student_image_url)): ?>
                        <img src="<?php echo htmlspecialchars($student_image_url); ?>" alt="Student Image" style="width:70px; height:70px; object-fit:cover; border-radius:50%; border:2px solid #eee;">
                    <?php endif; ?>
                    <div style="text-align:right; width:60%;">
                        <div style="font-size:1em; margin-bottom:1.2em;">Prepared By: <span class="uline"> <?php echo htmlspecialchars($certificate['prepared_by']); ?> </span></div>
                        <div style="font-size:1em;">Principal Signature: <span class="uline">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
.classic-certificate {
    background: #fafbfc;
    border-radius: 16px;
    box-shadow: 0 4px 24px #0002;
    padding: 2.5em 2em;
    margin: 2em auto;
    max-width: 780px;
    border: 1.5px solid #bbb;
    font-family: 'Segoe UI', Arial, sans-serif;
}
.uline {
    display: inline-block;
    border-bottom: 1.5px solid #222;
    min-width: 120px;
    padding: 0 0.3em;
    margin: 0 0.2em;
}
</style>
            <div style="display:flex;justify-content:space-between;align-items:center;">
  <img src="assets/img/logo.png" alt="School Logo" class="school-logo">
  <?php if (!empty($student_image_url)): ?>
    <img src="<?php echo htmlspecialchars($student_image_url); ?>" alt="Student Image" style="width:80px;object-fit:cover;border-radius:8px;">
  <?php endif; ?>
</div>
            <div class="certificate-title">Student Leaving Certificate</div>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($certificate['student']['name']); ?></p>
            <p><strong>Father Name:</strong> <?php echo htmlspecialchars($certificate['student']['father_name']); ?></p>
            <p><strong>Class:</strong> <?php echo htmlspecialchars($certificate['class']); ?></p>
            <p><strong>Conduct:</strong> <?php echo htmlspecialchars($certificate['conduct']); ?></p>
            <p><strong>Special Remarks:</strong> <?php echo htmlspecialchars($certificate['remarks']); ?></p>
            <p><strong>Leaving Date:</strong> <?php echo htmlspecialchars($certificate['leaving_date']); ?></p>
        </div>
        <div class="certificate-title">Student Leaving Certificate</div>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($certificate['student']['name']); ?></p>
        <p><strong>Father Name:</strong> <?php echo htmlspecialchars($certificate['student']['father_name']); ?></p>
        <p><strong>Class:</strong> <?php echo htmlspecialchars($certificate['class']); ?></p>
        <p><strong>Conduct:</strong> <?php echo htmlspecialchars($certificate['conduct']); ?></p>
        <p><strong>Special Remarks:</strong> <?php echo htmlspecialchars($certificate['remarks']); ?></p>
        <p><strong>Leaving Date:</strong> <?php echo htmlspecialchars($certificate['leaving_date']); ?></p>
        <div class="signature-area">
            <p>Prepared By: <?php echo htmlspecialchars($certificate['prepared_by']); ?></p>
            <p>Signature: ________________________</p>
        </div>
    </div>
    <div style="text-align:center; margin:2em; display:flex; gap:1em; justify-content:center; align-items:center;">
        <button onclick="window.print()">Print Certificate</button>
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
                        link.download = 'student-certificate.png';
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