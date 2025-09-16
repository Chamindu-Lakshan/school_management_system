<?php
include 'config.php';
if (!isset($_SESSION['loggedin'])) header("Location: ../index.html");

// Handle mark insertion
if (isset($_POST['insert_mark'])) {
    $student_id = (int)$_POST['student_id'];
    $subject_id = (int)$_POST['subject_id'];
    $grade_id = (int)$_POST['grade_id'];
    $year = (int)$_POST['year'];
    $term = $_POST['term'];
    $mark = (float)$_POST['mark'];
    
    // Determine grade letter
    $grade_letter = 'F';
    if ($mark >= 75) $grade_letter = 'A';
    elseif ($mark >= 60) $grade_letter = 'B';
    elseif ($mark >= 40) $grade_letter = 'S';
    
    // Check if mark already exists
    $check_sql = "SELECT id FROM marks WHERE student_id = ? AND subject_id = ? AND grade_id = ? AND year = ? AND term = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("iiiss", $student_id, $subject_id, $grade_id, $year, $term);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Update existing mark
        $update_sql = "UPDATE marks SET mark = ?, grade_letter = ?, updated_at = CURRENT_TIMESTAMP WHERE student_id = ? AND subject_id = ? AND grade_id = ? AND year = ? AND term = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("dsiiiss", $mark, $grade_letter, $student_id, $subject_id, $grade_id, $year, $term);
        $update_stmt->execute();
        $update_stmt->close();
        $message = "Mark updated successfully!";
    } else {
        // Insert new mark
        $insert_sql = "INSERT INTO marks (student_id, subject_id, grade_id, year, term, mark, grade_letter, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $remarks = "Mark entered for " . $term . " term";
        $insert_stmt->bind_param("iiissds", $student_id, $subject_id, $grade_id, $year, $term, $mark, $grade_letter, $remarks);
        $insert_stmt->execute();
        $insert_stmt->close();
        $message = "Mark inserted successfully!";
    }
    $check_stmt->close();
}

// Get current year as default
$current_year = date('Y');
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : $current_year;
$selected_grade = isset($_GET['grade']) ? (int)$_GET['grade'] : 0;
$selected_class = isset($_GET['class']) ? $_GET['class'] : '';
$selected_subject = isset($_GET['subject']) ? (int)$_GET['subject'] : 0;

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

// Get classes for selected year and grade
$classes = array();
if ($selected_year && $selected_grade) {
    $class_sql = "SELECT DISTINCT class_name FROM grades WHERE year = ? AND grade_number = ? ORDER BY class_name";
    $class_stmt = $conn->prepare($class_sql);
    $class_stmt->bind_param("ii", $selected_year, $selected_grade);
    $class_stmt->execute();
    $class_result = $class_stmt->get_result();
    while ($row = $class_result->fetch_assoc()) {
        $classes[] = $row['class_name'];
    }
    $class_stmt->close();
}

// Get subjects
$subjects = array();
$subject_sql = "SELECT id, name FROM subjects WHERE status = 'active' ORDER BY name";
$subject_result = $conn->query($subject_sql);
while ($row = $subject_result->fetch_assoc()) {
    $subjects[] = $row;
}

// Get students for selected year, grade, and class
$students = array();
if ($selected_year && $selected_grade && $selected_class) {
    $student_sql = "SELECT DISTINCT s.id, s.full_name, s.gender, s.image_path 
                    FROM students s 
                    JOIN student_grades sg ON s.id = sg.student_id 
                    JOIN grades g ON sg.grade_id = g.id 
                    WHERE g.year = ? AND g.grade_number = ? AND g.class_name = ? AND sg.status = 'active'
                    ORDER BY s.full_name";
    $student_stmt = $conn->prepare($student_sql);
    $student_stmt->bind_param("iis", $selected_year, $selected_grade, $selected_class);
    $student_stmt->execute();
    $student_result = $student_stmt->get_result();
    while ($row = $student_result->fetch_assoc()) {
        $students[] = $row;
    }
    $student_stmt->close();
}

// Get existing marks for selected criteria
$existing_marks = array();
if ($selected_year && $selected_grade && $selected_class && $selected_subject) {
    $marks_sql = "SELECT m.student_id, m.term, m.mark, m.grade_letter 
                  FROM marks m 
                  JOIN student_grades sg ON m.student_id = sg.student_id 
                  JOIN grades g ON sg.grade_id = g.id 
                  WHERE g.year = ? AND g.grade_number = ? AND g.class_name = ? AND m.subject_id = ? AND m.year = ?
                  ORDER BY m.student_id, m.term";
    $marks_stmt = $conn->prepare($marks_sql);
    $marks_stmt->bind_param("iisis", $selected_year, $selected_grade, $selected_class, $selected_subject, $selected_year);
    $marks_stmt->execute();
    $marks_result = $marks_stmt->get_result();
    while ($row = $marks_result->fetch_assoc()) {
        $existing_marks[$row['student_id']][$row['term']] = array(
            'mark' => $row['mark'],
            'grade' => $row['grade_letter']
        );
    }
    $marks_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Test Marks - SchoolSync</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .marks-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .marks-table th {
            background: #1e3a8a;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        .marks-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        .marks-table tr:hover {
            background: #f8fafc;
        }
        .mark-input {
            width: 80px;
            padding: 8px;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            text-align: center;
        }
        .grade-display {
            display: inline-block;
            width: 30px;
            height: 30px;
            line-height: 30px;
            text-align: center;
            border-radius: 50%;
            font-weight: bold;
            font-size: 12px;
            color: white;
        }
        .grade-a { background: #10b981; }
        .grade-b { background: #3b82f6; }
        .grade-s { background: #f59e0b; }
        .grade-f { background: #ef4444; }
        .student-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .student-avatar {
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
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
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
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <div class="header-container">
            <h1><i class="fa-solid fa-chart-line"></i> Manage Test Marks</h1>
        </div>

        <?php if (isset($message)): ?>
            <div class="message">
                <i class="fa-solid fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

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

                <div class="selection-group">
                    <label for="class">Class</label>
                    <select name="class" id="class" onchange="this.form.submit()" <?php if (!$selected_grade) echo 'disabled'; ?>>
                        <option value="">Select Class</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class; ?>" <?php if ($selected_class == $class) echo 'selected'; ?>>
                                Class <?php echo $class; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="selection-group">
                    <label for="subject">Subject</label>
                    <select name="subject" id="subject" onchange="this.form.submit()" <?php if (!$selected_class) echo 'disabled'; ?>>
                        <option value="">Select Subject</option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?php echo $subject['id']; ?>" <?php if ($selected_subject == $subject['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($subject['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>

        <!-- Marks Entry Table -->
        <?php if ($selected_year && $selected_grade && $selected_class && $selected_subject && !empty($students)): ?>
            <div class="card">
                <h3><i class="fa-solid fa-edit"></i> Enter Marks for <?php echo htmlspecialchars($subjects[array_search($selected_subject, array_column($subjects, 'id'))]['name']); ?></h3>
                <p class="text-muted">Grade <?php echo $selected_grade; ?> Class <?php echo $selected_class; ?> - Year <?php echo $selected_year; ?></p>
                
                <form method="POST">
                    <input type="hidden" name="year" value="<?php echo $selected_year; ?>">
                    <input type="hidden" name="grade_id" value="<?php 
                        $grade_id_sql = "SELECT id FROM grades WHERE year = ? AND grade_number = ? AND class_name = ?";
                        $grade_id_stmt = $conn->prepare($grade_id_sql);
                        $grade_id_stmt->bind_param("iis", $selected_year, $selected_grade, $selected_class);
                        $grade_id_stmt->execute();
                        $grade_id_result = $grade_id_stmt->get_result();
                        if ($grade_id_row = $grade_id_result->fetch_assoc()) {
                            echo $grade_id_row['id'];
                        }
                        $grade_id_stmt->close();
                    ?>">
                    <input type="hidden" name="subject_id" value="<?php echo $selected_subject; ?>">
                    
                    <table class="marks-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>1st Term</th>
                                <th>2nd Term</th>
                                <th>3rd Term</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td>
                                        <div class="student-info">
                                            <div class="student-avatar">
                                                <?php if ($student['image_path']): ?>
                                                    <img src="<?php echo htmlspecialchars($student['image_path']); ?>" alt="Student Photo" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                                                <?php else: ?>
                                                    <i class="fa-solid fa-user"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <strong><?php echo htmlspecialchars($student['full_name']); ?></strong>
                                                <br><small><?php echo ucfirst($student['gender']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" name="mark_<?php echo $student['id']; ?>_1st" 
                                               class="mark-input" placeholder="Mark" min="0" max="100" step="0.01"
                                               value="<?php echo isset($existing_marks[$student['id']]['1st']) ? $existing_marks[$student['id']]['1st']['mark'] : ''; ?>">
                                        <?php if (isset($existing_marks[$student['id']]['1st'])): ?>
                                            <span class="grade-display grade-<?php echo strtolower($existing_marks[$student['id']]['1st']['grade']); ?>">
                                                <?php echo $existing_marks[$student['id']]['1st']['grade']; ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <input type="number" name="mark_<?php echo $student['id']; ?>_2nd" 
                                               class="mark-input" placeholder="Mark" min="0" max="100" step="0.01"
                                               value="<?php echo isset($existing_marks[$student['id']]['2nd']) ? $existing_marks[$student['id']]['2nd']['mark'] : ''; ?>">
                                        <?php if (isset($existing_marks[$student['id']]['2nd'])): ?>
                                            <span class="grade-display grade-<?php echo strtolower($existing_marks[$student['id']]['2nd']['grade']); ?>">
                                                <?php echo $existing_marks[$student['id']]['2nd']['grade']; ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <input type="number" name="mark_<?php echo $student['id']; ?>_3rd" 
                                               class="mark-input" placeholder="Mark" min="0" max="100" step="0.01"
                                               value="<?php echo isset($existing_marks[$student['id']]['3rd']) ? $existing_marks[$student['id']]['3rd']['mark'] : ''; ?>">
                                        <?php if (isset($existing_marks[$student['id']]['3rd'])): ?>
                                            <span class="grade-display grade-<?php echo strtolower($existing_marks[$student['id']]['3rd']['grade']); ?>">
                                                <?php echo $existing_marks[$student['id']]['3rd']['grade']; ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button type="submit" name="insert_mark" class="btn btn-primary btn-sm">
                                            <i class="fa-solid fa-save"></i> Save
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </form>
            </div>
        <?php elseif ($selected_year && $selected_grade && $selected_class && $selected_subject): ?>
            <div class="card">
                <div class="no-data">
                    <i class="fa-solid fa-users-slash fa-3x"></i>
                    <h3>No Students Found</h3>
                    <p>No students are enrolled in the selected criteria.</p>
                </div>
            </div>
        <?php elseif ($selected_year && $selected_grade && $selected_class): ?>
            <div class="card">
                <div class="no-data">
                    <i class="fa-solid fa-book fa-3x"></i>
                    <h3>Select Subject</h3>
                    <p>Please select a subject to manage marks.</p>
                </div>
            </div>
        <?php elseif ($selected_year && $selected_grade): ?>
            <div class="card">
                <div class="no-data">
                    <i class="fa-solid fa-chalkboard fa-3x"></i>
                    <h3>Select Class</h3>
                    <p>Please select a class to continue.</p>
                </div>
            </div>
        <?php elseif ($selected_year): ?>
            <div class="card">
                <div class="no-data">
                    <i class="fa-solid fa-graduation-cap fa-3x"></i>
                    <h3>Select Grade</h3>
                    <p>Please select a grade to continue.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="no-data">
                    <i class="fa-solid fa-calendar fa-3x"></i>
                    <h3>Select Academic Year</h3>
                    <p>Please select an academic year to start managing marks.</p>
                </div>
            </div>
        <?php endif; ?>
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
    </script>
</body>
</html>
<?php $conn->close(); ?>
