<?php
include 'config.php';

if (!isset($_SESSION['loggedin'])) {
    http_response_code(401);
    exit('Unauthorized');
}

// Function to calculate grade letter from mark
function getGradeLetter($mark) {
    if ($mark >= 75) return 'A';
    if ($mark >= 60) return 'B';
    if ($mark >= 40) return 'S';
    return 'F';
}

if (isset($_GET['student_id']) && isset($_GET['grade']) && isset($_GET['year'])) {
    $student_id = (int)$_GET['student_id'];
    $grade = (int)$_GET['grade'];
    $year = (int)$_GET['year'];
    
    // Get student information
    $student_sql = "SELECT * FROM students WHERE id = ?";
    $student_stmt = $conn->prepare($student_sql);
    $student_stmt->bind_param("i", $student_id);
    $student_stmt->execute();
    $student_result = $student_stmt->get_result();
    
    if ($student_result->num_rows > 0) {
        $student = $student_result->fetch_assoc();
        
        // Get student's marks for the specified year and grade
        $marks_sql = "SELECT m.*, s.name as subject_name, g.grade_number, g.class_name 
                      FROM marks m 
                      JOIN subjects s ON m.subject_id = s.id 
                      JOIN grades g ON m.grade_id = g.id 
                      WHERE m.student_id = ? AND m.year = ? AND g.grade_number = ?
                      ORDER BY s.name, m.term";
        $marks_stmt = $conn->prepare($marks_sql);
        $marks_stmt->bind_param("iii", $student_id, $year, $grade);
        $marks_stmt->execute();
        $marks_result = $marks_stmt->get_result();
        
        // Organize marks by subject
        $marks_by_subject = array();
        $total_marks = 0;
        $total_subjects = 0;
        $term_marks = array('1st' => 0, '2nd' => 0, '3rd' => 0);
        $term_counts = array('1st' => 0, '2nd' => 0, '3rd' => 0);
        
        while ($mark = $marks_result->fetch_assoc()) {
            $subject_name = $mark['subject_name'];
            if (!isset($marks_by_subject[$subject_name])) {
                $marks_by_subject[$subject_name] = array();
            }
            $marks_by_subject[$subject_name][$mark['term']] = array(
                'mark' => $mark['mark'],
                'grade' => getGradeLetter($mark['mark']),
                'remarks' => $mark['remarks']
            );
            
            $total_marks += $mark['mark'];
            $total_subjects++;
            $term_marks[$mark['term']] += $mark['mark'];
            $term_counts[$mark['term']]++;
        }
        
        // Calculate averages
        $overall_average = $total_subjects > 0 ? round($total_marks / $total_subjects, 2) : 0;
        $term_averages = array();
        foreach ($term_marks as $term => $total) {
            $term_averages[$term] = $term_counts[$term] > 0 ? round($total / $term_counts[$term], 2) : 0;
        }
        
        // Determine report card format based on grade
        $format = '';
        if ($grade >= 1 && $grade <= 5) $format = 'Primary';
        elseif ($grade >= 6 && $grade <= 9) $format = 'Junior Secondary';
        elseif ($grade >= 10 && $grade <= 11) $format = 'Senior Secondary';
        elseif ($grade >= 12 && $grade <= 13) $format = 'Advanced Level';
        else $format = 'General';
        
        // Get student's class
        $class_sql = "SELECT g.class_name FROM student_grades sg 
                      JOIN grades g ON sg.grade_id = g.id 
                      WHERE sg.student_id = ? AND g.year = ? AND g.grade_number = ?";
        $class_stmt = $conn->prepare($class_sql);
        $class_stmt->bind_param("iii", $student_id, $year, $grade);
        $class_stmt->execute();
        $class_result = $class_stmt->get_result();
        $class_row = $class_result->fetch_assoc();
        $class_name = $class_result->num_rows > 0 ? $class_row['class_name'] : 'N/A';
        
        $marks_stmt->close();
        $student_stmt->close();
        $class_stmt->close();
        
        // Output report card HTML
        ?>
        <div class="report-card" style="font-family: Arial, sans-serif; max-width: 100%;">
            <!-- Header -->
            <div style="text-align: center; border-bottom: 3px solid #1e3a8a; padding-bottom: 20px; margin-bottom: 30px;">
                <h1 style="color: #1e3a8a; margin: 0; font-size: 28px;">SCHOOLSYNC ACADEMIC REPORT CARD</h1>
                <h2 style="color: #374151; margin: 10px 0; font-size: 20px;"><?php echo $format; ?> Level - Academic Year <?php echo $year; ?></h2>
                <div style="display: flex; justify-content: center; align-items: center; gap: 40px; margin-top: 20px;">
                    <div style="text-align: left;">
                        <p><strong>Student Name:</strong> <?php echo htmlspecialchars($student['full_name']); ?></p>
                        <p><strong>Student ID:</strong> <?php echo $student['id']; ?></p>
                        <p><strong>Grade:</strong> <?php echo $grade; ?></p>
                        <p><strong>Class:</strong> <?php echo $class_name; ?></p>
                    </div>
                    <div style="text-align: left;">
                        <p><strong>Date of Birth:</strong> <?php echo $student['birth_date']; ?></p>
                        <p><strong>Gender:</strong> <?php echo ucfirst($student['gender']); ?></p>
                        <p><strong>Religion:</strong> <?php echo htmlspecialchars($student['religion']); ?></p>
                        <p><strong>Status:</strong> <?php echo ucfirst($student['status']); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Family Information -->
            <div style="background: #f8fafc; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                <h3 style="color: #1e3a8a; margin-top: 0; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">Family Information</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                    <div>
                        <p><strong>Father's Name:</strong> <?php echo htmlspecialchars($student['father_name']); ?></p>
                        <p><strong>Mother's Name:</strong> <?php echo htmlspecialchars($student['mother_name']); ?></p>
                    </div>
                    <div>
                        <p><strong>Guardian's Name:</strong> <?php echo htmlspecialchars($student['guardian_name']); ?></p>
                        <p><strong>Guardian's Phone:</strong> <?php echo htmlspecialchars($student['guardian_phone']); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Academic Performance -->
            <div style="margin-bottom: 30px;">
                <h3 style="color: #1e3a8a; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">Academic Performance</h3>
                
                <?php if (!empty($marks_by_subject)): ?>
                    <table style="width: 100%; border-collapse: collapse; margin-top: 20px; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                        <thead>
                            <tr style="background: #1e3a8a; color: white;">
                                <th style="padding: 15px; text-align: left; border: none;">Subject</th>
                                <th style="padding: 15px; text-align: center; border: none;">1st Term</th>
                                <th style="padding: 15px; text-align: center; border: none;">2nd Term</th>
                                <th style="padding: 15px; text-align: center; border: none;">3rd Term</th>
                                <th style="padding: 15px; text-align: center; border: none;">Average</th>
                                <th style="padding: 15px; text-align: center; border: none;">Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($marks_by_subject as $subject => $terms): ?>
                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                    <td style="padding: 15px; font-weight: 600; color: #1e3a8a;"><?php echo htmlspecialchars($subject); ?></td>
                                    <?php 
                                    $subject_total = 0;
                                    $subject_count = 0;
                                    for ($term = 1; $term <= 3; $term++):
                                        $term_name = $term == 1 ? '1st' : ($term == 2 ? '2nd' : '3rd');
                                        $mark_data = isset($terms[$term_name]) ? $terms[$term_name] : null;
                                        if ($mark_data) {
                                            $subject_total += $mark_data['mark'];
                                            $subject_count++;
                                        }
                                    ?>
                                        <td style="padding: 15px; text-align: center;">
                                            <?php if ($mark_data): ?>
                                                <div style="font-weight: 600; color: #1e3a8a;"><?php echo $mark_data['mark']; ?></div>
                                                <div style="font-size: 12px; color: #64748b;"><?php echo $mark_data['grade']; ?></div>
                                            <?php else: ?>
                                                <span style="color: #94a3b8; font-style: italic;">-</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endfor; ?>
                                    <td style="padding: 15px; text-align: center; font-weight: 600; color: #059669;">
                                        <?php echo $subject_count > 0 ? round($subject_total / $subject_count, 2) : '-'; ?>
                                    </td>
                                    <td style="padding: 15px; text-align: center;">
                                        <?php if ($subject_count > 0): 
                                            $avg = $subject_total / $subject_count;
                                            $grade_letter = getGradeLetter($avg);
                                            $grade_class = 'grade-' . strtolower($grade_letter);
                                        ?>
                                            <span class="grade-display <?php echo $grade_class; ?>" style="display: inline-block; width: 30px; height: 30px; line-height: 30px; text-align: center; border-radius: 50%; font-weight: bold; font-size: 12px; color: white;">
                                                <?php echo $grade_letter; ?>
                                            </span>
                                        <?php else: ?>
                                            <span style="color: #94a3b8;">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <!-- Summary Statistics -->
                    <div style="background: #f0f9ff; padding: 20px; border-radius: 8px; margin-top: 20px; border-left: 4px solid #0ea5e9;">
                        <h4 style="color: #0c4a6e; margin-top: 0;">Summary Statistics</h4>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                            <div>
                                <p><strong>Overall Average:</strong> <span style="color: #059669; font-weight: 600; font-size: 18px;"><?php echo $overall_average; ?>%</span></p>
                                <p><strong>Total Subjects:</strong> <?php echo $total_subjects; ?></p>
                            </div>
                            <div>
                                <p><strong>1st Term Average:</strong> <?php echo $term_averages['1st']; ?>%</p>
                                <p><strong>2nd Term Average:</strong> <?php echo $term_averages['2nd']; ?>%</p>
                                <p><strong>3rd Term Average:</strong> <?php echo $term_averages['3rd']; ?>%</p>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: #64748b; background: #f8fafc; border-radius: 8px;">
                        <i class="fa-solid fa-chart-line fa-3x" style="margin-bottom: 15px;"></i>
                        <h4>No Marks Available</h4>
                        <p>No academic marks have been recorded for this student in the selected criteria.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Additional Information -->
            <div style="background: #f8fafc; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                <h3 style="color: #1e3a8a; margin-top: 0; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px;">Additional Information</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                    <div>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($student['address']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($student['phone']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
                    </div>
                    <div>
                        <?php if ($student['special_details']): ?>
                            <p><strong>Special Details:</strong> <?php echo htmlspecialchars($student['special_details']); ?></p>
                        <?php endif; ?>
                        <p><strong>Enrollment Date:</strong> <?php echo date('F j, Y', strtotime($student['created_at'])); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div style="text-align: center; border-top: 2px solid #e2e8f0; padding-top: 20px; margin-top: 30px;">
                <p style="color: #64748b; font-size: 14px;">
                    <strong>Report Generated:</strong> <?php echo date('F j, Y \a\t g:i A'); ?> | 
                    <strong>System:</strong> SchoolSync School Management System
                </p>
            </div>
        </div>
        
        <style>
            .grade-a { background: #10b981 !important; }
            .grade-b { background: #3b82f6 !important; }
            .grade-s { background: #f59e0b !important; }
            .grade-f { background: #ef4444 !important; }
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
