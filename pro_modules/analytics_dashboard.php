<?php
require_once '../includes/check_pro.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
$license_type = getLicenseType();

// Monthly student enrollment (last 12 months)
$enroll_data = [];
$res = $conn->query('SELECT CONCAT(YEAR(created_at), "-", LPAD(MONTH(created_at),2,"0")) as ym, COUNT(*) as total FROM students GROUP BY ym ORDER BY ym DESC LIMIT 12');
while ($row = $res->fetch_assoc()) {
    $enroll_data[] = $row;
}
// Gender distribution (only if gender column exists)
$gender_data = null;
$has_gender = false;
$res_col = $conn->query("SHOW COLUMNS FROM students LIKE 'gender'");
if ($res_col && $res_col->num_rows > 0) {
    $has_gender = true;
    $gender_data = ["Male" => 0, "Female" => 0, "Other" => 0];
    $res2 = $conn->query('SELECT gender, COUNT(*) as count FROM students GROUP BY gender');
    while ($row2 = $res2->fetch_assoc()) {
        $g = ucfirst(strtolower($row2['gender']));
        if (isset($gender_data[$g])) $gender_data[$g] += $row2['count'];
        else $gender_data['Other'] += $row2['count'];
    }
}
// Total students
$res3 = $conn->query('SELECT COUNT(*) as total FROM students');
$total_students = ($row3 = $res3->fetch_assoc()) ? $row3['total'] : 0;
// Total teachers
$res4 = $conn->query('SELECT COUNT(*) as total FROM teachers');
$total_teachers = ($row4 = $res4->fetch_assoc()) ? $row4['total'] : 0;
// Monthly fee collection (last 12 months)
$fee_data = [];
$res5 = $conn->query('SELECT CONCAT(year, "-", LPAD(month,2,"0")) as ym, SUM(amount) as total FROM student_fees WHERE status="Paid" GROUP BY year, month ORDER BY year DESC, month DESC LIMIT 12');
while ($row5 = $res5->fetch_assoc()) {
    $fee_data[] = $row5;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container { max-width: 600px; margin: 2em auto; background: #fff; padding: 2em; border-radius: 8px; box-shadow: 0 2px 8px #0001; }
        h2 { text-align: center; }
    </style>
</head>
<body>
    <div class="chart-container">
        <h2>Analytics Dashboard</h2>
<div style="text-align:center; margin:2em; display:flex; gap:1em; justify-content:center; align-items:center;">
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
            var card = document.getElementById('mainReportArea') || document.body;
            if (!card) return;
            html2canvas(card, {backgroundColor: '#fff', scale: 2}).then(function(canvas) {
                var link = document.createElement('a');
                link.download = 'analytics-report.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            });
        });
    }
});
</script>
        <div style="display:flex; justify-content:space-between; gap:2em; margin-bottom:2em;">
            <div style="flex:1; background:#f9f9f9; border-radius:8px; padding:1.5em; text-align:center; box-shadow:0 1px 4px #0001;">
                <div style="font-size:2.3em; color:#0090d0; font-weight:bold;"><?php echo $total_students; ?></div>
                <div style="font-size:1.1em; color:#555;">Total Students</div>
            </div>
            <div style="flex:1; background:#f9f9f9; border-radius:8px; padding:1.5em; text-align:center; box-shadow:0 1px 4px #0001;">
                <div style="font-size:2.3em; color:#b48e00; font-weight:bold;"><?php echo $total_teachers; ?></div>
                <div style="font-size:1.1em; color:#555;">Total Teachers</div>
            </div>
        </div>
        <canvas id="enrollChart" height="120"></canvas>
        <?php if ($has_gender): ?>
        <canvas id="genderChart" height="120" style="margin-top:2em;"></canvas>
        <?php endif; ?>
        <canvas id="feeChart" height="120" style="margin-top:2em;"></canvas>
    </div>
    <script>
    // Monthly student enrollment
    const enrollLabels = <?php echo json_encode(array_column(array_reverse($enroll_data), 'ym')); ?>;
    const enrollCounts = <?php echo json_encode(array_map('intval', array_column(array_reverse($enroll_data), 'total'))); ?>;
    new Chart(document.getElementById('enrollChart').getContext('2d'), {
        type: 'bar',
        data: { labels: enrollLabels, datasets: [{ label: 'Monthly Student Enrollment', data: enrollCounts, backgroundColor: '#3498db' }] },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });
    <?php if ($has_gender): ?>
    // Gender distribution
    const genderLabels = <?php echo json_encode(array_keys($gender_data)); ?>;
    const genderCounts = <?php echo json_encode(array_values($gender_data)); ?>;
    new Chart(document.getElementById('genderChart').getContext('2d'), {
        type: 'pie',
        data: { labels: genderLabels, datasets: [{ data: genderCounts, backgroundColor: ['#0090d0', '#e67e22', '#b48e00'] }] },
        options: { responsive: true, plugins: { legend: { display: true } } }
    });
    <?php endif; ?>
    // Monthly fee collection
    const feeLabels = <?php echo json_encode(array_column(array_reverse($fee_data), 'ym')); ?>;
    const feeTotals = <?php echo json_encode(array_map('floatval', array_column(array_reverse($fee_data), 'total'))); ?>;
    new Chart(document.getElementById('feeChart').getContext('2d'), {
        type: 'line',
        data: { labels: feeLabels, datasets: [{ label: 'Monthly Fee Collection', data: feeTotals, backgroundColor: '#2ecc71', borderColor: '#27ae60', fill: false }] },
        options: { responsive: true, plugins: { legend: { display: true } }, scales: { y: { beginAtZero: true } } }
    });
    </script>
</body>
</html> 