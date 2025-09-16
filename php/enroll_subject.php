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
            case 'enroll':
    $student_id = $_POST['student_id'];
    $subject_id = $_POST['subject_id'];
    $year = $_POST['year'];

                // Check if already enrolled
                $check_sql = "SELECT id FROM enrollments WHERE student_id = ? AND subject_id = ? AND year = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("iii", $student_id, $subject_id, $year);
                $check_stmt->execute();
                $result = $check_stmt->get_result();
                
                if ($result->num_rows == 0) {
                    $sql = "INSERT INTO enrollments (student_id, subject_id, year, enrolled_date) VALUES (?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $enrolled_date = date('Y-m-d');
                    $stmt->bind_param("iiis", $student_id, $subject_id, $year, $enrolled_date);
                    $stmt->execute();
                }
                break;
                
            case 'unenroll':
                $enrollment_id = $_POST['enrollment_id'];
                $sql = "DELETE FROM enrollments WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $enrollment_id);
    $stmt->execute();
                break;
        }
        header("Location: enroll_subject.php" . (isset($_GET['year']) ? "?year=" . $_GET['year'] : "") . (isset($_GET['grade']) ? "&grade=" . $_GET['grade'] : ""));
        exit;
    }
}

// Get current year as default
$current_year = date('Y');
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : $current_year;
$selected_grade = isset($_GET['grade']) ? (int)$_GET['grade'] : null;

// Fetch all grades
$sql_grades = "SELECT DISTINCT grade_number FROM grades WHERE year = ? ORDER BY grade_number";
$stmt_grades = $conn->prepare($sql_grades);
$stmt_grades->bind_param("i", $selected_year);
$stmt_grades->execute();
$result_grades = $stmt_grades->get_result();

// Fetch students if grade is selected
$result_students = null;
if ($selected_grade) {
    $sql_students = "SELECT DISTINCT s.id, s.full_name, s.gender, s.birth_date, s.address, s.phone, s.email, s.religion, s.father_name, s.mother_name, s.guardian_name, s.guardian_phone, s.guardian_email, s.special_details, s.status, s.image_path, s.created_at FROM students s 
                     JOIN student_grades sg ON s.id = sg.student_id 
                     JOIN grades g ON sg.grade_id = g.id 
                     WHERE g.year = ? AND g.grade_number = ? AND s.status = 'active'
                     ORDER BY s.full_name";
    $stmt_students = $conn->prepare($sql_students);
    $stmt_students->bind_param("ii", $selected_year, $selected_grade);
    $stmt_students->execute();
    $result_students = $stmt_students->get_result();
}

// Fetch all subjects
$sql_subjects = "SELECT * FROM subjects WHERE status = 'active' ORDER BY name";
$result_subjects = $conn->query($sql_subjects);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enroll Students to Subjects - SchoolSync</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="../js/scripts.js"></script>
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
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .student-card:hover {
            transform: translateY(-4px);
        }
        .student-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e5e7eb;
        }
        .student-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }
        .student-info h3 {
            margin: 0;
            color: #1e3a8a;
            font-size: 1.1rem;
        }
        .student-info p {
            margin: 5px 0 0 0;
            color: #64748b;
            font-size: 0.9rem;
        }
        .enrolled-subjects {
            margin-bottom: 15px;
        }
        .enrolled-subjects h4 {
            color: #1e3a8a;
            margin-bottom: 10px;
            font-size: 0.95rem;
        }
        .subject-tag {
            display: inline-block;
            background: #dbeafe;
            color: #1e3a8a;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            margin: 2px;
            position: relative;
        }
        .subject-tag .remove-btn {
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 50%;
            width: 16px;
            height: 16px;
            font-size: 0.7rem;
            cursor: pointer;
            margin-left: 5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .enroll-form {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .enroll-form select {
            flex: 1;
            padding: 8px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.9rem;
        }
        .enroll-form button {
            padding: 8px 12px;
            background: #10b981;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: background 0.3s ease;
        }
        .enroll-form button:hover {
            background: #059669;
        }
        .no-students {
            text-align: center;
            padding: 40px;
            color: #64748b;
        }
        .no-students i {
            font-size: 3rem;
            margin-bottom: 20px;
            display: block;
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
            <h1>Enroll Students to Subjects</h1>
        </div>

        <!-- Year and Grade Selection -->
        <div class="card">
            <div class="selection-container">
                <form method="GET" class="selection-container">
                    <div class="selection-group">
                        <label for="year">Academic Year</label>
                        <select name="year" id="year" onchange="this.form.submit()">
                            <?php for ($i = $current_year - 5; $i <= $current_year + 5; $i++) { ?>
                                <option value="<?php echo $i; ?>" <?php if ($selected_year == $i) echo 'selected'; ?>>
                                    <?php echo $i; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="selection-group">
                        <label for="grade">Grade</label>
                        <select name="grade" id="grade" onchange="this.form.submit()">
                            <option value="">Select Grade</option>
                            <?php while ($grade = $result_grades->fetch_assoc()) { ?>
                                <option value="<?php echo $grade['grade_number']; ?>" 
                                    <?php if ($selected_grade == $grade['grade_number']) echo 'selected'; ?>>
                                    Grade <?php echo $grade['grade_number']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <!-- Students List -->
        <?php if ($selected_grade && $result_students && $result_students->num_rows > 0): ?>
            <div class="students-grid">
                <?php while($student = $result_students->fetch_assoc()): ?>
                    <?php
                    // Get enrolled subjects for this student
                    $sql_enrolled = "SELECT e.id as enrollment_id, s.name as subject_name 
                                   FROM enrollments e 
                                   JOIN subjects s ON e.subject_id = s.id 
                                   WHERE e.student_id = ? AND e.year = ? AND e.status = 'active'
                                   ORDER BY s.name";
                    $stmt_enrolled = $conn->prepare($sql_enrolled);
                    $stmt_enrolled->bind_param("ii", $student['id'], $selected_year);
                    $stmt_enrolled->execute();
                    $result_enrolled = $stmt_enrolled->get_result();
                    
                    // Get available subjects (not enrolled)
                    $sql_available = "SELECT s.* FROM subjects s 
                                    WHERE s.status = 'active' 
                                    AND s.id NOT IN (
                                        SELECT e.subject_id FROM enrollments e 
                                        WHERE e.student_id = ? AND e.year = ? AND e.status = 'active'
                                    )
                                    ORDER BY s.name";
                    $stmt_available = $conn->prepare($sql_available);
                    $stmt_available->bind_param("ii", $student['id'], $selected_year);
                    $stmt_available->execute();
                    $result_available = $stmt_available->get_result();
                    ?>
                    
                    <div class="student-card">
                        <div class="student-header" onclick="openStudentModal(<?php echo $student['id']; ?>)">
                            <div class="student-avatar">
                                <?php if ($student['image_path']): ?>
                                    <img src="<?php echo htmlspecialchars($student['image_path']); ?>" alt="Student Photo" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                                <?php else: ?>
                                    <i class="fa-solid fa-user"></i>
                                <?php endif; ?>
                            </div>
                            <div class="student-info">
                                <h3><?php echo htmlspecialchars($student['full_name']); ?></h3>
                                <p>ID: <?php echo $student['id']; ?> • <?php echo ucfirst($student['gender']); ?></p>
                            </div>
                        </div>
                        
                        <div class="enrolled-subjects">
                            <h4>Enrolled Subjects (<?php echo $result_enrolled->num_rows; ?>)</h4>
                            <?php if ($result_enrolled->num_rows > 0): ?>
                                <?php while($enrolled = $result_enrolled->fetch_assoc()): ?>
                                    <span class="subject-tag">
                                        <?php echo htmlspecialchars($enrolled['subject_name']); ?>
                                        <button class="remove-btn" onclick="unenrollSubject(<?php echo $enrolled['enrollment_id']; ?>)">
                                            <i class="fa-solid fa-times"></i>
                                        </button>
                                    </span>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p style="color: #64748b; font-style: italic;">No subjects enrolled</p>
                            <?php endif; ?>
                </div>

                        <?php if ($result_available->num_rows > 0): ?>
                            <form method="POST" class="enroll-form">
                                <input type="hidden" name="action" value="enroll">
                                <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                                <input type="hidden" name="year" value="<?php echo $selected_year; ?>">
                                <select name="subject_id" required>
                                    <option value="">Select Subject</option>
                                    <?php while($subject = $result_available->fetch_assoc()): ?>
                                        <option value="<?php echo $subject['id']; ?>">
                                            <?php echo htmlspecialchars($subject['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                    </select>
                                <button type="submit">
                                    <i class="fa-solid fa-plus"></i> Enroll
                                </button>
                            </form>
                        <?php else: ?>
                            <p style="color: #10b981; font-size: 0.9rem; text-align: center; margin-top: 10px;">
                                <i class="fa-solid fa-check-circle"></i> All subjects enrolled
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php elseif ($selected_grade): ?>
            <div class="no-students">
                <i class="fa-solid fa-users"></i>
                <h3>No Students Found</h3>
                <p>No students are assigned to Grade <?php echo $selected_grade; ?> for <?php echo $selected_year; ?>.</p>
            </div>
        <?php else: ?>
            <div class="no-students">
                <i class="fa-solid fa-graduation-cap"></i>
                <h3>Select Year and Grade</h3>
                <p>Please select a year and grade to view students and manage subject enrollments.</p>
            </div>
        <?php endif; ?>
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

        function unenrollSubject(enrollmentId) {
            if (confirm('Are you sure you want to unenroll this student from this subject?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="unenroll">
                    <input type="hidden" name="enrollment_id" value="${enrollmentId}">
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
