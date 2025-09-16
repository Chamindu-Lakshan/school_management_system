<?php
include 'config.php';
if (!isset($_SESSION['loggedin'])) header("Location: ../index.html");

// Get current year as default
$current_year = date('Y');
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : $current_year;
$selected_grade = isset($_GET['grade']) ? (int)$_GET['grade'] : 6; // Default to Grade 6

// If this is the initial page load with default values, redirect to show the data
if (!isset($_GET['year']) && !isset($_GET['grade'])) {
    $redirect_url = "report_cards.php?year=" . $selected_year . "&grade=" . $selected_grade;
    header("Location: " . $redirect_url);
    exit();
}

// Get available years
$years = array();
for ($i = $current_year - 5; $i <= $current_year + 5; $i++) {
    $years[] = $i;
}

// Get grades for selected year
$grades = array();
if ($selected_year) {
    $grade_sql = "SELECT DISTINCT grade_number FROM grades WHERE year = ? ORDER BY grade_number";
    $grade_stmt = $conn->prepare($grade_sql);
    $grade_stmt->bind_param("i", $selected_year);
    $grade_stmt->execute();
    $grade_result = $grade_stmt->get_result();
    while ($row = $grade_result->fetch_assoc()) {
        $grades[] = $row['grade_number'];
    }
    $grade_stmt->close();
}

// Get students for selected year and grade
$students = array();
if ($selected_year && $selected_grade) {
    $student_sql = "SELECT DISTINCT s.id, s.full_name, s.gender, s.birth_date, s.address, s.phone, s.email, s.religion, s.father_name, s.mother_name, s.guardian_name, s.guardian_phone, s.guardian_email, s.special_details, s.status, s.image_path, s.created_at 
                    FROM students s 
                    JOIN student_grades sg ON s.id = sg.student_id 
                    JOIN grades g ON sg.grade_id = g.id 
                    WHERE g.year = ? AND g.grade_number = ? AND sg.status = 'active'
                    ORDER BY s.full_name";
    $student_stmt = $conn->prepare($student_sql);
    $student_stmt->bind_param("ii", $selected_year, $selected_grade);
    $student_stmt->execute();
    $student_result = $student_stmt->get_result();
    while ($row = $student_result->fetch_assoc()) {
        $students[] = $row;
    }
    $student_stmt->close();
}

// Function to get grade letter
function getGradeLetter($mark) {
    if ($mark >= 75) return 'A';
    if ($mark >= 60) return 'B';
    if ($mark >= 40) return 'S';
    return 'F';
}

// Function to get grade description
function getGradeDescription($grade_letter) {
    switch ($grade_letter) {
        case 'A': return 'Excellent';
        case 'B': return 'Good';
        case 'S': return 'Satisfactory';
        case 'F': return 'Fail';
        default: return 'N/A';
    }
}

// Function to determine report card format
function getReportCardFormat($grade) {
    if ($grade >= 1 && $grade <= 5) return 'primary';
    if ($grade >= 6 && $grade <= 9) return 'junior';
    if ($grade >= 10 && $grade <= 11) return 'senior';
    if ($grade >= 12 && $grade <= 13) return 'advanced';
    return 'primary';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Report Cards - SchoolSync</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .students-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .student-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        .student-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
        }
        .student-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f1f5f9;
        }
        .student-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: bold;
        }
        .student-info h3 {
            margin: 0 0 5px 0;
            color: #1e3a8a;
            font-size: 18px;
        }
        .student-info p {
            margin: 0;
            color: #64748b;
            font-size: 14px;
        }
        .report-card-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .report-card-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
        }
        .report-card-btn.primary { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .report-card-btn.junior { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
        .report-card-btn.senior { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); }
        .report-card-btn.advanced { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #64748b;
        }
        .no-data i {
            font-size: 3rem;
            margin-bottom: 20px;
            display: block;
        }
        .format-indicator {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            color: white;
            margin-left: 10px;
        }
        .format-primary { background: #10b981; }
        .format-junior { background: #3b82f6; }
        .format-senior { background: #8b5cf6; }
        .format-advanced { background: #f59e0b; }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: #000;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <div class="header-container">
            <h1><i class="fa-solid fa-file-alt"></i> Student Report Cards</h1>
        </div>

        <!-- Selection Form -->
        <div class="card">
            <h3><i class="fa-solid fa-filter"></i> Select Criteria</h3>
            <form method="GET" class="selection-container">
                <div class="selection-group">
                    <label for="year">Academic Year</label>
                    <select name="year" id="year" onchange="this.form.submit()">
                        <option value="">Select Year</option>
                        <?php foreach ($years as $year): ?>
                            <option value="<?php echo $year; ?>" <?php if ($selected_year == $year) echo 'selected'; ?>>
                                <?php echo $year; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="selection-group">
                    <label for="grade">Grade</label>
                    <select name="grade" id="grade" onchange="this.form.submit()" <?php if (!$selected_year) echo 'disabled'; ?>>
                        <option value="">Select Grade</option>
                        <?php foreach ($grades as $grade): ?>
                            <option value="<?php echo $grade; ?>" <?php if ($selected_grade == $grade) echo 'selected'; ?>>
                                Grade <?php echo $grade; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>

        <!-- Students List -->
        <?php if ($selected_year && $selected_grade && !empty($students)): ?>
            <div class="card">
                <h3><i class="fa-solid fa-users"></i> Students - Grade <?php echo $selected_grade; ?> (Year <?php echo $selected_year; ?>)</h3>
                <p class="text-muted">Click on a student to view their report card</p>
                
                <div class="students-grid">
                    <?php foreach ($students as $student): ?>
                        <?php 
                        $format = getReportCardFormat($selected_grade);
                        $format_class = 'format-' . $format;
                        ?>
                        <div class="student-card">
                            <div class="student-header">
                                <div class="student-avatar">
                                    <?php if ($student['image_path']): ?>
                                        <img src="<?php echo htmlspecialchars($student['image_path']); ?>" alt="Student Photo" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                                    <?php else: ?>
                                        <i class="fa-solid fa-user"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="student-info">
                                    <h3><?php echo htmlspecialchars($student['full_name']); ?></h3>
                                    <p>ID: <?php echo $student['id']; ?> | <?php echo ucfirst($student['gender']); ?></p>
                                    <p><?php echo $student['birth_date']; ?></p>
                                    <span class="format-indicator <?php echo $format_class; ?>">
                                        <?php echo ucfirst($format); ?> Level
                                    </span>
                                </div>
                            </div>
                            
                            <button class="report-card-btn <?php echo $format; ?>" onclick="viewReportCard(<?php echo $student['id']; ?>, <?php echo $selected_grade; ?>, <?php echo $selected_year; ?>)">
                                <i class="fa-solid fa-eye"></i>
                                View Report Card
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php elseif ($selected_year && $selected_grade): ?>
            <div class="card">
                <div class="no-data">
                    <i class="fa-solid fa-users-slash fa-3x"></i>
                    <h3>No Students Found</h3>
                    <p>No students are enrolled in Grade <?php echo $selected_grade; ?> for the year <?php echo $selected_year; ?>.</p>
                </div>
            </div>
        <?php elseif ($selected_year): ?>
            <div class="card">
                <div class="no-data">
                    <i class="fa-solid fa-graduation-cap fa-3x"></i>
                    <h3>Select Grade</h3>
                    <p>Please select a grade to view student report cards.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="no-data">
                    <i class="fa-solid fa-calendar fa-3x"></i>
                    <h3>Select Academic Year</h3>
                    <p>Please select an academic year to start viewing report cards.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Report Card Modal -->
    <div id="reportCardModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 800px; max-height: 90vh; overflow-y: auto;">
            <span class="close" onclick="closeReportCardModal()">&times;</span>
            <div id="reportCardContent"></div>
        </div>
    </div>

    <script src="../js/scripts.js"></script>
    <script>
        // Auto-submit form when selections change
        document.querySelectorAll('select').forEach(select => {
            select.addEventListener('change', function() {
                if (this.value) {
                    this.form.submit();
                }
            });
        });

        function viewReportCard(studentId, grade, year) {
            // Show loading
            document.getElementById('reportCardContent').innerHTML = '<div style="text-align: center; padding: 40px;"><i class="fa-solid fa-spinner fa-spin fa-2x"></i><p>Loading report card...</p></div>';
            document.getElementById('reportCardModal').style.display = 'block';
            
            // Fetch report card data
            fetch(`get_report_card.php?student_id=${studentId}&grade=${grade}&year=${year}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('reportCardContent').innerHTML = data;
                })
                .catch(error => {
                    document.getElementById('reportCardContent').innerHTML = '<div style="text-align: center; padding: 40px; color: #ef4444;"><i class="fa-solid fa-exclamation-triangle fa-2x"></i><p>Error loading report card</p></div>';
                });
        }

        function closeReportCardModal() {
            document.getElementById('reportCardModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('reportCardModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
