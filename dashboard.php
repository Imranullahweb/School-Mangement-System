<?php
require_once 'includes/auth.php';
if (!is_admin_logged_in()) {
    header('Location: index.php');
    exit;
}
require_once 'includes/functions.php';
$license_type = getLicenseType();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - School Management System</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        body {
            background: linear-gradient(135deg, #e0f7fa 0%, #f8fafc 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', 'Roboto', Arial, sans-serif;
        }
        .dashboard-container {
            max-width: 750px;
            margin: 48px auto;
            padding: 2.5em 2em;
            background: rgba(255,255,255,0.75);
            border-radius: 18px;
            box-shadow: 0 8px 32px #0090d055, 0 1.5px 8px #0001;
            backdrop-filter: blur(4px);
            border: 1.5px solid #fff6;
        }
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5em;
        }
        .dashboard-header h2 {
            margin: 0;
            font-size: 2.1em;
            letter-spacing: 0.01em;
            color: #0090d0;
            font-family: 'Roboto Slab', serif;
            text-shadow: 0 2px 8px #0090d022;
        }
        .dashboard-header .home-btn {
            display: inline-flex;
            align-items: center;
            background: #fff;
            border-radius: 50%;
            box-shadow: 0 1.5px 8px #0090d022;
            border: 1.5px solid #0090d0;
            padding: 0.25em;
            margin-right: 0.2em;
            transition: box-shadow .18s, background .18s;
        }
        .dashboard-header .home-btn:hover {
            background: #e0f7fa;
            box-shadow: 0 2px 14px #0090d044;
        }
        .logout-btn {
            background: linear-gradient(90deg,#fa5252,#ff8787);
            color: #fff;
            border: none;
            padding: 0.55em 1.2em;
            border-radius: 6px;
            font-size: 1em;
            font-weight: 600;
            box-shadow: 0 2px 8px #fa525233;
            cursor: pointer;
            transition: background .18s, box-shadow .18s;
        }
        .logout-btn:hover {
            background: linear-gradient(90deg,#ff8787,#fa5252);
            box-shadow: 0 4px 16px #fa525244;
        }
        .dashboard-nav {
            margin-top: 2.5em;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.2em;
        }
        .dashboard-nav a {
            display: flex;
            align-items: center;
            gap: 0.7em;
            padding: 1.15em 1em 1.15em 1.2em;
            background: rgba(240,248,255,0.82);
            border-radius: 12px;
            text-decoration: none;
            color: #0a2d3a;
            font-weight: 500;
            font-size: 1.08em;
            box-shadow: 0 2px 10px #0090d012;
            border: 1.2px solid #0090d022;
            transition: background 0.18s, box-shadow 0.18s, transform 0.13s;
            position: relative;
        }
        .dashboard-nav a:hover:not(.locked) {
            background: linear-gradient(90deg, #e0f7fa 60%, #b2ebf2 100%);
            box-shadow: 0 6px 24px #0090d044;
            transform: translateY(-2px) scale(1.03);
        }
        .dashboard-nav a.locked {
            color: #bbb;
            background: #f6f6f6;
            pointer-events: none;
            border: 1.2px dashed #bbb;
            opacity: 0.7;
        }
        .pro-badge {
            margin-left: 0.6em;
            font-size: 0.92em;
            color: #fff;
            background: linear-gradient(90deg,#b48e00,#ffec99);
            border-radius: 4px;
            padding: 0.1em 0.6em;
            font-weight: 700;
            letter-spacing: 0.02em;
            box-shadow: 0 1.5px 8px #b48e0033;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <div style="display:flex;align-items:center;gap:1em;">
                <a href="dashboard.php" class="home-btn" title="Home"><svg width="28" height="28" fill="none" viewBox="0 0 24 24"><path d="M3 11.5L12 4l9 7.5" stroke="#0090d0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 21V14h6v7" stroke="#0090d0" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
                <h2>Admin Dashboard</h2>
            </div>
            <form method="post" action="logout.php" style="margin:0;">
                <button class="logout-btn" type="submit">Logout</button>
            </form>
        </div>
        <div class="dashboard-nav">
            <a href="student_register.php">👦 Student Registration</a>
            <a href="teacher_register.php">👨‍🏫 Teacher Registration</a>
            <a href="student_certificate.php">📄 Student Leaving Certificate</a>
            <a href="teacher_experience.php">📜 Teacher Experience Certificate</a>
            <a href="student_fees.php">💸 Student Fee Management</a>
            <a href="generate_marksheet.php">📝 Marksheet (DMC) Generation</a>
            <?php if ($license_type === 'pro'): ?>
                <a href="pro_modules/generate_admit_card.php">🎫 Admit Cards <span class="pro-badge">Pro</span></a>
                <a href="pro_modules/analytics_dashboard.php">📊 Analytics <span class="pro-badge">Pro</span></a>
                <a href="pro_modules/view_students.php">👥 View All Students <span class="pro-badge">Pro</span></a>
                <a href="pro_modules/view_users.php">🧑‍💼 View All Users <span class="pro-badge">Pro</span></a>
            <?php else: ?>
                <a href="activate_pro.php">🔐 Activate Pro to unlock features</a>
                <a class="locked">🎫 Admit Cards <span class="pro-badge">Pro</span></a>
                <a class="locked">📊 Analytics <span class="pro-badge">Pro</span></a>
                <a class="locked">👥 View All Students <span class="pro-badge">Pro</span></a>
                <a class="locked">🧑‍💼 View All Users <span class="pro-badge">Pro</span></a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>