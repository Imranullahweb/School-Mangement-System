<?php
require_once '../includes/check_pro.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Handle Delete Action
if (isset($_GET['delete']) && isset($_GET['type']) && isset($_GET['id'])) {
    $type = $_GET['type'];
    $id = $_GET['id'];
    $table = ($type === 'teacher') ? 'teachers' : 'students';
    $id_field = $type . '_id';
    
    $stmt = $conn->prepare("DELETE FROM $table WHERE $id_field = ?");
    $stmt->bind_param('s', $id);
    if ($stmt->execute()) {
        header('Location: view_users.php?deleted=1');
        exit();
    } else {
        $error = "Error deleting user: " . $conn->error;
    }
}

// Handle Edit Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_user') {
    $type = $_POST['user_type'];
    $id = $_POST['user_id'];
    $table = ($type === 'teacher') ? 'teachers' : 'students';
    $id_field = $type . '_id';
    
    if ($type === 'teacher') {
        $stmt = $conn->prepare("UPDATE $table SET name = ?, father_name = ?, designation = ?, address = ?, phone = ? WHERE $id_field = ?");
        $stmt->bind_param('ssssss', 
            $_POST['name'],
            $_POST['father_name'],
            $_POST['designation'],
            $_POST['address'],
            $_POST['phone'],
            $id
        );
    } else {
        $stmt = $conn->prepare("UPDATE $table SET name = ?, father_name = ?, class = ?, dob = ?, address = ? WHERE $id_field = ?");
        $stmt->bind_param('ssssss', 
            $_POST['name'],
            $_POST['father_name'],
            $_POST['class'],
            $_POST['dob'],
            $_POST['address'],
            $id
        );
    }
    
    if ($stmt->execute()) {
        header('Location: view_users.php?updated=1');
        exit();
    } else {
        $error = "Error updating user: " . $conn->error;
    }
}

// Fetch all students
$students = [];
$res = $conn->query('SELECT student_id AS id, name, father_name, class, dob, address FROM students');
while ($row = $res->fetch_assoc()) {
    $row['role'] = 'Student';
    $row['contact'] = '-'; // No contact field in students table
    $students[] = $row;
}
// Fetch all teachers
$teachers = [];
$res2 = $conn->query('SELECT teacher_id AS id, name, father_name, designation AS class, NULL AS dob, address, phone AS contact FROM teachers');
while ($row2 = $res2->fetch_assoc()) {
    $row2['role'] = 'Teacher';
    $teachers[] = $row2;
}
// Merge users
$users = array_merge($students, $teachers);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Users - Pro Feature</title>
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
        .users-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 2.5em;
            background: rgba(255,255,255,0.93);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 2px 12px 0 rgba(31,38,135,0.07);
        }
        .users-table th, .users-table td {
            padding: 1em 0.7em;
            text-align: center;
            font-size: 1.07em;
        }
        .users-table th {
            background: #f5e7c6;
            color: #5a4300;
            font-weight: 700;
            position: sticky;
            top: 0;
            z-index: 2;
        }
        .users-table tr:nth-child(even) {
            background: #f7f9ff;
        }
        .users-table tr:nth-child(odd) {
            background: #fff;
        }
        .users-table tr:hover {
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
            .users-table th, .users-table td { font-size: 0.97em; padding: 0.7em 0.2em; }
        }
        @media (max-width: 600px) {
            .chart-container { padding: 0.5em 0.2em; }
            .users-table th, .users-table td { font-size: 0.91em; padding: 0.5em 0.1em; }
        }
    </style>
</head>
<body>
    <div class="chart-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1em; flex-wrap: wrap;">
            <div>
                <h2 style="margin: 0 0 0.5em 0;">All Users</h2>
                <a href="../dashboard.php" style="font-size:1.1em; color:#0090d0; text-decoration:none;">← Back to Dashboard</a>
            </div>
            <span class="pro-badge" style="margin: 0.5em 0;">Pro</span>
        </div>
        
        <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 1em;">
                User updated successfully!
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-info" style="background: #d1ecf1; color: #0c5460; padding: 10px; border-radius: 4px; margin-bottom: 1em;">
                User deleted successfully!
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger" style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 1em;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search users by name, role, class, ID..." />
        </div>
        <table class="users-table" id="usersTable">
            <thead>
                <tr>
                    <th>Role</th>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Father Name</th>
                    <th>Class/Designation</th>
                    <th>DOB</th>
                    <th>Address</th>
                    <th>Contact</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                    <td><?php echo htmlspecialchars($user['father_name']); ?></td>
                    <td><?php echo htmlspecialchars($user['class']); ?></td>
                    <td><?php echo $user['dob'] ? htmlspecialchars($user['dob']) : '-'; ?></td>
                    <td><?php echo htmlspecialchars($user['address']); ?></td>
                    <td><?php echo htmlspecialchars($user['contact']); ?></td>
                    <td>
                        <button class="btn-edit" onclick="editUser('<?php echo $user['role'] === 'Teacher' ? 'teacher' : 'student'; ?>', '<?php echo $user['id']; ?>')" 
                                style="background: #3498db; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; margin-right: 5px;">
                            Edit
                        </button>
                        <button class="btn-delete" onclick="confirmDelete('<?php echo $user['role'] === 'Teacher' ? 'teacher' : 'student'; ?>', '<?php echo $user['id']; ?>')" 
                                style="background: #e74c3c; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">
                            Delete
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Edit User Modal -->
    <div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
        <div style="background: white; padding: 2em; border-radius: 8px; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1em;">
                <h3 style="margin: 0;">Edit User</h3>
                <button onclick="closeModal()" style="background: none; border: none; font-size: 1.5em; cursor: pointer;">&times;</button>
            </div>
            <form id="editUserForm" method="POST" action="">
                <input type="hidden" name="action" value="edit_user">
                <input type="hidden" name="user_type" id="userType">
                <input type="hidden" name="user_id" id="userId">
                
                <div id="editFormContent">
                    <!-- Form will be loaded here via AJAX -->
                </div>
                
                <div style="margin-top: 1.5em; text-align: right;">
                    <button type="button" onclick="closeModal()" style="margin-right: 10px; padding: 8px 15px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">
                        Cancel
                    </button>
                    <button type="submit" style="padding: 8px 20px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    // Function to open edit modal
    function editUser(type, id) {
        // Show loading state
        document.getElementById('editFormContent').innerHTML = '<p>Loading...</p>';
        document.getElementById('editModal').style.display = 'flex';
        document.getElementById('userType').value = type;
        document.getElementById('userId').value = id;
        
        // Load edit form via AJAX
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_user_edit_form.php?type=' + type + '&id=' + id, true);
        xhr.onload = function() {
            if (this.status === 200) {
                document.getElementById('editFormContent').innerHTML = this.responseText;
            } else {
                document.getElementById('editFormContent').innerHTML = 'Error loading form. Please try again.';
            }
        };
        xhr.send();
    }
    
    // Function to close modal
    function closeModal() {
        document.getElementById('editModal').style.display = 'none';
    }
    
    // Function to confirm delete
    function confirmDelete(type, id) {
        if (confirm('Are you sure you want to delete this ' + type + '? This action cannot be undone.')) {
            window.location.href = 'view_users.php?delete=1&type=' + type + '&id=' + id;
        }
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('editModal');
        if (event.target === modal) {
            closeModal();
        }
    };
    
    // Live search filter for users table
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('searchInput');
        if (input) {
            input.addEventListener('keyup', function() {
                const filter = input.value.toLowerCase().trim();
                const rows = document.querySelectorAll('#usersTable tbody tr');
                
                rows.forEach(function(row) {
                    const cells = row.getElementsByTagName('td');
                    let rowText = '';
                    
                    // Combine text from all cells except the last one (actions)
                    for (let i = 0; i < cells.length - 1; i++) {
                        rowText += cells[i].textContent.toLowerCase() + ' ';
                    }
                    
                    // Show/hide row based on search term
                    row.style.display = rowText.includes(filter) ? '' : 'none';
                });
            });
            
            // Add focus to search input on page load
            input.focus();
        }
    });
    </script>
    </script>
</body>
</html>
