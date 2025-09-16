<?php
include 'config.php';
if (!isset($_SESSION['loggedin'])) {
    header("Location: ../index.html");
    exit;
}
$username = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SchoolSync - Dashboard</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../js/scripts.js"></script>
    <style>
        h1 {
            color: #6366f1;
            margin-bottom: 24px;
        }
        .card-grid {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
        }
        .dashboard-card {
            min-height: 160px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: space-between;
            gap: 15px;
            padding: 24px;
            border-radius: 16px;
            text-decoration: none;
            color: inherit;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .dashboard-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6, #8b5cf6);
        }
        .dashboard-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 32px rgba(59,130,246,0.15);
        }
        .card-header {
            display: flex;
            align-items: center;
            gap: 15px;
            width: 100%;
        }
        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
        }
        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1e3a8a;
            margin: 0;
        }
        .card-description {
            color: #64748b;
            font-size: 0.9rem;
            line-height: 1.5;
            margin: 0;
        }
        .card-footer {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #3b82f6;
            font-size: 0.85rem;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- Main Content -->
    <div class="container">
        <h1>Dashboard Overview</h1>
        <div class="card-grid">

            <a href="subject_manage.php" class="dashboard-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fa-solid fa-book-open"></i>
                    </div>
                    <h3 class="card-title">Manage Subjects</h3>
                </div>
                <p class="card-description">Add, edit, and remove subjects. View subject statistics and manage curriculum.</p>
                <div class="card-footer">
                    <span>Manage Curriculum</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </div>
            </a>

            <a href="student_manage.php" class="dashboard-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <h3 class="card-title">Manage Students</h3>
                </div>
                <p class="card-description">Add, edit, and remove students. View detailed student profiles and information.</p>
                <div class="card-footer">
                    <span>Student Database</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </div>
            </a>

            <a href="assign_grade.php" class="dashboard-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <h3 class="card-title">Assign Grades</h3>
                </div>
                <p class="card-description">Assign students to grades and classes. Manage class structures and enrollments.</p>
                <div class="card-footer">
                    <span>Class Management</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </div>
            </a>

            <a href="enroll_subject.php" class="dashboard-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <h3 class="card-title">Enroll to Subjects</h3>
                </div>
                <p class="card-description">Enroll students in subjects. Manage subject enrollments and course selections.</p>
                <div class="card-footer">
                    <span>Course Enrollment</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </div>
            </a>

            <a href="test_marks.php" class="dashboard-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <h3 class="card-title">Test Marks</h3>
                </div>
                <p class="card-description">Add, edit, and manage test marks for different subjects, grades, and terms.</p>
                <div class="card-footer">
                    <span>Grade Management</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </div>
            </a>

            <a href="report_cards.php" class="dashboard-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fa-solid fa-file-lines"></i>
                    </div>
                    <h3 class="card-title">Report Cards</h3>
                </div>
                <p class="card-description">Generate and view comprehensive student report cards with grades and performance analysis.</p>
                <div class="card-footer">
                    <span>Academic Reports</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </div>
            </a>

            <a href="class_stats.php" class="dashboard-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fa-solid fa-chart-bar"></i>
                    </div>
                    <h3 class="card-title">Class Statistics</h3>
                </div>
                <p class="card-description">View detailed class statistics, student rankings, and performance analytics by grade and class.</p>
                <div class="card-footer">
                    <span>Class Analytics</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </div>
            </a>

            <a href="subject_stats.php" class="dashboard-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                    <h3 class="card-title">Subject Statistics</h3>
                </div>
                <p class="card-description">Analyze subject performance across different grades and terms with detailed statistics.</p>
                <div class="card-footer">
                    <span>Subject Analytics</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </div>
            </a>

            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="admin_teachers.php" class="dashboard-card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>
                    <h3 class="card-title">Manage Teachers</h3>
                </div>
                <p class="card-description">Add new teachers and manage existing teacher accounts. Admin-only feature.</p>
                <div class="card-footer">
                    <span>Teacher Management</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </div>
            </a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>
