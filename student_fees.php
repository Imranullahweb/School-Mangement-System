<?php
require_once 'includes/auth.php';
if (!is_admin_logged_in()) {
    header('Location: index.php');
    exit;
}
require_once 'includes/db.php';

$success = '';
$error = '';

// Fetch students for dropdown
$students = [];
$res = $conn->query('SELECT id, student_id, name, class FROM students ORDER BY name');
while ($row = $res->fetch_assoc()) {
    $students[] = $row;
}

// Add or update fee
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_fee'])) {
    $student_id = $_POST['student_id'] ?? '';
    $class = trim($_POST['class'] ?? '');
    $month = trim($_POST['month'] ?? '');
    $year = intval($_POST['year'] ?? 0);
    $total_fee = floatval($_POST['total_fee'] ?? 0);
    $amount_paid = floatval($_POST['amount_paid'] ?? 0);
    $remaining_fee = isset($_POST['remaining_fee']) ? floatval($_POST['remaining_fee']) : ($total_fee - $amount_paid);
    $receipt_number = ''; // Will be generated after insert/update
    $amount = $total_fee; // for backward compatibility, keep amount as total_fee
    $status = $_POST['status'] ?? 'Unpaid';
    $paid_date = $_POST['paid_date'] ?? null;

    if ($student_id && $class && $month && $year && $total_fee && $amount_paid) {
        // Check if fee already exists for this student/month/year
        $stmt = $conn->prepare('SELECT id FROM student_fees WHERE student_id = ? AND month = ? AND year = ? LIMIT 1');
        $stmt->bind_param('ssi', $student_id, $month, $year);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            // Update
            // Get the id for this record
            $stmt2 = $conn->prepare('SELECT id FROM student_fees WHERE student_id=? AND month=? AND year=?');
            $stmt2->bind_param('ssi', $student_id, $month, $year);
            $stmt2->execute();
            $stmt2->bind_result($fee_id);
            $stmt2->fetch();
            $stmt2->close();
            
            $receipt_number = 'FEE' . date('Ymd') . '-' . $fee_id;
            $stmt3 = $conn->prepare('UPDATE student_fees SET class=?, amount=?, total_fee=?, amount_paid=?, remaining_fee=?, receipt_number=?, status=?, paid_date=? WHERE student_id=? AND month=? AND year=?');
            $stmt3->bind_param('sddddsssssi', $class, $amount, $total_fee, $amount_paid, $remaining_fee, $receipt_number, $status, $paid_date, $student_id, $month, $year);
            if ($stmt3->execute()) {
                $success = 'Fee updated successfully!';
            } else {
                $error = 'Database error. Please try again.';
            }
        } else {
            // Insert
            $stmt2 = $conn->prepare('INSERT INTO student_fees (student_id, class, month, year, amount, total_fee, amount_paid, remaining_fee, receipt_number, status, paid_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $receipt_placeholder = ''; // Will be updated after insert
            $stmt2->bind_param('sssidddddss', $student_id, $class, $month, $year, $amount, $total_fee, $amount_paid, $remaining_fee, $receipt_placeholder, $status, $paid_date);
            if ($stmt2->execute()) {
                $fee_id = $conn->insert_id;
                $receipt_number = 'FEE' . date('Ymd') . '-' . $fee_id;
                $stmt3 = $conn->prepare('UPDATE student_fees SET receipt_number=? WHERE id=?');
                $stmt3->bind_param('si', $receipt_number, $fee_id);
                $stmt3->execute();
                $stmt3->close();
                $success = 'Fee added successfully!';
            } else {
                $error = 'Database error. Please try again.';
            }
        }
    } else {
        $error = 'Please fill in all required fields.';
    }
}

// View fees by student/class
$fee_records = [];
if (isset($_GET['view_fees'])) {
    $filter_student = $_GET['filter_student'] ?? '';
    $filter_class = $_GET['filter_class'] ?? '';
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
    while ($row = $result->fetch_assoc()) {
        $fee_records[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Fee Management</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .fee-table { width: 100%; border-collapse: collapse; margin-top: 1em; font-size: 0.9em; }
        .fee-table th, .fee-table td { border: 1px solid #ccc; padding: 0.5em; text-align: center; }
        .fee-table th { background: #f5f5f5; }
        .print-area { background: #fff; padding: 1em; margin: 1em auto; max-width: 100%; width: 210mm; /* A4 width */ }
        @media print {
            @page {
                size: A4 portrait;
                margin: 1cm;
            }
            body, html { 
                width: 210mm;
                margin: 0 auto !important;
                padding: 0 !important;
                font-size: 12pt;
            }
            body * { 
                visibility: hidden; 
            }
            .print-area, .print-area * { 
                visibility: visible; 
            }
            .print-area { 
                position: relative;
                left: 0;
                top: 0;
                margin: 0;
                padding: 1cm;
                width: 100%;
                box-shadow: none;
                border: none;
                page-break-after: always;
            }
            .print-area:last-child {
                page-break-after: avoid;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="form-container">
        <h2>Student Fee Management</h2>
        <?php if ($success): ?>
            <div class="success-msg"><?php echo $success; ?></div>
        <?php elseif ($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <h3>Add/Update Fee</h3>
            <label for="student_id">Select Student*</label>
            <select name="student_id" id="student_id" required>
                <option value="">-- Select --</option>
                <?php foreach ($students as $s): ?>
                    <option value="<?php echo htmlspecialchars($s['student_id']); ?>">
                        <?php echo htmlspecialchars($s['name'] . ' (' . $s['student_id'] . ', Class ' . $s['class'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label for="class">Class*</label>
            <input type="text" name="class" id="class" required>
            <label for="month">Month*</label>
            <input type="text" name="month" id="month" required placeholder="e.g. January">
            <label for="year">Year*</label>
            <input type="number" name="year" id="year" required min="2000" max="2100">
            <label for="total_fee">Total Fee*</label>
            <input type="number" step="0.01" name="total_fee" id="total_fee" required>
            <label for="amount_paid">Amount Paid*</label>
            <input type="number" step="0.01" name="amount_paid" id="amount_paid" required oninput="updateRemainingFee()">
            <label for="remaining_fee">Remaining Fee</label>
            <input type="number" step="0.01" name="remaining_fee" id="remaining_fee" readonly>

            <script>
            function updateRemainingFee() {
                var total = parseFloat(document.getElementById('total_fee').value) || 0;
                var paid = parseFloat(document.getElementById('amount_paid').value) || 0;
                var remain = total - paid;
                document.getElementById('remaining_fee').value = remain >= 0 ? remain : 0;
            }
            document.getElementById('total_fee').addEventListener('input', updateRemainingFee);
            document.getElementById('amount_paid').addEventListener('input', updateRemainingFee);
            </script>
            <label for="status">Status*</label>
            <select name="status" id="status" required>
                <option value="Paid">Paid</option>
                <option value="Unpaid">Unpaid</option>
            </select>
            <label for="paid_date">Paid Date</label>
            <input type="date" name="paid_date" id="paid_date">
            <button type="submit" name="add_fee">Add/Update Fee</button>
        </form>
        <hr>
        <form method="get" autocomplete="off">
            <h3>View Fees</h3>
            <label for="filter_student">Filter by Student</label>
            <select name="filter_student" id="filter_student">
                <option value="">-- All --</option>
                <?php foreach ($students as $s): ?>
                    <option value="<?php echo htmlspecialchars($s['student_id']); ?>" <?php if (!empty($_GET['filter_student']) && $_GET['filter_student'] == $s['student_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($s['name'] . ' (' . $s['student_id'] . ', Class ' . $s['class'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label for="filter_class">Filter by Class</label>
            <input type="text" name="filter_class" id="filter_class" value="<?php echo htmlspecialchars($_GET['filter_class'] ?? ''); ?>">
            <button type="submit" name="view_fees">View Fees</button>
        </form>
    </div>
    <?php if ($fee_records): 
        // Group records by student
        $student_records = [];
        foreach ($fee_records as $fee) {
            $key = $fee['student_id'];
            if (!isset($student_records[$key])) {
                $student_records[$key] = [
                    'student_id' => $fee['student_id'],
                    'class' => $fee['class'],
                    'records' => [],
                    'total_fee' => 0,
                    'total_paid' => 0,
                    'total_remaining' => 0
                ];
            }
            $student_records[$key]['records'][] = $fee;
            $student_records[$key]['total_fee'] += $fee['total_fee'];
            $student_records[$key]['total_paid'] += $fee['amount_paid'];
            $student_records[$key]['total_remaining'] += $fee['remaining_fee'];
        }
        ?>
        <div class="print-area" id="printArea">
            <?php foreach ($student_records as $student): ?>
            <div style="max-width:100%;width:190mm;margin:0 auto 2em auto;padding:1.5em;border:1px solid #ddd;border-radius:8px;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,0.1);page-break-inside:avoid;font-size:11pt;">
                <!-- Header -->
                <div style="text-align:center; margin-bottom:1.2em;">
                    <img src='assets/img/logo.png' alt='Logo' style='height:60px;vertical-align:middle;margin-right:12px;'>
                    <h2 style="color:#1a237e; margin:0.3em 0 0.2em 0; font-size:1.4em;line-height:1.2;">Learning Kids School System</h2>
                    <div style="color:#555; margin-bottom:0.3em;font-size:0.9em;">123 School Street, City, Country</div>
                    <h3 style="color:#3949ab; margin:0.3em 0 0.8em 0; padding-bottom:0.4em; border-bottom:1px solid #e0e0e0;font-size:1.1em;letter-spacing:1px;">FEE RECEIPT</h3>
                </div>
                
                <!-- Student Info -->
                <div style="display:flex; justify-content:space-between; margin-bottom:1.2em; background:#f8f9ff; padding:0.7em 1em; border-radius:5px;border:1px solid #e0e4ff;font-size:0.95em;">
                    <div>
                        <div><b>Student ID:</b> <?php echo htmlspecialchars($student['student_id']); ?></div>
                        <div><b>Class:</b> <?php echo htmlspecialchars($student['class']); ?></div>
                    </div>
                    <div style="text-align:right;">
                        <div><b>Receipt Date:</b> <?php echo date('d-M-Y'); ?></div>
                        <div><b>Receipt No:</b> <?php echo 'FEE' . date('Ymd') . '-' . $student['records'][0]['id']; ?></div>
                    </div>
                </div>
                
                <!-- Fee Details Table -->
                <table style="width:100%; border-collapse:collapse; margin-bottom:1.2em; background:#fff; border:1px solid #eee; border-radius:4px; overflow:hidden;font-size:0.9em;">
                    <thead>
                        <tr style="background:#1a237e; color:#fff;">
                            <th style="padding:0.7em 1em; text-align:left;">Month/Year</th>
                            <th style="padding:0.7em 1em; text-align:right;">Total Fee</th>
                            <th style="padding:0.7em 1em; text-align:right;">Paid</th>
                            <th style="padding:0.7em 1em; text-align:right;">Remaining</th>
                            <th style="padding:0.7em 1em; text-align:center;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($student['records'] as $fee): ?>
                        <tr style="border-bottom:1px solid #eee;">
                            <td style="padding:0.7em 1em;"><?php echo htmlspecialchars($fee['month'] . ' ' . $fee['year']); ?></td>
                            <td style="padding:0.7em 1em; text-align:right;"><?php echo number_format($fee['total_fee'], 2); ?></td>
                            <td style="padding:0.7em 1em; text-align:right; color:#2e7d32; font-weight:bold;"><?php echo number_format($fee['amount_paid'], 2); ?></td>
                            <td style="padding:0.7em 1em; text-align:right; color:#c62828; font-weight:bold;"><?php echo number_format($fee['remaining_fee'], 2); ?></td>
                            <td style="padding:0.7em 1em; text-align:center;">
                                <span style="display:inline-block; padding:0.2em 0.6em; border-radius:12px; font-size:0.85em; font-weight:500; background:<?php echo $fee['status'] === 'Paid' ? '#e8f5e9' : '#ffebee'; ?>; color:<?php echo $fee['status'] === 'Paid' ? '#2e7d32' : '#c62828'; ?>;">
                                    <?php echo htmlspecialchars($fee['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <!-- Summary Row -->
                        <tr style="background:#f5f5f5; font-weight:bold; border-top:2px solid #ddd;">
                            <td style="padding:0.8em 1em;">TOTAL</td>
                            <td style="padding:0.8em 1em; text-align:right;"><?php echo number_format($student['total_fee'], 2); ?></td>
                            <td style="padding:0.8em 1em; text-align:right; color:#2e7d32;"><?php echo number_format($student['total_paid'], 2); ?></td>
                            <td style="padding:0.8em 1em; text-align:right; color:#c62828;"><?php echo number_format($student['total_remaining'], 2); ?></td>
                            <td style="padding:0.8em 1em; text-align:center;">
                                <?php echo $student['total_remaining'] <= 0 ? 'PAID' : 'PENDING'; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <!-- Footer -->
                <div style="margin-top:1.5em; padding-top:0.8em; border-top:1px solid #e0e0e0; font-size:0.85em; color:#555;">
                    <div style="display:flex; justify-content:space-between; margin-bottom:1em;">
                        <div style="width:48%; padding:0.8em; background:#f5f5f5; border-radius:4px;">
                            <div style="font-weight:bold; margin-bottom:0.3em;">Payment Method:</div>
                            <div>Cash / Bank Transfer</div>
                        </div>
                        <div style="width:48%; padding:0.8em; background:#f5f5f5; border-radius:4px;">
                            <div style="font-weight:bold; margin-bottom:0.3em;">Next Due Date:</div>
                            <div><?php echo date('d-M-Y', strtotime('+1 month')); ?></div>
                        </div>
                    </div>
                    
                    <div style="margin-top:1.5em; padding:1em; background:#f0f7ff; border-radius:4px; border-left:4px solid #1a73e8;">
                        <div style="font-weight:bold; color:#1a73e8; margin-bottom:0.3em;">Important Notice:</div>
                        <div style="font-size:0.95em; line-height:1.5;">
                            • Please keep this receipt for future reference.<br>
                            • Late payments may incur additional charges.<br>
                            • For any discrepancies, please contact the accounts office within 7 days.
                        </div>
                    </div>
                    
                    <div style="margin-top:2em; display:flex; justify-content:space-between; align-items:flex-end;">
                        <div style="font-size:0.9em; color:#666;">
                            <div style="margin-bottom:0.3em;">Generated on: <?php echo date('d-M-Y h:i A'); ?></div>
                            <div>Thank you for your payment!</div>
                        </div>
                        <div style="text-align:center;">
                            <div style="margin-top:3em; padding-top:0.5em; border-top:1px solid #888; width:200px; margin-left:auto;">
                                Authorized Signature
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
                </tr>
                <?php foreach ($fee_records as $fee): ?>
                <tr>
                    <td><?php echo htmlspecialchars($fee['receipt_number']); ?></td>
                    <td><?php echo htmlspecialchars($fee['student_id']); ?></td>
                    <td><?php echo htmlspecialchars($fee['class']); ?></td>
                    <td><?php echo htmlspecialchars($fee['month']); ?></td>
                    <td><?php echo htmlspecialchars($fee['year']); ?></td>
                    <td><?php echo htmlspecialchars($fee['total_fee']); ?></td>
                    <td><?php echo htmlspecialchars($fee['amount_paid']); ?></td>
                    <td><?php echo htmlspecialchars($fee['remaining_fee']); ?></td>
                    <td><?php echo htmlspecialchars($fee['status']); ?></td>
                    <td><?php echo htmlspecialchars($fee['paid_date']); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <div style="margin-top:2em;">
                <strong>Principal Signature: ________________________</strong>
            </div>
        </div>
        <?php require_once 'includes/functions.php'; $license_type = getLicenseType(); ?>
        <div style="text-align:center; margin:2em; display:flex; gap:1em; justify-content:center; align-items:center;">
            <button onclick="window.print()">Print Fee Report</button>
            <?php if ($license_type === 'pro'): ?>
                <button id="savePngBtn" style="background:#0090d0;color:#fff;position:relative;">Save as PNG <span class="pro-badge" style="background:#b48e00;color:#fff;font-size:0.85em;margin-left:0.4em;">Pro</span></button>
            <?php else: ?>
                <button disabled title="Pro feature" style="background:#eee;color:#bbb;cursor:not-allowed;position:relative;">Save as PNG <span class="pro-badge" style="background:#b48e00;color:#fff;font-size:0.85em;margin-left:0.4em;">Pro</span> <span style="margin-left:0.3em;">🔒</span></button>
            <?php endif; ?>
        </div>
    <?php endif; ?>
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
                    link.download = 'fee-report.png';
                    link.href = canvas.toDataURL('image/png');
                    link.click();
                });
            });
        }
    });
    </script>
</body>
</html>