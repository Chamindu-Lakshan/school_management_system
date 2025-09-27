<?php
include 'config.php';

if (!isset($_SESSION['loggedin'])) {
    http_response_code(401);
    exit('Unauthorized');
}

// Function to generate report card table
function generateReportCardTable($marks_data, $all_subjects, $years_data, $level) {
    if (empty($years_data) || empty($all_subjects)) {
        return '<p style="text-align: center; color: #64748b; padding: 20px;">No data available for this level.</p>';
    }
    $html = '<div style="overflow-x: auto; margin-bottom: 20px;">';
    $html .= '<table style="width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">';
    $html .= '<thead style="background: #f8fafc;">';
    $html .= '<tr>';
    $html .= '<th style="padding: 15px; text-align: left; border: 1px solid #e2e8f0; font-weight: 600; color: #374151; min-width: 150px;">Subject</th>';
    foreach ($years_data as $year_data) {
        $year = $year_data['year'];
        $grade = $year_data['grade_number'];
        $class = $year_data['class_name'];
        $html .= '<th style="padding: 15px; text-align: center; border: 1px solid #e2e8f0; font-weight: 600; color: #374151; background: #f1f5f9;">';
        $html .= '<div style="font-size: 14px; font-weight: 700; color: #1e3a8a;">' . $year . '</div>';
        $html .= '<div style="font-size: 12px; color: #64748b;">Grade ' . $grade . ' - ' . $class . '</div>';
        $html .= '<div style="display: flex; justify-content: space-around; margin-top: 8px;">';
        $html .= '<span style="font-size: 11px; font-weight: 600; color: #059669;">1st Term</span>';
        $html .= '<span style="font-size: 11px; font-weight: 600; color: #3b82f6;">2nd Term</span>';
        $html .= '<span style="font-size: 11px; font-weight: 600; color: #8b5cf6;">3rd Term</span>';
        $html .= '</div>';
        $html .= '</th>';
    }
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';
    foreach ($all_subjects as $subject) {
        $subject_name = $subject['subject_name'];
        $html .= '<tr style="border-bottom: 1px solid #f1f5f9;">';
        $html .= '<td style="padding: 15px; border: 1px solid #e2e8f0; font-weight: 600; color: #1e3a8a; background: #f8fafc;">' . htmlspecialchars($subject_name) . '</td>';
        foreach ($years_data as $year_data) {
            $year = $year_data['year'];
            $grade = $year_data['grade_number'];
            $html .= '<td style="padding: 15px; text-align: center; border: 1px solid #e2e8f0;">';
            $html .= '<div style="display: flex; justify-content: space-around; gap: 5px;">';
            $terms = array('1st term', '2nd term', '3rd term');
            foreach ($terms as $term) {
                $mark_data = null;
                if (isset($marks_data[$subject_name][$year][$grade][$term])) {
                    $mark_data = $marks_data[$subject_name][$year][$grade][$term];
                }
                $html .= '<div style="flex: 1; padding: 8px; border-radius: 4px; background: #f8fafc; border: 1px solid #e2e8f0;">';
                if ($mark_data) {
                    $mark = $mark_data['mark'];
                    $grade_letter = $mark_data['grade'];
                    $color = getGradeColor($grade_letter);
                    $html .= '<div style="font-weight: 600; color: ' . $color . '; font-size: 14px;">' . $mark . '</div>';
                    $html .= '<div style="font-size: 11px; color: ' . $color . '; font-weight: 600;">' . $grade_letter . '</div>';
                } else {
                    $html .= '<div style="color: #9ca3af; font-size: 12px;">-</div>';
                    $html .= '<div style="color: #9ca3af; font-size: 10px;">N/A</div>';
                }
                $html .= '</div>';
            }
            $html .= '</div>';
            $html .= '</td>';
        }
        $html .= '</tr>';
    }
    $html .= '</tbody>';
    $html .= '</table>';
    $html .= '</div>';
    return $html;
}

// Function to get grade color
function getGradeColor($grade_letter) {
    switch ($grade_letter) {
        case 'A': return '#059669'; // Green
        case 'B': return '#3b82f6'; // Blue
        case 'S': return '#8b5cf6'; // Purple
        case 'F': return '#dc2626'; // Red
        default: return '#6b7280'; // Gray
    }
}
// Function to calculate grade letter from mark
function getGradeLetter($mark) {
    if ($mark >= 75) return 'A';
    if ($mark >= 60) return 'B';
    if ($mark >= 40) return 'S';
    return 'F';
}

if (isset($_GET['student_id'])) {
    $student_id = (int)$_GET['student_id'];
    
    // Get student information
    $student_sql = "SELECT * FROM students WHERE id = ?";
    $student_stmt = $conn->prepare($student_sql);
    $student_stmt->bind_param("i", $student_id);
    $student_stmt->execute();
    $student_result = $student_stmt->get_result();
    
    if ($student_result->num_rows > 0) {
        $student = $student_result->fetch_assoc();
        
        // Get all years and grades for this student
        $student_years_sql = "SELECT DISTINCT g.year, g.grade_number, g.class_name 
                              FROM student_grades sg 
                              JOIN grades g ON sg.grade_id = g.id 
                              WHERE sg.student_id = ? AND sg.status = 'active'
                              ORDER BY g.year, g.grade_number";
        $student_years_stmt = $conn->prepare($student_years_sql);
        $student_years_stmt->bind_param("i", $student_id);
        $student_years_stmt->execute();
        $student_years_result = $student_years_stmt->get_result();
        
        $student_years = array();
        while ($row = $student_years_result->fetch_assoc()) {
            $student_years[] = $row;
        }
        $student_years_stmt->close();
        
        // Get all subjects for this student across all years
        $subjects_sql = "SELECT DISTINCT s.id, s.name as subject_name 
                         FROM subjects s 
                         JOIN marks m ON s.id = m.subject_id 
                         WHERE m.student_id = ? AND s.status = 'active'
                         ORDER BY s.name";
        $subjects_stmt = $conn->prepare($subjects_sql);
        $subjects_stmt->bind_param("i", $student_id);
        $subjects_stmt->execute();
        $subjects_result = $subjects_stmt->get_result();
        
        $all_subjects = array();
        while ($row = $subjects_result->fetch_assoc()) {
            $all_subjects[] = $row;
        }
        $subjects_stmt->close();
        
        // Get all marks for this student across all years and grades
        $all_marks_sql = "SELECT m.*, s.name as subject_name, g.year, g.grade_number, g.class_name 
                          FROM marks m 
                          JOIN subjects s ON m.subject_id = s.id 
                          JOIN grades g ON m.grade_id = g.id 
                          WHERE m.student_id = ?
                          ORDER BY g.year, g.grade_number, s.name, m.term";
        $all_marks_stmt = $conn->prepare($all_marks_sql);
        $all_marks_stmt->bind_param("i", $student_id);
        $all_marks_stmt->execute();
        $all_marks_result = $all_marks_stmt->get_result();
        
        // Organize marks by subject, year, grade, and term
        $marks_data = array();
        while ($mark = $all_marks_result->fetch_assoc()) {
            $subject_name = $mark['subject_name'];
            $year = $mark['year'];
            $grade = $mark['grade_number'];
            $term = $mark['term'];
            
            if (!isset($marks_data[$subject_name])) {
                $marks_data[$subject_name] = array();
            }
            if (!isset($marks_data[$subject_name][$year])) {
                $marks_data[$subject_name][$year] = array();
            }
            if (!isset($marks_data[$subject_name][$year][$grade])) {
                $marks_data[$subject_name][$year][$grade] = array();
            }
            
            $marks_data[$subject_name][$year][$grade][$term] = array(
                'mark' => $mark['mark'],
                'grade' => getGradeLetter($mark['mark']),
                'remarks' => $mark['remarks']
            );
        }
        $all_marks_stmt->close();
        
        // Group years and grades by report card format
        $primary_years = array();    // Grades 1-5
        $junior_years = array();     // Grades 6-11
        $advanced_years = array();   // Grades 12-13
        
        foreach ($student_years as $year_data) {
            $grade_num = $year_data['grade_number'];
            if ($grade_num >= 1 && $grade_num <= 5) {
                $primary_years[] = $year_data;
            } elseif ($grade_num >= 6 && $grade_num <= 11) {
                $junior_years[] = $year_data;
            } elseif ($grade_num >= 12 && $grade_num <= 13) {
                $advanced_years[] = $year_data;
            }
        }
        
        $student_stmt->close();
        
        // Output report card HTML
        ?>
        <div class="report-card-container" style="font-family: Arial, sans-serif; max-width: 100%;">
            <!-- Student Header -->
            <div class="student-header" style="text-align: center; margin-bottom: 30px; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px;">
                <h1 style="margin: 0 0 10px 0; font-size: 28px;">STUDENT REPORT CARD</h1>
                <h2 style="margin: 0 0 15px 0; font-size: 22px;"><?php echo htmlspecialchars($student['full_name']); ?></h2>
                <div style="display: flex; justify-content: center; gap: 30px; flex-wrap: wrap;">
                    <div><strong>Student ID:</strong> <?php echo $student['id']; ?></div>
                    <div><strong>Gender:</strong> <?php echo ucfirst($student['gender']); ?></div>
                    <div><strong>Birth Date:</strong> <?php echo $student['birth_date']; ?></div>
                </div>
            </div>
            
            <?php if (!empty($primary_years)): ?>
            <!-- Primary Report Card (Grades 1-5) -->
            <div class="report-card-section" style="margin-bottom: 40px;">
                <h3 style="text-align: center; color: #10b981; font-size: 20px; margin-bottom: 20px; padding: 10px; background: #f0fdf4; border-radius: 8px;">
                    <i class="fa-solid fa-graduation-cap"></i> PRIMARY LEVEL (GRADES 1-5)
                </h3>
                <?php echo generateReportCardTable($marks_data, $all_subjects, $primary_years, 'primary'); ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($junior_years)): ?>
            <!-- Junior Secondary Report Card (Grades 6-11) -->
            <div class="report-card-section" style="margin-bottom: 40px;">
                <h3 style="text-align: center; color: #3b82f6; font-size: 20px; margin-bottom: 20px; padding: 10px; background: #eff6ff; border-radius: 8px;">
                    <i class="fa-solid fa-book-open"></i> JUNIOR SECONDARY LEVEL (GRADES 6-11)
                </h3>
                <?php echo generateReportCardTable($marks_data, $all_subjects, $junior_years, 'junior'); ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($advanced_years)): ?>
            <!-- Advanced Level Report Card (Grades 12-13) -->
            <div class="report-card-section" style="margin-bottom: 40px;">
                <h3 style="text-align: center; color: #8b5cf6; font-size: 20px; margin-bottom: 20px; padding: 10px; background: #faf5ff; border-radius: 8px;">
                    <i class="fa-solid fa-trophy"></i> ADVANCED LEVEL (GRADES 12-13)
                </h3>
                <?php echo generateReportCardTable($marks_data, $all_subjects, $advanced_years, 'advanced'); ?>
            </div>
            <?php endif; ?>
            
            <?php if (empty($primary_years) && empty($junior_years) && empty($advanced_years)): ?>
            <div style="text-align: center; padding: 40px; color: #64748b;">
                <i class="fa-solid fa-inbox" style="font-size: 3rem; margin-bottom: 20px; display: block;"></i>
                <h3>No Academic Records Found</h3>
                <p>This student has no marks recorded in the system.</p>
            </div>
            <?php endif; ?>
        </div>
        
        <style>
        .report-card-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .report-card-section {
            margin-bottom: 40px;
        }
        
        .report-card-section table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .report-card-section th {
            padding: 15px;
            text-align: center;
            border: 1px solid #e2e8f0;
            font-weight: 600;
            color: #374151;
            background: #f8fafc;
        }
        
        .report-card-section td {
            padding: 15px;
            text-align: center;
            border: 1px solid #e2e8f0;
        }
        
        .report-card-section tr:hover {
            background-color: #f9fafb;
        }
        
        @media (max-width: 768px) {
            .report-card-container {
                padding: 10px;
            }
            
            .report-card-section table {
                font-size: 12px;
            }
            
            .report-card-section th,
            .report-card-section td {
                padding: 8px;
            }
        }
        </style>
        <?php
    } else {
        echo '<div style="text-align: center; padding: 40px; color: #ef4444;"><i class="fa-solid fa-exclamation-triangle fa-2x"></i><p>Student not found</p></div>';
    }
} else {
    echo '<div style="text-align: center; padding: 40px; color: #ef4444;"><i class="fa-solid fa-exclamation-triangle fa-2x"></i><p>Missing required parameters</p></div>';
}

$conn->close();
?>
