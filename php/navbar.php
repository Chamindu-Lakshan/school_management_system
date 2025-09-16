<?php
// Modern, light-gradient navbar with Font Awesome icons and new logo
?>
<div class="sidebar">
    <div class="logo">
        <img src="../images/logo.svg" alt="Logo">
        <span>SchoolSync</span>
    </div>
    <a href="dashboard.php" class="<?php echo (basename($_SERVER['PHP_SELF'])=='dashboard.php') ? 'active' : ''; ?>"><i class="fa-solid fa-gauge"></i> <span class="text">Dashboard</span></a>
    <a href="subject_manage.php" class="<?php echo (basename($_SERVER['PHP_SELF'])=='subject_manage.php') ? 'active' : ''; ?>"><i class="fa-solid fa-book"></i> <span class="text">Manage Subjects</span></a>
    <a href="student_manage.php" class="<?php echo (basename($_SERVER['PHP_SELF'])=='student_manage.php') ? 'active' : ''; ?>"><i class="fa-solid fa-users"></i> <span class="text">Manage Students</span></a>
    <a href="assign_grade.php" class="<?php echo (basename($_SERVER['PHP_SELF'])=='assign_grade.php') ? 'active' : ''; ?>"><i class="fa-solid fa-pen"></i> <span class="text">Assign Grades</span></a>
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <a href="advance_grades.php" class="<?php echo (basename($_SERVER['PHP_SELF'])=='advance_grades.php') ? 'active' : ''; ?>"><i class="fa-solid fa-arrow-up"></i> <span class="text">Advance Grades</span></a>
    <?php endif; ?>
    <a href="enroll_subject.php" class="<?php echo (basename($_SERVER['PHP_SELF'])=='enroll_subject.php') ? 'active' : ''; ?>"><i class="fa-solid fa-user-plus"></i> <span class="text">Enroll to Subjects</span></a>
    <a href="test_marks.php" class="<?php echo (basename($_SERVER['PHP_SELF'])=='test_marks.php') ? 'active' : ''; ?>"><i class="fa-solid fa-chart-line"></i> <span class="text">Test Marks</span></a>
    <a href="report_cards.php" class="<?php echo (basename($_SERVER['PHP_SELF'])=='report_cards.php') ? 'active' : ''; ?>"><i class="fa-solid fa-file-lines"></i> <span class="text">Report Cards</span></a>
    <a href="class_stats.php" class="<?php echo (basename($_SERVER['PHP_SELF'])=='class_stats.php') ? 'active' : ''; ?>"><i class="fa-solid fa-chart-bar"></i> <span class="text">Class Stats</span></a>
    <a href="subject_stats.php" class="<?php echo (basename($_SERVER['PHP_SELF'])=='subject_stats.php') ? 'active' : ''; ?>"><i class="fa-solid fa-chart-pie"></i> <span class="text">Subject Stats</span></a>
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
        <a href="admin_teachers.php" class="<?php echo (basename($_SERVER['PHP_SELF'])=='admin_teachers.php') ? 'active' : ''; ?>"><i class="fa-solid fa-user-tie"></i> <span class="text">Manage Teachers</span></a>
    <?php endif; ?>
    <a href="profile.php" class="<?php echo (basename($_SERVER['PHP_SELF'])=='profile.php') ? 'active' : ''; ?>"><i class="fa-solid fa-user-circle"></i> <span class="text">Profile</span></a>
</div>

<div class="top-bar">
    <span class="menu-toggle"><i class="fa-solid fa-bars"></i></span>
    <div class="welcome">
        <img src="../images/profile-avatar.png" class="avatar" alt="Profile" onerror="this.src='../images/logo.svg'">
        <a href="profile.php" style="color: inherit; text-decoration: none;">
            Welcome, <?php echo isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : 'Admin'; ?>
        </a>
    </div>
    <button class="logout-btn" onclick="window.location.href='logout.php'"><i class="fa-solid fa-right-from-bracket"></i> Logout</button>
</div>
