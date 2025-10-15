<?php
require_once '../includes/check_pro.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Fetch all students
$students = [];
$res = $conn->query('SELECT student_id, reg_no, name, father_name, class, dob, address FROM students ORDER BY name');
while ($row = $res->fetch_assoc()) {
    $students[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Students - Pro Feature</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        body { background: linear-gradient(120deg, #e0e7ff 0%, #fffbe7 100%); min-height:100vh; }
        .chart-container {
            background: rgba(255,255,255,0.82);
            box-shadow: 0 8px 32px 0 rgba(31,38,135,0.09);
            border-radius: 18px;
            padding: 2.5em 2em 2em 2em;
            max-width: 1200px;
            margin: 2.5em auto;
            position: relative;
        }
        .pro-badge {
            background: #b48e00;
            color: #fff;
            font-size: 0.9em;
            margin-left: 0.6em;
            padding: 4px 13px;
            border-radius: 7px;
            font-weight: 600;
            box-shadow: 0 1px 3px #0002;
            position: absolute;
            top: 2em;
            right: 2em;
        }
        .students-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 2.5em;
            background: rgba(255,255,255,0.93);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 2px 12px 0 rgba(31,38,135,0.07);
        }
        .students-table th, .students-table td {
            padding: 1em 0.7em;
            text-align: center;
            font-size: 1.07em;
        }
        .students-table th {
            background: #f5e7c6;
            color: #5a4300;
            font-weight: 700;
            position: sticky;
            top: 0;
            z-index: 2;
        }
        .students-table tr:nth-child(even) {
            background: #f7f9ff;
        }
        .students-table tr:nth-child(odd) {
            background: #fff;
        }
        .students-table tr:hover {
            background: #e0f7fa;
            transition: background 0.2s;
        }
        .search-box {
            margin-bottom: 1.2em;
            text-align: right;
        }
        .search-box input {
            padding: 0.5em 1.1em;
            border-radius: 5px;
            border: 1.5px solid #b48e00;
            font-size: 1em;
            outline: none;
            transition: border 0.2s;
        }
        .search-box input:focus {
            border-color: #0090d0;
        }
        @media (max-width: 900px) {
            .chart-container { padding: 1.5em 0.2em; }
            .students-table th, .students-table td { font-size: 0.97em; padding: 0.7em 0.2em; }
        }
        @media (max-width: 600px) {
            .chart-container { padding: 0.5em 0.2em; }
            .students-table th, .students-table td { font-size: 0.91em; padding: 0.5em 0.1em; }
        }
    </style>
</head>
<body>
    <div class="chart-container">
        <h2 style="margin-bottom:0.5em;">All Students</h2>
        <span class="pro-badge">Pro</span>
        <a href="../dashboard.php" style="margin-bottom:1.3em; display:inline-block; font-size:1.1em; color:#0090d0; text-decoration:none;">← Back to Dashboard</a>
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search students by name, reg no, class, ID..." />
        </div>
        <table class="students-table" id="studentsTable">
            <thead>
                <tr>
                    <th>Reg No</th>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Father Name</th>
                    <th>Class</th>
                    <th>DOB</th>
                    <th>Address</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $stu): ?>
                <tr>
                    <td><?php echo htmlspecialchars($stu['reg_no']); ?></td>
                    <td><?php echo htmlspecialchars($stu['student_id']); ?></td>
                    <td><?php echo htmlspecialchars($stu['name']); ?></td>
                    <td><?php echo htmlspecialchars($stu['father_name']); ?></td>
                    <td><?php echo htmlspecialchars($stu['class']); ?></td>
                    <td><?php echo htmlspecialchars($stu['dob']); ?></td>
                    <td><?php echo htmlspecialchars($stu['address']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script>
    // Live search filter for students table
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('searchInput');
        const table = document.getElementById('studentsTable');
        input.addEventListener('keyup', function() {
            const filter = input.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(function(row) {
                let text = row.textContent.toLowerCase();
                row.style.display = text.indexOf(filter) > -1 ? '' : 'none';
            });
        });
    });
    </script>
</body>
</html>
