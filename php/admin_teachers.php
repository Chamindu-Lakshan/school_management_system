<?php
include 'config.php';
if (!isset($_SESSION['loggedin'])) header("Location: ../index.html");

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Handle user addition
$message = '';
$message_type = '';

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $user_id = (int)$_POST['user_id'];
    $username = trim($_POST['username']);
    
    // Prevent admin from deleting themselves
    if ($user_id == $_SESSION['user_id']) {
        $message = "You cannot delete your own account.";
        $message_type = "error";
    } else {
        // Get user details for confirmation
        $get_user_sql = "SELECT username, full_name, role FROM users WHERE id = ? AND role IN ('admin', 'teacher')";
        $get_user_stmt = $conn->prepare($get_user_sql);
        $get_user_stmt->bind_param("i", $user_id);
        $get_user_stmt->execute();
        $user_result = $get_user_stmt->get_result();
        
        if ($user_result->num_rows > 0) {
            $user_data = $user_result->fetch_assoc();
            
            // Verify username matches for security
            if ($username === $user_data['username']) {
                // Delete the user
                $delete_sql = "DELETE FROM users WHERE id = ? AND role IN ('admin', 'teacher')";
                $delete_stmt = $conn->prepare($delete_sql);
                $delete_stmt->bind_param("i", $user_id);
                
                if ($delete_stmt->execute()) {
                    $role_display = ucfirst($user_data['role']);
                    $message = "$role_display '{$user_data['full_name']}' has been removed successfully.";
                    $message_type = "success";
                } else {
                    $message = "Error deleting user. Please try again.";
                    $message_type = "error";
                }
                $delete_stmt->close();
            } else {
                $message = "Username verification failed. Please try again.";
                $message_type = "error";
            }
        } else {
            $message = "User not found or invalid user type.";
            $message_type = "error";
        }
        $get_user_stmt->close();
    }
}

if (isset($_POST['add_user'])) {
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];
    
    // Validation
    if (empty($username) || empty($full_name) || empty($password)) {
        $message = "Username, full name, and password are required.";
        $message_type = "error";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
        $message_type = "error";
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $message_type = "error";
    } elseif (!in_array($role, array('admin', 'teacher'))) {
        $message = "Invalid role selected.";
        $message_type = "error";
    } else {
        // Check if username already exists
        $check_sql = "SELECT id FROM users WHERE username = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $message = "Username already exists. Please choose a different one.";
            $message_type = "error";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user with selected role
            $insert_sql = "INSERT INTO users (username, password, full_name, email, phone, role, status) VALUES (?, ?, ?, ?, ?, ?, 'active')";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("ssssss", $username, $hashed_password, $full_name, $email, $phone, $role);
            
            if ($insert_stmt->execute()) {
                $role_display = ucfirst($role);
                $message = "$role_display added successfully!";
                $message_type = "success";
                
                // Clear form data
                $_POST = array();
            } else {
                $message = "Error adding user: " . $insert_stmt->error;
                $message_type = "error";
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
}

// Get all users (both admins and teachers)
$users_sql = "SELECT id, username, full_name, email, phone, role, status, created_at FROM users WHERE role IN ('admin', 'teacher') ORDER BY role, created_at DESC";
$users_result = $conn->query($users_sql);

// Get statistics
$stats_sql = "SELECT 
                COUNT(*) as total_users,
                COUNT(CASE WHEN role = 'admin' THEN 1 END) as total_admins,
                COUNT(CASE WHEN role = 'teacher' THEN 1 END) as total_teachers,
                COUNT(CASE WHEN status = 'active' THEN 1 END) as active_users,
                COUNT(CASE WHEN status = 'inactive' THEN 1 END) as inactive_users
               FROM users WHERE role IN ('admin', 'teacher')";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Management - SchoolSync</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: center;
        }
        .admin-header h1 {
            margin: 0 0 10px 0;
            font-size: 2rem;
        }
        .admin-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            text-align: center;
        }
        .stat-card h3 {
            color: #1e3a8a;
            margin-top: 0;
            font-size: 1.1rem;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #059669;
            margin: 10px 0;
        }
        .form-container {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            margin-bottom: 30px;
        }
        .form-container h3 {
            color: #1e3a8a;
            margin-top: 0;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }
        .form-group input, .form-group select {
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #3b82f6;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
        }
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        .btn-secondary:hover {
            background: #4b5563;
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
        }
        .message.success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .message.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        .users-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .users-table th {
            background: #1e3a8a;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        .users-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        .users-table tr:hover {
            background: #f8fafc;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-active {
            background: #d1fae5;
            color: #065f46;
        }
        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }
        .role-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            margin-left: 8px;
        }
        .role-admin {
            background: #fef3c7;
            color: #92400e;
        }
        .role-teacher {
            background: #dbeafe;
            color: #1e40af;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-size: 18px;
        }
        .no-users {
            text-align: center;
            padding: 40px;
            color: #64748b;
            font-style: italic;
        }
        .role-selector {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            font-size: 14px;
            color: #374151;
        }
        .role-selector:focus {
            border-color: #3b82f6;
            background: white;
        }
        .btn-sm {
            padding: 8px 16px;
            font-size: 12px;
        }
        .btn-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }
        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3);
        }
        .actions-cell {
            text-align: center;
            min-width: 100px;
        }
        .delete-form {
            margin: 0;
            display: inline;
        }
        .text-muted {
            color: #6b7280;
            font-style: italic;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            animation: modalSlideIn 0.3s ease-out;
        }
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .modal-header {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            padding: 20px;
            border-radius: 12px 12px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-header h3 {
            margin: 0;
            font-size: 1.2rem;
        }
        .close {
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            line-height: 1;
        }
        .close:hover {
            opacity: 0.7;
        }
        .modal-body {
            padding: 20px;
        }
        .modal-body p {
            margin: 0 0 15px 0;
            color: #374151;
        }
        .user-info {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            border-left: 4px solid #3b82f6;
        }
        .user-info p {
            margin: 0 0 8px 0;
        }
        .user-info p:last-child {
            margin-bottom: 0;
        }
        .warning-text {
            color: #dc2626;
            font-weight: 600;
            background: #fef2f2;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #fecaca;
        }
        .modal-footer {
            padding: 20px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <!-- Admin Header -->
        <div class="admin-header">
            <h1><i class="fa-solid fa-users-cog"></i> User Management</h1>
            <p>Administrator Panel - Manage System Users (Admins & Teachers)</p>
        </div>

        <!-- Message Display -->
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <i class="fa-solid fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3><i class="fa-solid fa-users"></i> Total Users</h3>
                <div class="stat-value"><?php echo $stats['total_users']; ?></div>
            </div>
            
            <div class="stat-card">
                <h3><i class="fa-solid fa-user-shield"></i> Administrators</h3>
                <div class="stat-value"><?php echo $stats['total_admins']; ?></div>
            </div>
            
            <div class="stat-card">
                <h3><i class="fa-solid fa-user-tie"></i> Teachers</h3>
                <div class="stat-value"><?php echo $stats['total_teachers']; ?></div>
            </div>
            
            <div class="stat-card">
                <h3><i class="fa-solid fa-user-check"></i> Active Users</h3>
                <div class="stat-value"><?php echo $stats['active_users']; ?></div>
            </div>
        </div>

        <!-- Add New User -->
        <div class="form-container">
            <h3><i class="fa-solid fa-plus-circle"></i> Add New User</h3>
            
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Role *</label>
                        <select id="role" name="role" class="role-selector" required>
                            <option value="">Select Role</option>
                            <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] == 'admin') ? 'selected' : ''; ?>>Administrator</option>
                            <option value="teacher" <?php echo (isset($_POST['role']) && $_POST['role'] == 'teacher') ? 'selected' : ''; ?>>Teacher</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
                
                <button type="submit" name="add_user" class="btn btn-primary">
                    <i class="fa-solid fa-plus"></i> Add User
                </button>
            </form>
        </div>

        <!-- Users List -->
        <div class="form-container">
            <h3><i class="fa-solid fa-list"></i> Current Users</h3>
            
            <?php if ($users_result->num_rows > 0): ?>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined Date</th>
                            <th class="actions-cell">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $users_result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div class="user-avatar">
                                            <i class="fa-solid fa-<?php echo $user['role'] == 'admin' ? 'user-shield' : 'user-tie'; ?>"></i>
                                        </div>
                                        <div>
                                            <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo $user['email'] ? htmlspecialchars($user['email']) : 'Not provided'; ?></td>
                                <td><?php echo $user['phone'] ? htmlspecialchars($user['phone']) : 'Not provided'; ?></td>
                                <td>
                                    <span class="role-badge role-<?php echo $user['role']; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $user['status']; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                <td class="actions-cell">
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <button type="button" onclick="confirmDelete(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>', '<?php echo htmlspecialchars($user['full_name']); ?>', '<?php echo $user['role']; ?>')" class="btn btn-danger btn-sm">
                                            <i class="fa-solid fa-trash-can"></i> Delete
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">Current User</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-users">
                    <i class="fa-solid fa-users-slash fa-3x"></i>
                    <h3>No Users Found</h3>
                    <p>No users have been added to the system yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fa-solid fa-exclamation-triangle"></i> Confirm User Deletion</h3>
                <span class="close" onclick="closeDeleteModal()">&times;</span>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this user?</p>
                <div class="user-info">
                    <p><strong>Name:</strong> <span id="deleteUserName"></span></p>
                    <p><strong>Username:</strong> <span id="deleteUserUsername"></span></p>
                    <p><strong>Role:</strong> <span id="deleteUserRole"></span></p>
                </div>
                <p class="warning-text"><i class="fa-solid fa-exclamation-circle"></i> This action cannot be undone!</p>
            </div>
            <div class="modal-footer">
                <form method="POST" id="deleteForm">
                    <input type="hidden" name="user_id" id="deleteUserId">
                    <input type="hidden" name="username" id="deleteUsername">
                    <button type="button" onclick="closeDeleteModal()" class="btn btn-secondary">Cancel</button>
                    <button type="submit" name="delete_user" class="btn btn-danger">Delete User</button>
                </form>
            </div>
        </div>
    </div>

    <script src="../js/scripts.js"></script>
    <script>
        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
        
        document.getElementById('password').addEventListener('input', function() {
            const confirmPassword = document.getElementById('confirm_password');
            if (confirmPassword.value) {
                if (this.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Passwords do not match');
                } else {
                    confirmPassword.setCustomValidity('');
                }
            }
        });
        
        // Role selection styling
        document.getElementById('role').addEventListener('change', function() {
            const selectedRole = this.value;
            if (selectedRole === 'admin') {
                this.style.background = '#fef3c7';
                this.style.borderColor = '#f59e0b';
            } else if (selectedRole === 'teacher') {
                this.style.background = '#dbeafe';
                this.style.borderColor = '#3b82f6';
            } else {
                this.style.background = '#f8fafc';
                this.style.borderColor = '#e2e8f0';
            }
        });
        
        // Delete confirmation modal functions
        function confirmDelete(userId, username, fullName, role) {
            document.getElementById('deleteUserId').value = userId;
            document.getElementById('deleteUsername').value = username;
            document.getElementById('deleteUserName').textContent = fullName;
            document.getElementById('deleteUserUsername').textContent = username;
            document.getElementById('deleteUserRole').textContent = role.charAt(0).toUpperCase() + role.slice(1);
            
            document.getElementById('deleteModal').style.display = 'block';
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target === modal) {
                closeDeleteModal();
            }
        }
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeDeleteModal();
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>
