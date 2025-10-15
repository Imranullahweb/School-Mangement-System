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
$teacher = null;
$certificate = null;

// Fetch teachers for dropdown
$teachers = [];
$res = $conn->query('SELECT id, teacher_id, name, designation, appointment_date, leaving_date FROM teachers ORDER BY name');
while ($row = $res->fetch_assoc()) {
    $teachers[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle optional teacher image upload
    $teacher_image_url = '';
    if (isset($_FILES['teacher_image']) && $_FILES['teacher_image']['error'] === UPLOAD_ERR_OK) {
        $imageTmpPath = $_FILES['teacher_image']['tmp_name'];
        $imageName = basename($_FILES['teacher_image']['name']);
        $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($imageExt, $allowedExt)) {
            $newImageName = 'teacher_' . uniqid() . '.' . $imageExt;
            $uploadDir = 'assets/uploads/';
            $destPath = $uploadDir . $newImageName;
            if (move_uploaded_file($imageTmpPath, $destPath)) {
                $teacher_image_url = $destPath;
            }
        }
    }

    $teacher_id = $_POST['teacher_id'] ?? '';
    $message = trim($_POST['message'] ?? '');
    $leaving_date = $_POST['leaving_date'] ?? '';
    $prepared_by = trim($_POST['prepared_by'] ?? '');

    // Fetch teacher details
    $stmt = $conn->prepare('SELECT * FROM teachers WHERE teacher_id = ? LIMIT 1');
    $stmt->bind_param('s', $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $teacher = $result->fetch_assoc();

    if ($teacher && $leaving_date) {
        // Update leaving date if not set
        if (empty($teacher['leaving_date'])) {
            $stmt2 = $conn->prepare('UPDATE teachers SET leaving_date = ? WHERE teacher_id = ?');
            $stmt2->bind_param('ss', $leaving_date, $teacher_id);
            $stmt2->execute();
        }
        // Save certificate
        $stmt3 = $conn->prepare('INSERT INTO teacher_experience (teacher_id, message) VALUES (?, ?)');
        $stmt3->bind_param('ss', $teacher_id, $message);
        if ($stmt3->execute()) {
            $success = 'Experience certificate generated below. Print or export as needed.';
            $certificate = [
                'teacher' => $teacher,
                'message' => $message,
                'leaving_date' => $leaving_date,
                'prepared_by' => $prepared_by
            ];
        } else {
            $error = 'Database error. Please try again.';
        }
    } else {
        $error = 'Please select a teacher and provide leaving date.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Experience Certificate</title>
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
        <h2>Teacher Experience Certificate</h2>
        <?php if ($success): ?>
            <div class="success-msg"><?php echo $success; ?></div>
        <?php elseif ($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data" autocomplete="off">
            <label for="teacher_id">Select Teacher*</label>
            <select name="teacher_id" id="teacher_id" required>
                <option value="">-- Select --</option>
                <?php foreach ($teachers as $t): ?>
                    <option value="<?php echo htmlspecialchars($t['teacher_id']); ?>" <?php if (!empty($_POST['teacher_id']) && $_POST['teacher_id'] == $t['teacher_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($t['name'] . ' (' . $t['teacher_id'] . ', ' . $t['designation'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label for="leaving_date">Leaving Date*</label>
            <input type="date" name="leaving_date" id="leaving_date" value="<?php echo htmlspecialchars($_POST['leaving_date'] ?? ''); ?>" required>
            <label for="message">Custom Message</label>
            <textarea name="message" id="message" rows="3"><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
            <label for="prepared_by">Prepared By</label>
            <input type="text" name="prepared_by" id="prepared_by" value="<?php echo htmlspecialchars($_POST['prepared_by'] ?? ''); ?>">
            <label for="teacher_image">Upload Teacher Image</label>
<input type="file" name="teacher_image" id="teacher_image" accept="image/*">
<button type="submit">Generate Certificate</button>
        </form>
    </div>
    <?php if ($certificate): ?>
        <div class="nav-buttons" style="text-align:center; margin-bottom:1.2em;">
            <a href="index.php" class="nav-btn">Home</a>
            <a href="generate_marksheet.php" class="nav-btn">Marksheet</a>
            <a href="student_certificate.php" class="nav-btn">Student Leaving Certificate</a>
            <a href="teacher_experience.php" class="nav-btn">Teacher Experience Certificate</a>
        </div>
        <div class="print-area exp-template watermark" id="printArea" style="position:relative; background:#fff; box-shadow:0 4px 24px #0002; padding:2.2em 1.5em; max-width:1200px; margin:2em auto; font-family:'Merriweather','Roboto Slab',serif; overflow:hidden;">
  <!-- Decorative SVG corners -->
  <img src="assets/img/flourish-gold.svg" alt="" class="decor-flourish" style="position:absolute;top:18px;left:50%;transform:translateX(-50%);z-index:2;pointer-events:none;" width="180" height="30">
  <img src="assets/img/flourish-gold.svg" alt="" class="decor-flourish" style="position:absolute;bottom:18px;left:50%;transform:translateX(-50%) scaleY(-1);z-index:2;pointer-events:none;" width="180" height="30">
  <svg width="360" height="360" viewBox="0 0 360 360" style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);opacity:0.07;z-index:1;pointer-events:none;"><circle cx="180" cy="180" r="170" stroke="#b97a3c" stroke-width="14" fill="none"/><text x="50%" y="54%" text-anchor="middle" font-size="52" fill="#b97a3c" font-family='Merriweather,serif' font-weight="bold" opacity="0.22">EXPERIENCE</text></svg>
  <svg width="64" height="64" style="position:absolute;top:0;left:0;" viewBox="0 0 64 64"><path d="M2,62 Q2,2 62,2" stroke="#b97a3c" stroke-width="3" fill="none"/><circle cx="8" cy="8" r="3" fill="#b97a3c"/></svg>
  <svg width="64" height="64" style="position:absolute;top:0;right:0;transform:scaleX(-1);" viewBox="0 0 64 64"><path d="M2,62 Q2,2 62,2" stroke="#b97a3c" stroke-width="3" fill="none"/><circle cx="8" cy="8" r="3" fill="#b97a3c"/></svg>
  <svg width="64" height="64" style="position:absolute;bottom:0;left:0;transform:scaleY(-1);" viewBox="0 0 64 64"><path d="M2,62 Q2,2 62,2" stroke="#b97a3c" stroke-width="3" fill="none"/><circle cx="8" cy="8" r="3" fill="#b97a3c"/></svg>
  <svg width="64" height="64" style="position:absolute;bottom:0;right:0;transform:scale(-1,-1);" viewBox="0 0 64 64"><path d="M2,62 Q2,2 62,2" stroke="#b97a3c" stroke-width="3" fill="none"/><circle cx="8" cy="8" r="3" fill="#b97a3c"/></svg>
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <img src="assets/img/logo.png" alt="School Logo" style="width:70px; margin-top:0.2em;">
                <?php if (!empty($teacher_image_url)): ?>
                    <img src="<?php echo htmlspecialchars($teacher_image_url); ?>" alt="Teacher Image" style="width:90px; height:90px; object-fit:cover; border-radius:12px; border:3px solid #b97a3c; margin-top:0.2em;">
                <?php endif; ?>
            </div>
            <div style="border:2px solid #b97a3c; border-radius:12px; padding:2.2em 1.5em 1.5em 1.5em; margin-top:1.2em; background:rgba(255,255,255,0.98); position:relative;">
                <div style="font-family:'Merriweather',serif; font-size:2em; font-weight:800; color:#b97a3c; letter-spacing:1px; margin-bottom:0.3em;">CERTIFICATE<br>OF EXPERIENCE</div>
                <div style="font-size:1.1em; color:#444; margin-bottom:1.1em;">This certificate is presented to</div>
                <div style="font-family:'Pacifico',cursive; font-size:2.1em; color:#a94442; font-weight:700; margin-bottom:0.5em; letter-spacing:1px;">
                    <?php echo htmlspecialchars($certificate['teacher']['name']); ?>
                </div>
                <div style="font-size:1.08em; color:#333; margin-bottom:1em; border-bottom:1px solid #b97a3c; padding-bottom:0.6em;">
                    in recognition of their valuable service as <b><?php echo htmlspecialchars($certificate['teacher']['designation']); ?></b> at this institution.<br>
                    Appointment Date: <b><?php echo htmlspecialchars($certificate['teacher']['appointment_date']); ?></b><br>
                    Leaving Date: <b><?php echo htmlspecialchars($certificate['leaving_date']); ?></b>
                </div>
                <div style="font-size:1em; color:#555; margin-bottom:1.2em;">
                    <?php
    $msg = trim($certificate['message']);
    if ($msg === '') {
        $msg = '<div style="text-align:center; font-size:1.1em; margin-bottom:1.3em; font-family:serif;">
            <div style="font-size:1.05em; margin-bottom:0.5em;"><b>Date:</b> ' . date('d-m-Y') . '</div>
            <div style="font-size:1.5em; font-weight:800; letter-spacing:1px; margin-bottom:0.7em;">TO WHOM IT MAY CONCERN</div>
        </div>' .
        '<div style="font-size:1.18em; line-height:1.7; text-align:justify; font-family:serif;">
            This is to certify that <b>Mr./Mrs. ' . htmlspecialchars($certificate['teacher']['name']) . '</b>, son/daughter of <b>' . htmlspecialchars($certificate['teacher']['father_name']) . '</b>, has worked as <b>' . htmlspecialchars($certificate['teacher']['designation']) . '</b> in <b>Learning Kids School System Chataly Adda Sharif Abad</b> from <b>' . htmlspecialchars($certificate['teacher']['appointment_date']) . '</b> to <b>' . htmlspecialchars($certificate['leaving_date']) . '</b>.<br><br>
            The management found him/her responsible, enthusiastic and hardworking during his/her work tenure. He/she can prove to be an asset for any organization. The management wishes him/her success in his/her future endeavors.<br><br>
            This certificate is being issued on the request of Mr./Mrs. <b>' . htmlspecialchars($certificate['teacher']['name']) . '</b>.' .
        '</div>';
    }
    echo $msg;
?>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:end; margin-top:2em;">
                    <div style="font-size:1em;"><b>Prepared By:</b> <?php echo htmlspecialchars($certificate['prepared_by']); ?></div>
                    <div style="text-align:right; font-size:1em;"><b>Signature:</b> <span style="display:inline-block; border-bottom:1.5px solid #222; min-width:180px; margin-left:0.8em;"></span></div>
                </div>
            </div>
        </div>
        <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
<!-- Add gold flourish SVG asset in assets/img/flourish-gold.svg or use a suitable flourish image -->
        <div style="text-align:center; margin:2em; display:flex; gap:1em; justify-content:center; align-items:center;">
            <button onclick="window.print()">Print Experience Certificate</button>
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
                        link.download = 'teacher-experience-certificate.png';
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