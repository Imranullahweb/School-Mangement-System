
require_once '../includes/check_pro.php';
require_once '../includes/db.php';

// MPDF autoload (adjust path if needed)
require_once __DIR__ . '/../vendor/autoload.php';

use Mpdf\Mpdf;

// Only allow export if certificate_id is provided
if (!isset($_GET['type']) || !isset($_GET['id'])) {
    die('Invalid request.');
}
$type = $_GET['type'];
$id = intval($_GET['id']);

if ($type === 'student_certificate') {
    // Fetch certificate and student data
    $stmt = $conn->prepare('SELECT sc.*, s.name, s.father_name, s.class as student_class FROM student_certificates sc JOIN students s ON sc.student_id = s.student_id WHERE sc.id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cert = $result->fetch_assoc();
    if (!$cert) die('Certificate not found.');

    $html = '<div style="text-align:center;">'
        . '<img src="../assets/img/logo.png" style="width:80px;"><br>'
        . '<h2 style="margin:0.5em 0;">Student Leaving Certificate</h2>'
        . '</div>'
        . '<p><strong>Name:</strong> ' . htmlspecialchars($cert['name']) . '</p>'
        . '<p><strong>Father Name:</strong> ' . htmlspecialchars($cert['father_name']) . '</p>'
        . '<p><strong>Class:</strong> ' . htmlspecialchars($cert['class']) . '</p>'
        . '<p><strong>Conduct:</strong> ' . htmlspecialchars($cert['conduct']) . '</p>'
        . '<p><strong>Special Remarks:</strong> ' . htmlspecialchars($cert['remarks']) . '</p>'
        . '<p><strong>Leaving Date:</strong> ' . htmlspecialchars($cert['leaving_date']) . '</p>'
        . '<div style="margin-top:2em;"><strong>Prepared By:</strong> ' . htmlspecialchars($cert['prepared_by']) . '<br><strong>Signature: ________________________</strong></div>';

    $mpdf = new Mpdf();
    $mpdf->WriteHTML($html);
    $mpdf->Output('student_certificate.pdf', 'I');
    exit;
}

if ($type === 'teacher_experience') {
    // Fetch certificate and teacher data
    $stmt = $conn->prepare('SELECT te.*, t.name, t.designation, t.appointment_date, t.leaving_date FROM teacher_experience te JOIN teachers t ON te.teacher_id = t.teacher_id WHERE te.id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cert = $result->fetch_assoc();
    if (!$cert) die('Certificate not found.');

    $html = '<div style="text-align:center;">'
        . '<img src="../assets/img/logo.png" style="width:80px;"><br>'
        . '<h2 style="margin:0.5em 0;">Teacher Experience Certificate</h2>'
        . '</div>'
        . '<p><strong>Name:</strong> ' . htmlspecialchars($cert['name']) . '</p>'
        . '<p><strong>Designation:</strong> ' . htmlspecialchars($cert['designation']) . '</p>'
        . '<p><strong>Appointment Date:</strong> ' . htmlspecialchars($cert['appointment_date']) . '</p>'
        . '<p><strong>Leaving Date:</strong> ' . htmlspecialchars($cert['leaving_date']) . '</p>'
        . '<p><strong>Message:</strong> ' . nl2br(htmlspecialchars($cert['message'])) . '</p>'
        . '<div style="margin-top:2em;"><strong>Signature: ________________________</strong></div>';

    $mpdf = new Mpdf();
    $mpdf->WriteHTML($html);
    $mpdf->Output('teacher_experience_certificate.pdf', 'I');
    exit;
}

if ($type === 'fee_report') {
    // Optional filters
    $filter_student = $_GET['student_id'] ?? '';
    $filter_class = $_GET['class'] ?? '';
    $where = [];
    $params = [];
    $types = '';
    if ($filter_student) {
        $where[] = 'student_id = ?';
        $params[] = $filter_student;
        $types .= 's';
    }
    if ($filter_class) {
        $where[] = 'class = ?';
        $params[] = $filter_class;
        $types .= 's';
    }
    $sql = 'SELECT * FROM student_fees';
    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' ORDER BY year DESC, month DESC';
    $stmt = $conn->prepare($sql);
    if ($where) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $fees = [];
    while ($row = $result->fetch_assoc()) {
        $fees[] = $row;
    }
    if (!$fees) die('No fee records found.');

    $html = '<div style="text-align:center;">'
        . '<img src="../assets/img/logo.png" style="width:80px;"><br>'
        . '<h2 style="margin:0.5em 0;">Student Fee Report</h2>'
        . '</div>'
        . '<table border="1" cellpadding="5" cellspacing="0" width="100%">'
        . '<tr><th>Student ID</th><th>Class</th><th>Month</th><th>Year</th><th>Amount</th><th>Status</th><th>Paid Date</th></tr>';
    foreach ($fees as $fee) {
        $html .= '<tr>'
            . '<td>' . htmlspecialchars($fee['student_id']) . '</td>'
            . '<td>' . htmlspecialchars($fee['class']) . '</td>'
            . '<td>' . htmlspecialchars($fee['month']) . '</td>'
            . '<td>' . htmlspecialchars($fee['year']) . '</td>'
            . '<td>' . htmlspecialchars($fee['amount']) . '</td>'
            . '<td>' . htmlspecialchars($fee['status']) . '</td>'
            . '<td>' . htmlspecialchars($fee['paid_date']) . '</td>'
            . '</tr>';
    }
    $html .= '</table>';
    $html .= '<div style="margin-top:2em;"><strong>Principal Signature: ________________________</strong></div>';

    $mpdf = new Mpdf();
    $mpdf->WriteHTML($html);
    $mpdf->Output('fee_report.pdf', 'I');
    exit;
}

if ($type === 'marksheet') {
    // Fetch marksheet and student data
    $stmt = $conn->prepare('SELECT m.*, s.name, s.father_name, s.class as student_class FROM marksheets m JOIN students s ON m.student_id = s.student_id WHERE m.id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $marksheet = $result->fetch_assoc();
    if (!$marksheet) die('Marksheet not found.');
    // Fetch subjects
    $stmt2 = $conn->prepare('SELECT subject_name, total_marks, obtained_marks FROM marksheet_subjects WHERE marksheet_id = ?');
    $stmt2->bind_param('i', $id);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $subjects = [];
    while ($row = $result2->fetch_assoc()) {
        $subjects[] = $row;
    }
    $html = '<div style="text-align:center;">'
        . '<img src="../assets/img/logo.png" style="width:80px;"><br>'
        . '<h2 style="margin:0.5em 0;">Marksheet (DMC)</h2>'
        . '</div>'
        . '<p><strong>Name:</strong> ' . htmlspecialchars($marksheet['name']) . '</p>'
        . '<p><strong>Father Name:</strong> ' . htmlspecialchars($marksheet['father_name']) . '</p>'
        . '<p><strong>Class:</strong> ' . htmlspecialchars($marksheet['student_class']) . '</p>'
        . '<p><strong>Exam:</strong> ' . htmlspecialchars($marksheet['exam_name']) . '</p>'
        . '<table border="1" cellpadding="5" cellspacing="0" width="100%">'
        . '<tr><th>Subject</th><th>Total Marks</th><th>Obtained Marks</th></tr>';
    foreach ($subjects as $sub) {
        $html .= '<tr>'
            . '<td>' . htmlspecialchars($sub['subject_name']) . '</td>'
            . '<td>' . htmlspecialchars($sub['total_marks']) . '</td>'
            . '<td>' . htmlspecialchars($sub['obtained_marks']) . '</td>'
            . '</tr>';
    }
    $html .= '<tr>'
        . '<th>Total</th>'
        . '<th>' . htmlspecialchars($marksheet['total_marks']) . '</th>'
        . '<th>' . htmlspecialchars($marksheet['obtained_marks']) . '</th>'
        . '</tr>';
    $html .= '</table>';
    $html .= '<p><strong>Percentage:</strong> ' . htmlspecialchars($marksheet['percentage']) . '%</p>';
    $html .= '<p><strong>Grade:</strong> ' . htmlspecialchars($marksheet['grade']) . '</p>';
    $html .= '<div style="margin-top:2em;"><strong>Signature: ________________________</strong></div>';

    $mpdf = new Mpdf();
    $mpdf->WriteHTML($html);
    $mpdf->Output('marksheet.pdf', 'I');
    exit;
}

if ($type === 'admit_card') {
    $student_id = $_GET['student_id'] ?? '';
    $exam_name = $_GET['exam_name'] ?? '';
    if (!$student_id || !$exam_name) die('Missing parameters.');
    // Fetch student
    $stmt = $conn->prepare('SELECT * FROM students WHERE student_id = ? LIMIT 1');
    $stmt->bind_param('s', $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
    // Fetch photo
    $photo = '';
    $stmt2 = $conn->prepare('SELECT photo_path FROM student_photos WHERE student_id = ? LIMIT 1');
    $stmt2->bind_param('s', $student_id);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    if ($row = $result2->fetch_assoc()) {
        $photo = $row['photo_path'];
    }
    // Fetch exam schedule for this class and exam
    $stmt3 = $conn->prepare('SELECT * FROM exam_schedule WHERE exam_name = ? AND class = ?');
    $stmt3->bind_param('ss', $exam_name, $student['class']);
    $stmt3->execute();
    $result3 = $stmt3->get_result();
    $subjects = [];
    while ($row3 = $result3->fetch_assoc()) {
        $subjects[] = $row3;
    }
    if (!$student || !$subjects) die('No exam schedule found for this student.');
    $html = '<div style="text-align:center;">'
        . '<img src="../assets/img/logo.png" style="width:80px; float:left;">';
    if ($photo) {
        $html .= '<img src="' . htmlspecialchars($photo) . '" style="width:80px; height:80px; object-fit:cover; border-radius:8px; border:1px solid #aaa; float:right;">';
    }
    $html .= '<div style="clear:both;"></div>'
        . '<h2 style="margin:0.5em 0;">Admit Card</h2>'
        . '</div>'
        . '<p><strong>Name:</strong> ' . htmlspecialchars($student['name']) . '</p>'
        . '<p><strong>Class:</strong> ' . htmlspecialchars($student['class']) . '</p>'
        . '<p><strong>Exam:</strong> ' . htmlspecialchars($exam_name) . '</p>'
        . '<table border="1" cellpadding="5" cellspacing="0" width="100%">'
        . '<tr><th>Subject</th><th>Room</th><th>Date</th><th>Time</th></tr>';
    foreach ($subjects as $sub) {
        $html .= '<tr>'
            . '<td>' . htmlspecialchars($sub['subject']) . '</td>'
            . '<td>' . htmlspecialchars($sub['room']) . '</td>'
            . '<td>' . htmlspecialchars($sub['exam_date']) . '</td>'
            . '<td>' . htmlspecialchars($sub['start_time']) . ' - ' . htmlspecialchars($sub['end_time']) . '</td>'
            . '</tr>';
    }
    $html .= '</table>';
    $html .= '<div style="margin-top:2em;"><strong>Signature: ________________________</strong></div>';

    $mpdf = new Mpdf();
    $mpdf->WriteHTML($html);
    $mpdf->Output('admit_card.pdf', 'I');
    exit;
}

// Add similar logic for other types (teacher_experience, fee_report, marksheet) as needed.

die('Invalid export type.'); 