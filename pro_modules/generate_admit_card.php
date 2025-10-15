<?php
session_start();
require_once '../includes/check_pro.php';
require_once '../includes/db.php';

// Check if user has Pro license
$is_pro = isset($_SESSION['is_pro']) && $_SESSION['is_pro'] === true;

$student = null;
$error = '';
$uploaded_photo = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'] ?? '';
    // Fetch student
    $stmt = $conn->prepare('SELECT * FROM students WHERE student_id = ? LIMIT 1');
    $stmt->bind_param('s', $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
    // Handle optional image upload
    if (isset($_FILES['student_photo']) && $_FILES['student_photo']['error'] === UPLOAD_ERR_OK) {
        $imageTmpPath = $_FILES['student_photo']['tmp_name'];
        $imageName = basename($_FILES['student_photo']['name']);
        $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($imageExt, $allowedExt)) {
            $newImageName = 'student_' . uniqid() . '.' . $imageExt;
            $uploadDir = '../assets/uploads/';
            $destPath = $uploadDir . $newImageName;
            if (move_uploaded_file($imageTmpPath, $destPath)) {
                $uploaded_photo = $destPath;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admit Card Generator</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        .print-area { background: #fff; padding: 2em; margin: 2em auto; max-width: 700px; border: 1px solid #ccc; border-radius: 8px; }
        .school-logo { width: 80px; }
        .admit-photo { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 1px solid #aaa; }
        .admit-table { width: 100%; border-collapse: collapse; margin-top: 1em; }
        .admit-table th, .admit-table td { border: 1px solid #ccc; padding: 0.5em; text-align: center; }
        .admit-table th { background: #f5f5f5; }
        @media print {
            body * { visibility: hidden; }
            .print-area, .print-area * { visibility: visible; }
            .print-area { position: absolute; left: 0; top: 0; width: 100%; }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Student ID Card Generator</h2>
        <?php if ($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data" autocomplete="off">
            <label for="student_id">Enter Student ID*</label>
            <input type="text" name="student_id" id="student_id" required value="<?php echo isset($_POST['student_id']) ? htmlspecialchars($_POST['student_id']) : ''; ?>">
            <label for="contact">Contact Number*</label>
            <input type="text" name="contact" id="contact" required value="<?php echo isset($_POST['contact']) ? htmlspecialchars($_POST['contact']) : ''; ?>">
            <label for="student_photo">Upload Student Photo (optional)</label>
            <input type="file" name="student_photo" id="student_photo" accept="image/*">
            <button type="submit">Generate Student ID Card</button>
            
        </form>
    </div>
    <?php if ($student): ?>
    <div class="print-area idcard-area" id="printArea">
        <div class="idcard-bg">
            <div class="idcard-sidebar">STUDENT IDENTITY CARD</div>
            <div class="idcard-main">
                <div class="idcard-header">
                    <div class="idcard-institute">Learning Kids School System</div>
                </div>
                <div class="idcard-photo-wrap">
                    <?php 
                        $photo_src = $uploaded_photo;
                        if (!$photo_src && isset($student['photo_path'])) $photo_src = $student['photo_path'];
                    ?>
                    <img src="<?php echo $photo_src ? htmlspecialchars($photo_src) : '../assets/img/default-profile.png'; ?>" alt="Student Photo" class="idcard-photo">
                </div>
                <div class="idcard-name"><?php echo htmlspecialchars($student['name'] ?? ''); ?></div>
                <div class="idcard-program"><?php echo htmlspecialchars($student['class'] ?? ''); ?></div>
                <div class="idcard-fields">
                    <div><span>F. Name</span> <?php echo htmlspecialchars($student['father_name'] ?? ''); ?></div>
                    <div><span>Roll No.</span> <?php echo htmlspecialchars($student['student_id'] ?? ''); ?></div>
                    <div><span>Contact</span> <?php echo htmlspecialchars($_POST['contact'] ?? ($student['contact'] ?? '')); ?></div>
                    <div><span>D.O.B</span> <?php echo htmlspecialchars($student['dob'] ?? ''); ?></div>
                    <div><span>Add</span> <?php echo htmlspecialchars($student['address'] ?? ''); ?></div>
                </div>
            </div>
        </div>
    </div>
    <div style="text-align:center; margin:2em; display:flex; gap:1em; justify-content:center; align-items:center;">
        <button onclick="window.print()">Print Student ID Card</button>
        <?php 
        // Debug: Show current Pro status
        $license_type = getLicenseType();
        $is_pro = $license_type === 'pro';
        ?>
        <button id="savePngBtn" 
            <?php echo $is_pro ? '' : 'disabled'; ?> 
            style="<?php echo $is_pro ? 'background:#0090d0;color:#fff;' : 'background:#eee;color:#bbb;cursor:not-allowed;'; ?> position:relative;">
            Save as PNG 
            <span class="pro-badge" style="background:#b48e00;color:#fff;font-size:0.85em;margin-left:0.4em;">Pro</span>
            <?php if (!$is_pro): ?>
                <span style="margin-left:0.3em;">🔒</span>
            <?php endif; ?>
        </button>
    </div>
    <?php if (!$is_pro): ?>
    <div style="text-align:center; color:#d32f2f; margin-top:-1em; margin-bottom:1em;">
        Pro feature. Please activate your Pro license to use this feature.
    </div>
    <?php endif; ?>
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var btn = document.getElementById('savePngBtn');
        if (btn) {
            btn.addEventListener('click', async function() {
                var card = document.querySelector('.idcard-area');
                if (!card) {
                    alert('Could not find ID card content');
                    return;
                }
                
                // Show loading state
                var originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = 'Generating...';
                
                try {
                    // Create a clone of the card to avoid affecting the original
                    var clone = card.cloneNode(true);
                    clone.style.position = 'absolute';
                    clone.style.left = '-9999px';
                    document.body.appendChild(clone);
                    
                    // Add a small delay to ensure the clone is rendered
                    await new Promise(resolve => setTimeout(resolve, 100));
                    
                    // Use html2canvas to capture the card
                    const canvas = await html2canvas(clone, {
                        scale: 3,
                        backgroundColor: null,
                        logging: false,
                        useCORS: true,
                        allowTaint: true
                    });
                    
                    // Create download link
                    const link = document.createElement('a');
                    const studentId = '<?php echo isset($student['student_id']) ? $student['student_id'] : 'id-card'; ?>';
                    link.download = 'student-id-' + studentId + '.png';
                    link.href = canvas.toDataURL('image/png');
                    
                    // Trigger download
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    
                } catch (error) {
                    console.error('Error generating image:', error);
                    alert('Error generating image. Please try again.\n' + error.message);
                } finally {
                    // Clean up
                    if (clone && clone.parentNode) {
                        document.body.removeChild(clone);
                    }
                    
                    // Reset button
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            });
        }
    });
    </script>
<?php endif; ?>
<style>
.idcard-area {
    max-width: 350px;
    margin: 2em auto;
    padding: 0;
    background: transparent;
    border: none;
    box-shadow: none;
}
.idcard-bg {
    display: flex;
    flex-direction: row;
    border-radius: 16px;
    box-shadow: 0 2px 12px #bbb;
    background: #fff;
    overflow: hidden;
    border: 1.5px solid #0090d0;
    min-height: 430px;
}
.idcard-sidebar {
    writing-mode: vertical-rl;
    text-orientation: mixed;
    background: #0090d0;
    color: #fff;
    font-weight: bold;
    font-size: 1.15em;
    letter-spacing: 0.08em;
    padding: 24px 8px 24px 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Roboto Slab', serif;
}
.idcard-main {
    flex: 1;
    padding: 18px 18px 12px 18px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
}
.idcard-header {
    width: 100%;
    text-align: center;
    margin-bottom: 8px;
}
.idcard-institute {
    font-size: 1.05em;
    font-weight: bold;
    color: #0090d0;
    font-family: 'Roboto Slab', serif;
    line-height: 1.15em;
}
.idcard-photo-wrap {
    margin: 8px 0 10px 0;
    width: 98px; height: 98px;
    border-radius: 50%;
    border: 2.5px solid #0090d0;
    display: flex; align-items: center; justify-content: center;
    background: #f8fafc;
}
.idcard-photo {
    width: 90px; height: 90px;
    object-fit: cover;
    border-radius: 50%;
    border: none;
}
.idcard-name {
    font-size: 1.25em;
    font-weight: bold;
    color: #0090d0;
    margin: 6px 0 2px 0;
    font-family: 'Roboto Slab', serif;
}
.idcard-program {
    color: #fff;
    background: #0090d0;
    font-size: 1em;
    border-radius: 6px;
    padding: 2px 12px;
    margin-bottom: 8px;
    font-family: 'Roboto Slab', serif;
}
.idcard-fields {
    width: 100%;
    margin-top: 5px;
    font-size: 0.98em;
    color: #222;
    font-family: 'Roboto', sans-serif;
}
.idcard-fields div {
    margin-bottom: 4px;
    display: flex;
    justify-content: flex-start;
    align-items: baseline;
}
.idcard-fields span {
    min-width: 68px;
    color: #0090d0;
    font-weight: bold;
    font-family: 'Roboto Slab', serif;
    font-size: 0.97em;
    margin-right: 6px;
}
@media print {
    body * { visibility: hidden; }
    #printArea, #printArea * { visibility: visible; }
    #printArea { position: absolute; left: 0; top: 0; width: 350px; background: #fff; box-shadow: none; }
    .idcard-area { margin: 0; box-shadow: none; }
}
</style>
</body>
</html> 