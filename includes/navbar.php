<?php if (basename($_SERVER['PHP_SELF']) != 'login.php' && basename($_SERVER['PHP_SELF']) != 'register.php'): ?>
<div class="navbar" style="background: #1a237e; padding: 10px 0; margin-bottom: 20px; border-radius: 4px;">
    <div style="max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; padding: 0 15px;">
        <div class="nav-buttons" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="dashboard.php" class="nav-btn" style="background: #3949ab; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-weight: 500; transition: background 0.3s; white-space: nowrap;" onmouseover="this.style.background='#303f9f'" onmouseout="this.style.background='#3949ab'">Dashboard</a>
            <a href="student_fees.php" class="nav-btn" style="background: #3949ab; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-weight: 500; transition: background 0.3s; white-space: nowrap;" onmouseover="this.style.background='#303f9f'" onmouseout="this.style.background='#3949ab'">Fee Management</a>
            <a href="student_marksheet.php" class="nav-btn" style="background: #3949ab; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-weight: 500; transition: background 0.3s; white-space: nowrap;" onmouseover="this.style.background='#303f9f'" onmouseout="this.style.background='#3949ab'">Marksheets</a>
            <a href="student_certificate.php" class="nav-btn" style="background: #3949ab; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-weight: 500; transition: background 0.3s; white-space: nowrap;" onmouseover="this.style.background='#303f9f'" onmouseout="this.style.background='#3949ab'">Student Certificates</a>
            <a href="teacher_experience.php" class="nav-btn" style="background: #3949ab; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-weight: 500; transition: background 0.3s; white-space: nowrap;" onmouseover="this.style.background='#303f9f'" onmouseout="this.style.background='#3949ab'">Teacher Experience</a>
            <a href="student_id_card.php" class="nav-btn" style="background: #3949ab; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-weight: 500; transition: background 0.3s; white-space: nowrap;" onmouseover="this.style.background='#303f9f'" onmouseout="this.style.background='#3949ab'">ID Cards</a>
        </div>
        <div class="user-actions" style="display: flex; gap: 10px;">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="profile.php" class="nav-btn" style="background: #5c6bc0; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-weight: 500; transition: background 0.3s;" onmouseover="this.style.background='#3f51b5'" onmouseout="this.style.background='#5c6bc0'">Profile</a>
                <a href="logout.php" class="nav-btn" style="background: #d32f2f; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-weight: 500; transition: background 0.3s;" onmouseover="this.style.background='#c62828'" onmouseout="this.style.background='#d32f2f'">Logout</a>
            <?php else: ?>
                <a href="login.php" class="nav-btn" style="background: #388e3c; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-weight: 500; transition: background 0.3s;" onmouseover="this.style.background='#2e7d32'" onmouseout="this.style.background='#388e3c'">Login</a>
                <a href="register.php" class="nav-btn" style="background: #5e35b1; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-weight: 500; transition: background 0.3s;" onmouseover="this.style.background='#512da8'" onmouseout="this.style.background='#5e35b1'">Register</a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>
