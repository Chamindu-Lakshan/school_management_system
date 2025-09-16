<?php
session_start();
include 'config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.html");
    exit;
}

// Current and next academic year
$current_year = date('Y');
$next_year = $current_year + 1;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['advance_grades'])) {
    // Begin transaction for data consistency
    $conn->begin_transaction();

    try {
        // Get all active grades for the current year
        $grade_sql = "SELECT id, grade_number, class_name FROM grades WHERE year = ? AND status = 'active'";
        $grade_stmt = $conn->prepare($grade_sql);
        $grade_stmt->bind_param("i", $current_year);
        $grade_stmt->execute();
        $grade_result = $grade_stmt->get_result();

        $total_students_advanced = 0;
        $total_grades_created = 0;

        while ($grade = $grade_result->fetch_assoc()) {
            $current_grade_id = $grade['id'];
            $current_grade_number = $grade['grade_number'];
            $class_name = $grade['class_name'];
            $next_grade_number = $current_grade_number + 1;

            // Skip if the next grade is beyond the maximum (e.g., Grade 13)
            if ($next_grade_number > 13) {
                continue; // Handle graduating students separately if needed
            }

            // Check if the next grade exists for the next year
            $check_grade_sql = "SELECT id FROM grades WHERE grade_number = ? AND class_name = ? AND year = ?";
            $check_grade_stmt = $conn->prepare($check_grade_sql);
            $check_grade_stmt->bind_param("isi", $next_grade_number, $class_name, $next_year);
            $check_grade_stmt->execute();
            $check_grade_result = $check_grade_stmt->get_result();

            if ($check_grade_result->num_rows == 0) {
                // Create the next grade if it doesn't exist
                $insert_grade_sql = "INSERT INTO grades (grade_number, class_name, year, status) VALUES (?, ?, ?, 'active')";
                $insert_grade_stmt = $conn->prepare($insert_grade_sql);
                $insert_grade_stmt->bind_param("isi", $next_grade_number, $class_name, $next_year);
                $insert_grade_stmt->execute();
                $next_grade_id = $conn->insert_id;
                $insert_grade_stmt->close();
                $total_grades_created++;
            } else {
                $next_grade = $check_grade_result->fetch_assoc();
                $next_grade_id = $next_grade['id'];
            }
            $check_grade_stmt->close();

            // Get students in the current grade
            $student_sql = "SELECT student_id FROM student_grades WHERE grade_id = ? AND status = 'active'";
            $student_stmt = $conn->prepare($student_sql);
            $student_stmt->bind_param("i", $current_grade_id);
            $student_stmt->execute();
            $student_result = $student_stmt->get_result();

            while ($student = $student_result->fetch_assoc()) {
                $student_id = $student['student_id'];

                // Check if student is already assigned to a grade in the next year
                $check_student_sql = "SELECT id FROM student_grades WHERE student_id = ? AND grade_id IN (SELECT id FROM grades WHERE year = ?)";
                $check_student_stmt = $conn->prepare($check_student_sql);
                $check_student_stmt->bind_param("ii", $student_id, $next_year);
                $check_student_stmt->execute();
                $check_student_result = $check_student_stmt->get_result();

                if ($check_student_result->num_rows == 0) {
                    // Assign student to the next grade
                    $insert_student_sql = "INSERT INTO student_grades (student_id, grade_id, enrolled_date, status) VALUES (?, ?, ?, 'active')";
                    $insert_student_stmt = $conn->prepare($insert_student_sql);
                    $enrolled_date = date('Y-m-d');
                    $insert_student_stmt->bind_param("iis", $student_id, $next_grade_id, $enrolled_date);
                    $insert_student_stmt->execute();
                    $insert_student_stmt->close();

                    // Mark old assignment as inactive
                    $update_old_sql = "UPDATE student_grades SET status = 'inactive' WHERE student_id = ? AND grade_id = ?";
                    $update_old_stmt = $conn->prepare($update_old_sql);
                    $update_old_stmt->bind_param("ii", $student_id, $current_grade_id);
                    $update_old_stmt->execute();
                    $update_old_stmt->close();

                    $total_students_advanced++;
                }
                $check_student_result->close();
            }
            $student_stmt->close();
        }
        $grade_stmt->close();

        // Commit transaction
        $conn->commit();
        $success_message = "Grade advancement completed successfully! $total_students_advanced students advanced to the next grade for $next_year. $total_grades_created new grades created.";
    } catch (Exception $e) {
        $conn->rollback();
        $error_message = "Error advancing grades: " . $e->getMessage();
    }
}

// Get statistics for display
$current_year_stats = array();
$next_year_stats = array();

// Get current year statistics
$current_stats_sql = "SELECT g.grade_number, g.class_name, COUNT(sg.student_id) as student_count 
                      FROM grades g 
                      LEFT JOIN student_grades sg ON g.id = sg.grade_id AND sg.status = 'active'
                      WHERE g.year = ? AND g.status = 'active'
                      GROUP BY g.id, g.grade_number, g.class_name
                      ORDER BY g.grade_number, g.class_name";
$current_stmt = $conn->prepare($current_stats_sql);
$current_stmt->bind_param("i", $current_year);
$current_stmt->execute();
$current_result = $current_stmt->get_result();
while ($row = $current_result->fetch_assoc()) {
    $current_year_stats[] = $row;
}
$current_stmt->close();

// Get next year statistics
$next_stats_sql = "SELECT g.grade_number, g.class_name, COUNT(sg.student_id) as student_count 
                    FROM grades g 
                    LEFT JOIN student_grades sg ON g.id = sg.grade_id AND sg.status = 'active'
                    WHERE g.year = ? AND g.status = 'active'
                    GROUP BY g.id, g.grade_number, g.class_name
                    ORDER BY g.grade_number, g.class_name";
$next_stmt = $conn->prepare($next_stats_sql);
$next_stmt->bind_param("i", $next_year);
$next_stmt->execute();
$next_result = $next_stmt->get_result();
while ($row = $next_result->fetch_assoc()) {
    $next_year_stats[] = $row;
}
$next_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advance Grades - SchoolSync</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="../js/scripts.js"></script>
    <style>
        .stats-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .stats-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .stats-card h3 {
            margin: 0 0 15px 0;
            color: #1e3a8a;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 10px;
        }
        .grade-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .grade-item:last-child {
            border-bottom: none;
        }
        .grade-info {
            font-weight: 500;
        }
        .student-count {
            background: #dbeafe;
            color: #1e40af;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 600;
        }
        .advance-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            text-align: center;
            margin-bottom: 30px;
        }
        .advance-card h3 {
            color: #1e3a8a;
            margin-bottom: 15px;
        }
        .advance-card p {
            color: #6b7280;
            margin-bottom: 25px;
            line-height: 1.6;
        }
        .btn-advance {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .btn-advance:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
        }
        .btn-advance:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        .warning-box {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            color: #92400e;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .warning-box i {
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <h1><i class="fa-solid fa-arrow-up"></i> Advance Grades to Next Academic Year</h1>

        <?php if (isset($success_message)): ?>
        <div class="alert alert-success">
            <i class="fa-solid fa-check-circle"></i>
            <?php echo htmlspecialchars($success_message); ?>
        </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
        <div class="alert alert-error">
            <i class="fa-solid fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error_message); ?>
        </div>
        <?php endif; ?>

        <div class="advance-card">
            <h3>Advance Students to <?php echo $next_year; ?></h3>
            <p>This will automatically move all active students to the next grade level for the academic year <?php echo $next_year; ?>. 
               For example, students in Grade 1 will be assigned to Grade 2, and their previous assignments will be marked as inactive to preserve historical data.</p>
            
            <div class="warning-box">
                <i class="fa-solid fa-exclamation-triangle"></i>
                <strong>Important:</strong> This action cannot be undone. Please review the current and next year statistics below before proceeding.
            </div>

            <form method="POST" onsubmit="return confirmAdvance()">
                <input type="hidden" name="advance_grades" value="1">
                <button type="submit" class="btn-advance">
                    <i class="fa-solid fa-arrow-right"></i> 
                    Advance Grades to <?php echo $next_year; ?>
                </button>
            </form>
        </div>

        <div class="stats-container">
            <div class="stats-card">
                <h3><i class="fa-solid fa-calendar"></i> Current Year (<?php echo $current_year; ?>) Statistics</h3>
                <?php if (empty($current_year_stats)): ?>
                    <p style="color: #6b7280; text-align: center; padding: 20px;">No grades found for <?php echo $current_year; ?></p>
                <?php else: ?>
                    <?php foreach ($current_year_stats as $grade): ?>
                        <div class="grade-item">
                            <span class="grade-info">Grade <?php echo $grade['grade_number']; ?> - Class <?php echo $grade['class_name']; ?></span>
                            <span class="student-count"><?php echo $grade['student_count']; ?> students</span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="stats-card">
                <h3><i class="fa-solid fa-calendar-plus"></i> Next Year (<?php echo $next_year; ?>) Statistics</h3>
                <?php if (empty($next_year_stats)): ?>
                    <p style="color: #6b7280; text-align: center; padding: 20px;">No grades found for <?php echo $next_year; ?></p>
                <?php else: ?>
                    <?php foreach ($next_year_stats as $grade): ?>
                        <div class="grade-item">
                            <span class="grade-info">Grade <?php echo $grade['grade_number']; ?> - Class <?php echo $grade['class_name']; ?></span>
                            <span class="student-count"><?php echo $grade['student_count']; ?> students</span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <h3><i class="fa-solid fa-info-circle"></i> How It Works</h3>
            <ul style="line-height: 1.8; color: #4b5563;">
                <li><strong>Grade Creation:</strong> If Grade 2 Class A doesn't exist for <?php echo $next_year; ?>, it will be automatically created.</li>
                <li><strong>Student Advancement:</strong> Students currently in Grade 1 Class A will be moved to Grade 2 Class A for <?php echo $next_year; ?>.</li>
                <li><strong>Historical Data:</strong> Previous grade assignments are marked as 'inactive' but preserved for reporting purposes.</li>
                <li><strong>Maximum Grade:</strong> Students in Grade 13 will not be advanced (they are considered graduates).</li>
                <li><strong>Duplicate Prevention:</strong> Students already assigned to a grade in <?php echo $next_year; ?> will not be moved.</li>
            </ul>
        </div>
    </div>

    <script>
        function confirmAdvance() {
            return confirm('Are you sure you want to advance all students to the next grade for <?php echo $next_year; ?>? This action cannot be undone.');
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
