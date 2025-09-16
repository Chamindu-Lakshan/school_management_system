<?php
include 'config.php';
if (!isset($_SESSION['loggedin'])) header("Location: ../index.html");

// Get current user information
$user_id = $_SESSION['user_id'];
$user_sql = "SELECT * FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_stmt->close();

// Handle profile update
$message = '';
$message_type = '';

if (isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    
    // Validation
    if (empty($full_name)) {
        $message = "Full name is required.";
        $message_type = "error";
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $message_type = "error";
    } else {
        // Update profile
        $update_sql = "UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sssi", $full_name, $email, $phone, $user_id);
        
        if ($update_stmt->execute()) {
            $message = "Profile updated successfully!";
            $message_type = "success";
            
            // Refresh user data
            $user_stmt = $conn->prepare($user_sql);
            $user_stmt->bind_param("i", $user_id);
            $user_stmt->execute();
            $user_result = $user_stmt->get_result();
            $user = $user_result->fetch_assoc();
            $user_stmt->close();
        } else {
            $message = "Error updating profile. Please try again.";
            $message_type = "error";
        }
        $update_stmt->close();
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $message = "All password fields are required.";
        $message_type = "error";
    } elseif ($new_password !== $confirm_password) {
        $message = "New passwords do not match.";
        $message_type = "error";
    } elseif (strlen($new_password) < 6) {
        $message = "New password must be at least 6 characters long.";
        $message_type = "error";
    } else {
        // Verify current password
        if (password_verify($current_password, $user['password'])) {
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password
            $password_sql = "UPDATE users SET password = ? WHERE id = ?";
            $password_stmt = $conn->prepare($password_sql);
            $password_stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($password_stmt->execute()) {
                $message = "Password changed successfully!";
                $message_type = "success";
            } else {
                $message = "Error changing password. Please try again.";
                $message_type = "error";
            }
            $password_stmt->close();
        } else {
            $message = "Current password is incorrect.";
            $message_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - SchoolSync</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: center;
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 48px;
            border: 4px solid rgba(255, 255, 255, 0.3);
        }
        .profile-header h1 {
            margin: 0 0 10px 0;
            font-size: 2rem;
        }
        .profile-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }
        .profile-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            margin-bottom: 20px;
        }
        .profile-section h3 {
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
        .form-group input {
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        .form-group input:focus {
            outline: none;
            border-color: #3b82f6;
        }
        .form-group input[readonly] {
            background: #f8fafc;
            color: #64748b;
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
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .info-item {
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #3b82f6;
        }
        .info-item h4 {
            margin: 0 0 10px 0;
            color: #1e3a8a;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-item p {
            margin: 0;
            color: #374151;
            font-size: 16px;
            font-weight: 600;
        }
        .password-form {
            background: #f8fafc;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <div class="profile-container">
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-avatar">
                    <i class="fa-solid fa-user"></i>
                </div>
                <h1><?php echo htmlspecialchars($user['full_name']); ?></h1>
                <p><?php echo ucfirst($user['role']); ?> Account</p>
                <p>Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
            </div>

            <!-- Message Display -->
            <?php if (!empty($message)): ?>
                <div class="message <?php echo $message_type; ?>">
                    <i class="fa-solid fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Profile Information -->
            <div class="profile-section">
                <h3><i class="fa-solid fa-user-circle"></i> Profile Information</h3>
                
                <div class="info-grid">
                    <div class="info-item">
                        <h4>Username</h4>
                        <p><?php echo htmlspecialchars($user['username']); ?></p>
                    </div>
                    <div class="info-item">
                        <h4>Full Name</h4>
                        <p><?php echo htmlspecialchars($user['full_name']); ?></p>
                    </div>
                    <div class="info-item">
                        <h4>Role</h4>
                        <p><?php echo ucfirst($user['role']); ?></p>
                    </div>
                    <div class="info-item">
                        <h4>Status</h4>
                        <p><?php echo ucfirst($user['status']); ?></p>
                    </div>
                    <div class="info-item">
                        <h4>Email</h4>
                        <p><?php echo $user['email'] ? htmlspecialchars($user['email']) : 'Not provided'; ?></p>
                    </div>
                    <div class="info-item">
                        <h4>Phone</h4>
                        <p><?php echo $user['phone'] ? htmlspecialchars($user['phone']) : 'Not provided'; ?></p>
                    </div>
                </div>
            </div>

            <!-- Edit Profile -->
            <div class="profile-section">
                <h3><i class="fa-solid fa-edit"></i> Edit Profile</h3>
                
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="role">Role</label>
                            <input type="text" id="role" value="<?php echo ucfirst($user['role']); ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="full_name">Full Name *</label>
                            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                        </div>
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn btn-primary">
                        <i class="fa-solid fa-save"></i> Update Profile
                    </button>
                </form>
            </div>

            <!-- Change Password -->
            <div class="profile-section">
                <h3><i class="fa-solid fa-lock"></i> Change Password</h3>
                
                <form method="POST" class="password-form">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="current_password">Current Password *</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">New Password *</label>
                            <input type="password" id="new_password" name="new_password" required minlength="6">
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password *</label>
                            <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                        </div>
                    </div>
                    
                    <button type="submit" name="change_password" class="btn btn-primary">
                        <i class="fa-solid fa-key"></i> Change Password
                    </button>
                </form>
            </div>

            <!-- Account Security -->
            <div class="profile-section">
                <h3><i class="fa-solid fa-shield-alt"></i> Account Security</h3>
                
                <div class="info-grid">
                    <div class="info-item">
                        <h4>Session Status</h4>
                        <p>Active</p>
                    </div>
                    <div class="info-item">
                        <h4>Account Created</h4>
                        <p><?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                    </div>
                    <div class="info-item">
                        <h4>Account Type</h4>
                        <p><?php echo $user['role'] == 'admin' ? 'Administrator' : 'Teacher'; ?></p>
                    </div>
                </div>
                
                <div style="margin-top: 20px;">
                    <button class="btn btn-secondary" onclick="logout()">
                        <i class="fa-solid fa-sign-out-alt"></i> Logout
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/scripts.js"></script>
    <script>
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        }
        
        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (newPassword !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
        
        document.getElementById('new_password').addEventListener('input', function() {
            const confirmPassword = document.getElementById('confirm_password');
            if (confirmPassword.value) {
                if (this.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Passwords do not match');
                } else {
                    confirmPassword.setCustomValidity('');
                }
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>
