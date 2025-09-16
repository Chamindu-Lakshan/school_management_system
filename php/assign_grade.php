<?php
session_start();
include 'config.php';

if (!isset($_SESSION['loggedin'])) {
    header("Location: ../index.html");
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'assign_student':
    $student_id = $_POST['student_id'];
                $grade_id = $_POST['grade_id'];
                $enrolled_date = $_POST['enrolled_date'];
                
                // Check if student is already in this grade
                $check_sql = "SELECT id FROM student_grades WHERE student_id = ? AND grade_id = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("ii", $student_id, $grade_id);
                $check_stmt->execute();
                $result = $check_stmt->get_result();
                
                if ($result->num_rows == 0) {
                    $sql = "INSERT INTO student_grades (student_id, grade_id, enrolled_date) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("iis", $student_id, $grade_id, $enrolled_date);
                    $stmt->execute();
                }
                break;
                
            case 'remove_student':
                $student_grade_id = $_POST['student_grade_id'];
                $sql = "DELETE FROM student_grades WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $student_grade_id);
                $stmt->execute();
                break;
                
            case 'add_grade':
                $grade_number = (int)$_POST['grade_number'];
                $class_name = trim($_POST['class_name']);
                $year = (int)$_POST['year'];
                
                // Check if grade already exists
                $check_sql = "SELECT id FROM grades WHERE grade_number = ? AND class_name = ? AND year = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("isi", $grade_number, $class_name, $year);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows == 0) {
                    $sql = "INSERT INTO grades (grade_number, class_name, year, status) VALUES (?, ?, ?, 'active')";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("isi", $grade_number, $class_name, $year);
                    
                    if ($stmt->execute()) {
                        $success_message = "Grade $grade_number - Class $class_name added successfully for year $year!";
                    } else {
                        $error_message = "Error adding grade: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $error_message = "Grade $grade_number - Class $class_name already exists for year $year!";
                }
                $check_stmt->close();
                break;
                
            case 'edit_grade':
                $grade_id = (int)$_POST['grade_id'];
                $grade_number = (int)$_POST['grade_number'];
                $class_name = trim($_POST['class_name']);
                $year = (int)$_POST['year'];
                
                // Check if the new combination already exists (excluding current grade)
                $check_sql = "SELECT id FROM grades WHERE grade_number = ? AND class_name = ? AND year = ? AND id != ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("isii", $grade_number, $class_name, $year, $grade_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows == 0) {
                    $sql = "UPDATE grades SET grade_number = ?, class_name = ?, year = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("isii", $grade_number, $class_name, $year, $grade_id);
                    
                    if ($stmt->execute()) {
                        $success_message = "Grade updated successfully!";
                    } else {
                        $error_message = "Error updating grade: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $error_message = "Grade $grade_number - Class $class_name already exists for year $year!";
                }
                $check_stmt->close();
                break;
                
            case 'delete_grade':
                $grade_id = (int)$_POST['grade_id'];
                
                // Check if there are students in this grade
                $check_sql = "SELECT COUNT(*) as student_count FROM student_grades WHERE grade_id = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("i", $grade_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                $student_row = $check_result->fetch_assoc();
                $student_count = $student_row['student_count'];
                
                if ($student_count == 0) {
                    $sql = "DELETE FROM grades WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $grade_id);
                    
                    if ($stmt->execute()) {
                        $success_message = "Grade deleted successfully!";
                    } else {
                        $error_message = "Error deleting grade: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $error_message = "Cannot delete grade: There are $student_count student(s) currently assigned to this grade.";
                }
                $check_stmt->close();
                break;
        }
        
        // Redirect with message
        $redirect_url = "assign_grade.php" . (isset($_GET['year']) ? "?year=" . $_GET['year'] : "");
        if (isset($success_message)) {
            $redirect_url .= (strpos($redirect_url, '?') !== false ? '&' : '?') . 'success=' . urlencode($success_message);
        }
        if (isset($error_message)) {
            $redirect_url .= (strpos($redirect_url, '?') !== false ? '&' : '?') . 'error=' . urlencode($error_message);
        }
        header("Location: " . $redirect_url);
        exit;
    }
}

// Get messages from URL parameters
$success_message = isset($_GET['success']) ? $_GET['success'] : '';
$error_message = isset($_GET['error']) ? $_GET['error'] : '';

// Get current year as default
$current_year = date('Y');
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : $current_year;

// Fetch all grades for the selected year
$sql_grades = "SELECT * FROM grades WHERE year = ? ORDER BY grade_number, class_name";
$stmt_grades = $conn->prepare($sql_grades);
$stmt_grades->bind_param("i", $selected_year);
$stmt_grades->execute();
$result_grades = $stmt_grades->get_result();

// Fetch all students not assigned to any grade in the selected year
$sql_unassigned = "SELECT s.* FROM students s 
                   WHERE s.id NOT IN (
                       SELECT sg.student_id FROM student_grades sg 
                       JOIN grades g ON sg.grade_id = g.id 
                       WHERE g.year = ?
                   ) AND s.status = 'active'
                   ORDER BY s.id";
$stmt_unassigned = $conn->prepare($sql_unassigned);
$stmt_unassigned->bind_param("i", $selected_year);
$stmt_unassigned->execute();
$result_unassigned = $stmt_unassigned->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Grades - SchoolSync</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="../js/scripts.js"></script>
    <style>
        .year-selector {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .year-selector h2 {
            margin: 0 0 15px 0;
            color: #1e3a8a;
            cursor: pointer;
            display: inline-block;
            padding: 10px 20px;
            border-radius: 8px;
            transition: background 0.3s ease;
        }
        .year-selector h2:hover {
            background: #f0f9ff;
        }
        .year-selector select {
            padding: 8px 15px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 1rem;
            margin-left: 10px;
        }
        .grade-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .grade-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            cursor: pointer;
        }
        .grade-card:hover {
            transform: translateY(-4px);
        }
        .grade-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e5e7eb;
        }
        .grade-title {
            margin: 0;
            color: #1e3a8a;
            font-size: 1.3rem;
        }
        .grade-stats {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        .stat-item {
            text-align: center;
        }
        .stat-value {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1e3a8a;
        }
        .stat-label {
            font-size: 0.8rem;
            color: #64748b;
        }
        .student-list {
            max-height: 200px;
            overflow-y: auto;
        }
        .student-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .student-item:last-child {
            border-bottom: none;
        }
        .student-name {
            color: #1e3a8a;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        .student-name:hover {
            color: #3b82f6;
        }
        .remove-btn {
            background: #ef4444;
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: background 0.3s ease;
        }
        .remove-btn:hover {
            background: #dc2626;
        }
        .add-student-section {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
        }
        .add-student-form {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .add-student-form select {
            flex: 1;
            padding: 8px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.9rem;
        }
        .add-student-form button {
            padding: 8px 12px;
            background: #10b981;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background 0.3s ease;
        }
        .add-student-form button:hover {
            background: #059669;
        }
        .empty-grade {
            text-align: center;
            color: #64748b;
            font-style: italic;
            padding: 20px;
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
        .grade-actions {
            display: flex;
            gap: 8px;
        }
        .grade-actions button {
            padding: 4px 8px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: background 0.3s ease;
        }
        .grade-actions .edit-btn {
            background: #3b82f6;
            color: white;
        }
        .grade-actions .edit-btn:hover {
            background: #2563eb;
        }
        .grade-actions .delete-btn {
            background: #ef4444;
            color: white;
        }
        .grade-actions .delete-btn:hover {
            background: #dc2626;
        }
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
            <h1>Assign Grades</h1>
            <button onclick="openAddGradeModal()" style="background: linear-gradient(135deg, #10b981, #059669);">
                <i class="fa-solid fa-plus"></i> Add New Grade/Class
            </button>
        </div>

        <!-- Message Display -->
        <?php if (!empty($success_message)): ?>
            <div class="message success">
                <i class="fa-solid fa-check-circle"></i>
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="message error">
                <i class="fa-solid fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Year Selector -->
        <div class="year-selector">
            <h2 onclick="toggleYearSelector()">
                <i class="fa-solid fa-calendar"></i> 
                Year: <?php echo $selected_year; ?>
                <i class="fa-solid fa-chevron-down" id="yearArrow"></i>
            </h2>
            <div id="yearSelector" style="display: none;">
                <form method="GET" style="display: inline;">
                    <select name="year" onchange="this.form.submit()">
                        <?php for ($i = $current_year - 10; $i <= $current_year + 2; $i++) { ?>
                            <option value="<?php echo $i; ?>" <?php if ($selected_year == $i) echo 'selected'; ?>>
                                <?php echo $i; ?>
                        </option>
                    <?php } ?>
                </select>
            </form>
            </div>
        </div>

        <!-- Grade Cards Grid -->
        <div class="grade-grid">
            <?php if ($result_grades->num_rows > 0): ?>
                <?php while($grade = $result_grades->fetch_assoc()): ?>
                    <?php
                    // Get students in this grade
                    $sql_students = "SELECT sg.id as student_grade_id, s.id, s.full_name, s.gender, sg.enrolled_date 
                                   FROM student_grades sg 
                                   JOIN students s ON sg.student_id = s.id 
                                   WHERE sg.grade_id = ? AND sg.status = 'active'
                                   ORDER BY s.full_name";
                    $stmt_students = $conn->prepare($sql_students);
                    $stmt_students->bind_param("i", $grade['id']);
                    $stmt_students->execute();
                    $result_students = $stmt_students->get_result();
                    $student_count = $result_students->num_rows;
                    ?>
                    
                    <div class="grade-card" onclick="openGradeDetails(<?php echo $grade['id']; ?>)">
                        <div class="grade-header">
                            <h3 class="grade-title">
                                <i class="fa-solid fa-graduation-cap"></i>
                                Grade <?php echo $grade['grade_number']; ?> - <?php echo $grade['class_name']; ?>
                            </h3>
                            <div class="grade-actions">
                                <button class="edit-btn" onclick="event.stopPropagation(); openEditGradeModal(<?php echo $grade['id']; ?>)">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </button>
                                <button class="delete-btn" onclick="event.stopPropagation(); openDeleteGradeModal(<?php echo $grade['id']; ?>)">
                                    <i class="fa-solid fa-trash-can"></i> Delete
                                </button>
                            </div>
                        </div>
                        
                        <div class="grade-stats">
                            <div class="stat-item">
                                <div class="stat-value"><?php echo $student_count; ?></div>
                                <div class="stat-label">Students</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value"><?php echo $grade['year']; ?></div>
                                <div class="stat-label">Year</div>
                            </div>
                        </div>
                        
                        <div class="student-list">
                            <?php if ($student_count > 0): ?>
                                <?php while($student = $result_students->fetch_assoc()): ?>
                                    <div class="student-item" onclick="event.stopPropagation(); openStudentModal(<?php echo $student['id']; ?>)">
                                        <span class="student-name">
                                            <i class="fa-solid fa-user"></i>
                                            <?php echo htmlspecialchars($student['full_name']); ?>
                                        </span>
                                        <button class="remove-btn" onclick="event.stopPropagation(); removeStudent(<?php echo $student['student_grade_id']; ?>)">
                                            <i class="fa-solid fa-times"></i>
                                        </button>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="empty-grade">No students assigned</div>
                            <?php endif; ?>
                </div>

                        <div class="add-student-section">
                            <form method="POST" class="add-student-form" onclick="event.stopPropagation();">
                                <input type="hidden" name="action" value="assign_student">
                                <input type="hidden" name="grade_id" value="<?php echo $grade['id']; ?>">
                                <input type="hidden" name="enrolled_date" value="<?php echo date('Y-m-d'); ?>">
                                <select name="student_id" required>
                                    <option value="">Select Student</option>
                                    <?php 
                                    $result_unassigned->data_seek(0);
                                    while($student = $result_unassigned->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo $student['id']; ?>">
                                            ID: <?php echo $student['id']; ?> - <?php echo htmlspecialchars($student['full_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <button type="submit">
                                    <i class="fa-solid fa-plus"></i> Add
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #64748b;">
                    <i class="fa-solid fa-graduation-cap" style="font-size: 3rem; margin-bottom: 20px; display: block;"></i>
                    <h3>No Grades Found for <?php echo $selected_year; ?></h3>
                    <p>Add grades and classes to get started.</p>
                </div>
            <?php endif; ?>
        </div>
                </div>

    <!-- Student Details Modal -->
    <div id="studentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Student Details</h2>
                <span class="close" onclick="closeModal('studentModal')">&times;</span>
            </div>
            <div id="studentDetails">
                <!-- Student details will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        function toggleYearSelector() {
            const selector = document.getElementById('yearSelector');
            const arrow = document.getElementById('yearArrow');
            if (selector.style.display === 'none') {
                selector.style.display = 'block';
                arrow.style.transform = 'rotate(180deg)';
            } else {
                selector.style.display = 'none';
                arrow.style.transform = 'rotate(0deg)';
            }
        }

        function openStudentModal(id) {
            // Fetch and display student details
            fetch(`get_student.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('studentDetails').innerHTML = `
                        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px;">
                            <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #8b5cf6); display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem;">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <div>
                                <h3 style="margin: 0; color: #1e3a8a;">${data.full_name}</h3>
                                <p style="margin: 5px 0; color: #64748b;">Student ID: ${data.id}</p>
                                <p style="margin: 5px 0; color: #64748b;">${data.gender} • ${data.status}</p>
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div>
                                <h4 style="color: #1e3a8a; margin-bottom: 10px;">Personal Information</h4>
                                <p><strong>Birth Date:</strong> ${data.birth_date}</p>
                                <p><strong>Address:</strong> ${data.address || 'Not provided'}</p>
                                <p><strong>Phone:</strong> ${data.phone || 'Not provided'}</p>
                                <p><strong>Email:</strong> ${data.email || 'Not provided'}</p>
                                <p><strong>Religion:</strong> ${data.religion || 'Not provided'}</p>
                            </div>
                            <div>
                                <h4 style="color: #1e3a8a; margin-bottom: 10px;">Family Information</h4>
                                <p><strong>Father:</strong> ${data.father_name || 'Not provided'}</p>
                                <p><strong>Mother:</strong> ${data.mother_name || 'Not provided'}</p>
                                <p><strong>Guardian:</strong> ${data.guardian_name || 'Not provided'}</p>
                                <p><strong>Guardian Phone:</strong> ${data.guardian_phone || 'Not provided'}</p>
                                <p><strong>Guardian Email:</strong> ${data.guardian_email || 'Not provided'}</p>
                            </div>
                        </div>
                        ${data.special_details ? `<div style="margin-top: 20px;"><h4 style="color: #1e3a8a; margin-bottom: 10px;">Special Details</h4><p>${data.special_details}</p></div>` : ''}
                    `;
                    document.getElementById('studentModal').style.display = 'block';
                });
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function removeStudent(studentGradeId) {
            if (confirm('Are you sure you want to remove this student from this grade?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="remove_student">
                    <input type="hidden" name="student_grade_id" value="${studentGradeId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function openGradeDetails(gradeId) {
            // This could open a detailed view of the grade
            console.log('Opening grade details for ID:', gradeId);
        }

        function openAddGradeModal() {
            // This would open a modal to add new grades/classes
            alert('Add Grade/Class functionality will be implemented here');
        }

        function openEditGradeModal(gradeId) {
            // This would open a modal to edit grades/classes
            alert(`Edit Grade functionality for ID: ${gradeId} will be implemented here.`);
        }

        function openDeleteGradeModal(gradeId) {
            if (confirm('Are you sure you want to delete this grade? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_grade">
                    <input type="hidden" name="grade_id" value="${gradeId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
