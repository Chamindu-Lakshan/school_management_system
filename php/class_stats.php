<?php
include 'config.php';
if (!isset($_SESSION['loggedin'])) header("Location: ../index.html");

// Get current year as default
$current_year = date('Y');
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : $current_year;
$selected_grade = isset($_GET['grade']) ? (int)$_GET['grade'] : 6; // Default to Grade 6
$selected_class = isset($_GET['class']) ? $_GET['class'] : 'A'; // Default to Class A

// If this is the initial page load with default values, redirect to show the data
if (!isset($_GET['year']) && !isset($_GET['grade']) && !isset($_GET['class'])) {
    $redirect_url = "class_stats.php?year=" . $selected_year . "&grade=" . $selected_grade . "&class=" . $selected_class;
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

// Get class statistics
$class_stats = array();
$student_rankings = array();
$subject_stats = array();

if ($selected_year && $selected_grade && $selected_class) {
    // Get students in the class
    $students_sql = "SELECT DISTINCT s.id, s.full_name, s.gender, s.image_path 
                     FROM students s 
                     JOIN student_grades sg ON s.id = sg.student_id 
                     JOIN grades g ON sg.grade_id = g.id 
                     WHERE g.year = ? AND g.grade_number = ? AND g.class_name = ? AND sg.status = 'active'
                     ORDER BY s.full_name";
    $students_stmt = $conn->prepare($students_sql);
    $students_stmt->bind_param("iis", $selected_year, $selected_grade, $selected_class);
    $students_stmt->execute();
    $students_result = $students_stmt->get_result();
    
    $student_ids = array();
    while ($student = $students_result->fetch_assoc()) {
        $student_ids[] = $student['id'];
    }
    $students_stmt->close();
    
    if (!empty($student_ids)) {
        // Get overall class statistics
        $overall_sql = "SELECT 
                           COUNT(DISTINCT m.student_id) as total_students,
                           COUNT(m.id) as total_marks,
                           MIN(m.mark) as min_mark,
                           MAX(m.mark) as max_mark,
                           AVG(m.mark) as avg_mark,
                           COUNT(CASE WHEN m.mark >= 75 THEN 1 END) as grade_a_count,
                           COUNT(CASE WHEN m.mark >= 60 AND m.mark < 75 THEN 1 END) as grade_b_count,
                           COUNT(CASE WHEN m.mark >= 40 AND m.mark < 60 THEN 1 END) as grade_s_count,
                           COUNT(CASE WHEN m.mark < 40 THEN 1 END) as grade_f_count
                        FROM marks m 
                        JOIN student_grades sg ON m.student_id = sg.student_id 
                        JOIN grades g ON sg.grade_id = g.id 
                        WHERE g.year = ? AND g.grade_number = ? AND g.class_name = ? AND m.student_id IN (" . implode(',', $student_ids) . ")";
        $overall_stmt = $conn->prepare($overall_sql);
        $overall_stmt->bind_param("iis", $selected_year, $selected_grade, $selected_class);
        $overall_stmt->execute();
        $overall_result = $overall_stmt->get_result();
        $class_stats = $overall_result->fetch_assoc();
        $overall_stmt->close();
        
        // Get student rankings by total marks
        $ranking_sql = "SELECT 
                           s.id, s.full_name, s.gender, s.image_path,
                           COUNT(m.id) as subjects_count,
                           SUM(m.mark) as total_marks,
                           AVG(m.mark) as average_mark,
                           COUNT(CASE WHEN m.mark >= 75 THEN 1 END) as a_grades,
                           COUNT(CASE WHEN m.mark >= 60 AND m.mark < 75 THEN 1 END) as b_grades,
                           COUNT(CASE WHEN m.mark >= 40 AND m.mark < 60 THEN 1 END) as s_grades,
                           COUNT(CASE WHEN m.mark < 40 THEN 1 END) as f_grades
                        FROM students s 
                        LEFT JOIN marks m ON s.id = m.student_id 
                        LEFT JOIN student_grades sg ON s.id = sg.student_id 
                        LEFT JOIN grades g ON sg.grade_id = g.id 
                        WHERE g.year = ? AND g.grade_number = ? AND g.class_name = ? AND sg.status = 'active'
                        GROUP BY s.id, s.full_name, s.gender, s.image_path
                        ORDER BY total_marks DESC, average_mark DESC";
        $ranking_stmt = $conn->prepare($ranking_sql);
        $ranking_stmt->bind_param("iis", $selected_year, $selected_grade, $selected_class);
        $ranking_stmt->execute();
        $ranking_result = $ranking_stmt->get_result();
        
        while ($row = $ranking_result->fetch_assoc()) {
            $student_rankings[] = $row;
        }
        $ranking_stmt->close();
        
        // Get subject-wise statistics
        $subject_sql = "SELECT 
                           s.id, s.name,
                           COUNT(m.id) as total_marks,
                           MIN(m.mark) as min_mark,
                           MAX(m.mark) as max_mark,
                           AVG(m.mark) as avg_mark,
                           COUNT(CASE WHEN m.mark >= 75 THEN 1 END) as grade_a_count,
                           COUNT(CASE WHEN m.mark >= 60 AND m.mark < 75 THEN 1 END) as grade_b_count,
                           COUNT(CASE WHEN m.mark >= 40 AND m.mark < 60 THEN 1 END) as grade_s_count,
                           COUNT(CASE WHEN m.mark < 40 THEN 1 END) as grade_f_count
                        FROM subjects s 
                        LEFT JOIN marks m ON s.id = m.subject_id 
                        LEFT JOIN student_grades sg ON m.student_id = sg.student_id 
                        LEFT JOIN grades g ON sg.grade_id = g.id 
                        WHERE g.year = ? AND g.grade_number = ? AND g.class_name = ? AND m.student_id IN (" . implode(',', $student_ids) . ")
                        GROUP BY s.id, s.name
                        ORDER BY s.name";
        $subject_stmt = $conn->prepare($subject_sql);
        $subject_stmt->bind_param("iis", $selected_year, $selected_grade, $selected_class);
        $subject_stmt->execute();
        $subject_result = $subject_stmt->get_result();
        
        while ($row = $subject_result->fetch_assoc()) {
            $subject_stats[] = $row;
        }
        $subject_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Statistics - SchoolSync</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
        }
        .stat-card h3 {
            color: #1e3a8a;
            margin-top: 0;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 10px;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #059669;
            text-align: center;
            margin: 15px 0;
        }
        .stat-label {
            text-align: center;
            color: #64748b;
            font-size: 0.9rem;
        }
        .grade-distribution {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-top: 15px;
        }
        .grade-item {
            text-align: center;
            padding: 10px;
            border-radius: 8px;
            color: white;
            font-weight: bold;
        }
        .grade-a { background: #10b981; }
        .grade-b { background: #3b82f6; }
        .grade-s { background: #f59e0b; }
        .grade-f { background: #ef4444; }
        .ranking-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .ranking-table th {
            background: #1e3a8a;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        .ranking-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        .ranking-table tr:hover {
            background: #f8fafc;
        }
        .rank-badge {
            display: inline-block;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #1e3a8a;
            color: white;
            text-align: center;
            line-height: 30px;
            font-weight: bold;
            font-size: 0.9rem;
        }
        .rank-1 { background: #fbbf24; }
        .rank-2 { background: #9ca3af; }
        .rank-3 { background: #b45309; }
        .student-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
        }
        .subject-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .subject-stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
        }
        .subject-stat-card h4 {
            color: #1e3a8a;
            margin-top: 0;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 10px;
        }
        .chart-container {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-top: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
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
            <h1><i class="fa-solid fa-chart-bar"></i> Class Statistics</h1>
        </div>

        <!-- Selection Form -->
        <div class="card">
            <h3><i class="fa-solid fa-filter"></i> Select Class</h3>
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
            </form>
        </div>

        <!-- Class Statistics -->
        <?php if ($selected_year && $selected_grade && $selected_class && !empty($class_stats)): ?>
            <div class="card">
                <h3><i class="fa-solid fa-users"></i> Grade <?php echo $selected_grade; ?> Class <?php echo $selected_class; ?> - Year <?php echo $selected_year; ?></h3>
                
                <!-- Overall Statistics -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3><i class="fa-solid fa-users"></i> Total Students</h3>
                        <div class="stat-value"><?php echo $class_stats['total_students']; ?></div>
                        <div class="stat-label">Enrolled Students</div>
                    </div>
                    
                    <div class="stat-card">
                        <h3><i class="fa-solid fa-chart-line"></i> Average Mark</h3>
                        <div class="stat-value"><?php echo round($class_stats['avg_mark'], 1); ?>%</div>
                        <div class="stat-label">Class Average</div>
                    </div>
                    
                    <div class="stat-card">
                        <h3><i class="fa-solid fa-trophy"></i> Highest Mark</h3>
                        <div class="stat-value"><?php echo $class_stats['max_mark']; ?>%</div>
                        <div class="stat-label">Best Performance</div>
                    </div>
                    
                    <div class="stat-card">
                        <h3><i class="fa-solid fa-chart-area"></i> Lowest Mark</h3>
                        <div class="stat-value"><?php echo $class_stats['min_mark']; ?>%</div>
                        <div class="stat-label">Needs Improvement</div>
                    </div>
                </div>
                
                <!-- Grade Distribution -->
                <div class="card">
                    <h3><i class="fa-solid fa-pie-chart"></i> Grade Distribution</h3>
                    <div class="grade-distribution">
                        <div class="grade-item grade-a">
                            <div style="font-size: 1.5rem;"><?php echo $class_stats['grade_a_count']; ?></div>
                            <div style="font-size: 0.8rem;">Grade A</div>
                        </div>
                        <div class="grade-item grade-b">
                            <div style="font-size: 1.5rem;"><?php echo $class_stats['grade_b_count']; ?></div>
                            <div style="font-size: 0.8rem;">Grade B</div>
                        </div>
                        <div class="grade-item grade-s">
                            <div style="font-size: 1.5rem;"><?php echo $class_stats['grade_s_count']; ?></div>
                            <div style="font-size: 0.8rem;">Grade S</div>
                        </div>
                        <div class="grade-item grade-f">
                            <div style="font-size: 1.5rem;"><?php echo $class_stats['grade_f_count']; ?></div>
                            <div style="font-size: 0.8rem;">Grade F</div>
                        </div>
                    </div>
                </div>
                
                <!-- Student Rankings -->
                <?php if (!empty($student_rankings)): ?>
                    <div class="card">
                        <h3><i class="fa-solid fa-ranking-star"></i> Student Rankings</h3>
                        <table class="ranking-table">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Student</th>
                                    <th>Total Marks</th>
                                    <th>Average</th>
                                    <th>Grade Distribution</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($student_rankings as $index => $student): ?>
                                    <tr>
                                        <td>
                                            <?php 
                                            $rank = $index + 1;
                                            $rank_class = $rank <= 3 ? "rank-$rank" : "rank-other";
                                            ?>
                                            <span class="rank-badge <?php echo $rank_class; ?>"><?php echo $rank; ?></span>
                                        </td>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                <div style="width: 40px; height: 40px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center; color: #64748b; overflow: hidden;">
                                                    <?php if ($student['image_path']): ?>
                                                        <img src="<?php echo htmlspecialchars($student['image_path']); ?>" alt="Student Photo" style="width: 100%; height: 100%; object-fit: cover;">
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
                                            <strong><?php echo $student['total_marks']; ?></strong>
                                            <br><small><?php echo $student['subjects_count']; ?> subjects</small>
                                        </td>
                                        <td>
                                            <strong style="color: #059669;"><?php echo round($student['average_mark'], 1); ?>%</strong>
                                        </td>
                                        <td>
                                            <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                                <?php if ($student['a_grades'] > 0): ?>
                                                    <span class="grade-item grade-a" style="font-size: 0.8rem; padding: 2px 6px;"><?php echo $student['a_grades']; ?>A</span>
                                                <?php endif; ?>
                                                <?php if ($student['b_grades'] > 0): ?>
                                                    <span class="grade-item grade-b" style="font-size: 0.8rem; padding: 2px 6px;"><?php echo $student['b_grades']; ?>B</span>
                                                <?php endif; ?>
                                                <?php if ($student['s_grades'] > 0): ?>
                                                    <span class="grade-item grade-s" style="font-size: 0.8rem; padding: 2px 6px;"><?php echo $student['s_grades']; ?>S</span>
                                                <?php endif; ?>
                                                <?php if ($student['f_grades'] > 0): ?>
                                                    <span class="grade-item grade-f" style="font-size: 0.8rem; padding: 2px 6px;"><?php echo $student['f_grades']; ?>F</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                
                <!-- Subject Statistics -->
                <?php if (!empty($subject_stats)): ?>
                    <div class="card">
                        <h3><i class="fa-solid fa-book"></i> Subject Performance</h3>
                        <div class="chart-container">
                            <canvas id="subjectChart" width="400" height="200"></canvas>
                        </div>
                        
                        <table class="ranking-table">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Average</th>
                                    <th>Highest</th>
                                    <th>Lowest</th>
                                    <th>Grade Distribution</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subject_stats as $subject): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($subject['name']); ?></strong></td>
                                        <td><strong style="color: #059669;"><?php echo round($subject['avg_mark'], 1); ?>%</strong></td>
                                        <td style="color: #10b981;"><?php echo $subject['max_mark']; ?>%</td>
                                        <td style="color: #ef4444;"><?php echo $subject['min_mark']; ?>%</td>
                                        <td>
                                            <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                                <?php if ($subject['grade_a_count'] > 0): ?>
                                                    <span class="grade-item grade-a" style="font-size: 0.8rem; padding: 2px 6px;"><?php echo $subject['grade_a_count']; ?>A</span>
                                                <?php endif; ?>
                                                <?php if ($subject['grade_b_count'] > 0): ?>
                                                    <span class="grade-item grade-b" style="font-size: 0.8rem; padding: 2px 6px;"><?php echo $subject['grade_b_count']; ?>B</span>
                                                <?php endif; ?>
                                                <?php if ($subject['grade_s_count'] > 0): ?>
                                                    <span class="grade-item grade-s" style="font-size: 0.8rem; padding: 2px 6px;"><?php echo $subject['grade_s_count']; ?>S</span>
                                                <?php endif; ?>
                                                <?php if ($subject['grade_f_count'] > 0): ?>
                                                    <span class="grade-item grade-f" style="font-size: 0.8rem; padding: 2px 6px;"><?php echo $subject['grade_f_count']; ?>F</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php elseif ($selected_year && $selected_grade && $selected_class): ?>
            <div class="card">
                <div class="no-data">
                    <i class="fa-solid fa-chart-bar fa-3x"></i>
                    <h3>No Data Available</h3>
                    <p>No statistics available for the selected class criteria.</p>
                </div>
            </div>
        <?php elseif ($selected_year && $selected_grade): ?>
            <div class="card">
                <div class="no-data">
                    <i class="fa-solid fa-chalkboard fa-3x"></i>
                    <h3>Select Class</h3>
                    <p>Please select a class to view statistics.</p>
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
                    <p>Please select an academic year to start viewing class statistics.</p>
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

        // Subject Performance Chart
        <?php if (!empty($subject_stats)): ?>
        const ctx = document.getElementById('subjectChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [<?php echo implode(',', array_map(function($subject) { return "'" . addslashes($subject['name']) . "'"; }, $subject_stats)); ?>],
                datasets: [{
                    label: 'Average Mark (%)',
                    data: [<?php echo implode(',', array_map(function($subject) { return round($subject['avg_mark'], 1); }, $subject_stats)); ?>],
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                },
                plugins: {
                    title: {
                        display: true,
                        text: 'Subject Performance Comparison'
                    }
                }
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>
<?php $conn->close(); ?>
