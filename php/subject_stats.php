<?php
include 'config.php';
if (!isset($_SESSION['loggedin'])) header("Location: ../index.html");

// Get current year as default
$current_year = date('Y');
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : $current_year;
$selected_subject = isset($_GET['subject']) ? (int)$_GET['subject'] : 0;

// Get available years
$years = array();
for ($i = $current_year - 5; $i <= $current_year + 5; $i++) {
    $years[] = $i;
}

// Get subjects
$subjects = array();
$subject_sql = "SELECT id, name, description FROM subjects WHERE status = 'active' ORDER BY name";
$subject_result = $conn->query($subject_sql);
while ($row = $subject_result->fetch_assoc()) {
    $subjects[] = $row;
}

// Get subject statistics
$subject_stats = array();
$grade_stats = array();
$term_stats = array();

if ($selected_year && $selected_subject) {
    // Get overall subject statistics
    $overall_sql = "SELECT 
                       COUNT(DISTINCT m.student_id) as total_students,
                       COUNT(m.id) as total_marks,
                       MIN(m.mark) as min_mark,
                       MAX(m.mark) as max_mark,
                       AVG(m.mark) as avg_mark,
                       COUNT(CASE WHEN m.grade_letter = 'A' THEN 1 END) as grade_a_count,
                       COUNT(CASE WHEN m.grade_letter = 'B' THEN 1 END) as grade_b_count,
                       COUNT(CASE WHEN m.grade_letter = 'S' THEN 1 END) as grade_s_count,
                       COUNT(CASE WHEN m.grade_letter = 'F' THEN 1 END) as grade_f_count
                    FROM marks m 
                    WHERE m.subject_id = ? AND m.year = ?";
    $overall_stmt = $conn->prepare($overall_sql);
    $overall_stmt->bind_param("ii", $selected_subject, $selected_year);
    $overall_stmt->execute();
    $overall_result = $overall_stmt->get_result();
    $subject_stats = $overall_result->fetch_assoc();
    $overall_stmt->close();
    
    // Get statistics by grade
    $grade_sql = "SELECT 
                     g.grade_number,
                     COUNT(DISTINCT m.student_id) as students_count,
                     COUNT(m.id) as marks_count,
                     MIN(m.mark) as min_mark,
                     MAX(m.mark) as max_mark,
                     AVG(m.mark) as avg_mark,
                     COUNT(CASE WHEN m.grade_letter = 'A' THEN 1 END) as grade_a_count,
                     COUNT(CASE WHEN m.grade_letter = 'B' THEN 1 END) as grade_b_count,
                     COUNT(CASE WHEN m.grade_letter = 'S' THEN 1 END) as grade_s_count,
                     COUNT(CASE WHEN m.grade_letter = 'F' THEN 1 END) as grade_f_count
                   FROM marks m 
                   JOIN student_grades sg ON m.student_id = sg.student_id 
                   JOIN grades g ON sg.grade_id = g.id 
                   WHERE m.subject_id = ? AND m.year = ? AND g.year = ?
                   GROUP BY g.grade_number
                   ORDER BY g.grade_number";
    $grade_stmt = $conn->prepare($grade_sql);
    $grade_stmt->bind_param("iii", $selected_subject, $selected_year, $selected_year);
    $grade_stmt->execute();
    $grade_result = $grade_stmt->get_result();
    
    while ($row = $grade_result->fetch_assoc()) {
        $grade_stats[] = $row;
    }
    $grade_stmt->close();
    
    // Get statistics by term
    $term_sql = "SELECT 
                    m.term,
                    COUNT(DISTINCT m.student_id) as students_count,
                    COUNT(m.id) as marks_count,
                    MIN(m.mark) as min_mark,
                    MAX(m.mark) as max_mark,
                    AVG(m.mark) as avg_mark,
                    COUNT(CASE WHEN m.grade_letter = 'A' THEN 1 END) as grade_a_count,
                    COUNT(CASE WHEN m.grade_letter = 'B' THEN 1 END) as grade_b_count,
                    COUNT(CASE WHEN m.grade_letter = 'S' THEN 1 END) as grade_s_count,
                    COUNT(CASE WHEN m.grade_letter = 'F' THEN 1 END) as grade_f_count
                  FROM marks m 
                  WHERE m.subject_id = ? AND m.year = ?
                  GROUP BY m.term
                  ORDER BY m.term";
    $term_stmt = $conn->prepare($term_sql);
    $term_stmt->bind_param("ii", $selected_subject, $selected_year);
    $term_stmt->execute();
    $term_result = $term_stmt->get_result();
    
    while ($row = $term_result->fetch_assoc()) {
        $term_stats[] = $row;
    }
    $term_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject Statistics - SchoolSync</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
        .stats-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .stats-table th {
            background: #1e3a8a;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        .stats-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        .stats-table tr:hover {
            background: #f8fafc;
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
            <h1><i class="fa-solid fa-chart-line"></i> Subject Statistics</h1>
        </div>

        <!-- Selection Form -->
        <div class="card">
            <h3><i class="fa-solid fa-filter"></i> Select Subject</h3>
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
                    <label for="subject">Subject</label>
                    <select name="subject" id="subject" onchange="this.form.submit()" <?php if (!$selected_year) echo 'disabled'; ?>>
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

        <!-- Subject Statistics -->
        <?php if ($selected_year && $selected_subject && !empty($subject_stats)): ?>
            <?php 
            $current_subject = null;
            foreach ($subjects as $subject) {
                if ($subject['id'] == $selected_subject) {
                    $current_subject = $subject;
                    break;
                }
            }
            ?>
            
            <!-- Subject Header -->
            <div class="subject-header">
                <h2><?php echo htmlspecialchars($current_subject['name']); ?></h2>
                <p><?php echo htmlspecialchars($current_subject['description']); ?></p>
                <p>Academic Year <?php echo $selected_year; ?></p>
            </div>
            
            <!-- Overall Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><i class="fa-solid fa-users"></i> Total Students</h3>
                    <div class="stat-value"><?php echo $subject_stats['total_students']; ?></div>
                    <div class="stat-label">Enrolled Students</div>
                </div>
                
                <div class="stat-card">
                    <h3><i class="fa-solid fa-chart-line"></i> Average Mark</h3>
                    <div class="stat-value"><?php echo round($subject_stats['avg_mark'], 1); ?>%</div>
                    <div class="stat-label">Overall Average</div>
                </div>
                
                <div class="stat-card">
                    <h3><i class="fa-solid fa-trophy"></i> Highest Mark</h3>
                    <div class="stat-value"><?php echo $subject_stats['max_mark']; ?>%</div>
                    <div class="stat-label">Best Performance</div>
                </div>
                
                <div class="stat-card">
                    <h3><i class="fa-solid fa-chart-area"></i> Lowest Mark</h3>
                    <div class="stat-value"><?php echo $subject_stats['min_mark']; ?>%</div>
                    <div class="stat-label">Needs Improvement</div>
                </div>
            </div>
            
            <!-- Grade Distribution -->
            <div class="card">
                <h3><i class="fa-solid fa-pie-chart"></i> Overall Grade Distribution</h3>
                <div class="grade-distribution">
                    <div class="grade-item grade-a">
                        <div style="font-size: 1.5rem;"><?php echo $subject_stats['grade_a_count']; ?></div>
                        <div style="font-size: 0.8rem;">Grade A</div>
                    </div>
                    <div class="grade-item grade-b">
                        <div style="font-size: 1.5rem;"><?php echo $subject_stats['grade_b_count']; ?></div>
                        <div style="font-size: 0.8rem;">Grade B</div>
                    </div>
                    <div class="grade-item grade-s">
                        <div style="font-size: 1.5rem;"><?php echo $subject_stats['grade_s_count']; ?></div>
                        <div style="font-size: 0.8rem;">Grade S</div>
                    </div>
                    <div class="grade-item grade-f">
                        <div style="font-size: 1.5rem;"><?php echo $subject_stats['grade_f_count']; ?></div>
                        <div style="font-size: 0.8rem;">Grade F</div>
                    </div>
                </div>
            </div>
            
            <!-- Performance by Grade -->
            <?php if (!empty($grade_stats)): ?>
                <div class="card">
                    <h3><i class="fa-solid fa-graduation-cap"></i> Performance by Grade Level</h3>
                    <div class="chart-container">
                        <canvas id="gradeChart" width="400" height="200"></canvas>
                    </div>
                    
                    <table class="stats-table">
                        <thead>
                            <tr>
                                <th>Grade</th>
                                <th>Students</th>
                                <th>Average</th>
                                <th>Highest</th>
                                <th>Lowest</th>
                                <th>Grade Distribution</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($grade_stats as $grade): ?>
                                <tr>
                                    <td><strong>Grade <?php echo $grade['grade_number']; ?></strong></td>
                                    <td><?php echo $grade['students_count']; ?></td>
                                    <td><strong style="color: #059669;"><?php echo round($grade['avg_mark'], 1); ?>%</strong></td>
                                    <td style="color: #10b981;"><?php echo $grade['max_mark']; ?>%</td>
                                    <td style="color: #ef4444;"><?php echo $grade['min_mark']; ?>%</td>
                                    <td>
                                        <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                            <?php if ($grade['grade_a_count'] > 0): ?>
                                                <span class="grade-item grade-a" style="font-size: 0.8rem; padding: 2px 6px;"><?php echo $grade['grade_a_count']; ?>A</span>
                                            <?php endif; ?>
                                            <?php if ($grade['grade_b_count'] > 0): ?>
                                                <span class="grade-item grade-b" style="font-size: 0.8rem; padding: 2px 6px;"><?php echo $grade['grade_b_count']; ?>B</span>
                                            <?php endif; ?>
                                            <?php if ($grade['grade_s_count'] > 0): ?>
                                                <span class="grade-item grade-s" style="font-size: 0.8rem; padding: 2px 6px;"><?php echo $grade['grade_s_count']; ?>S</span>
                                            <?php endif; ?>
                                            <?php if ($grade['grade_f_count'] > 0): ?>
                                                <span class="grade-item grade-f" style="font-size: 0.8rem; padding: 2px 6px;"><?php echo $grade['grade_f_count']; ?>F</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            
            <!-- Performance by Term -->
            <?php if (!empty($term_stats)): ?>
                <div class="card">
                    <h3><i class="fa-solid fa-calendar-days"></i> Performance by Term</h3>
                    <div class="chart-container">
                        <canvas id="termChart" width="400" height="200"></canvas>
                    </div>
                    
                    <table class="stats-table">
                        <thead>
                            <tr>
                                <th>Term</th>
                                <th>Students</th>
                                <th>Average</th>
                                <th>Highest</th>
                                <th>Lowest</th>
                                <th>Grade Distribution</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($term_stats as $term): ?>
                                <tr>
                                    <td><strong><?php echo ucfirst($term['term']); ?> Term</strong></td>
                                    <td><?php echo $term['students_count']; ?></td>
                                    <td><strong style="color: #059669;"><?php echo round($term['avg_mark'], 1); ?>%</strong></td>
                                    <td style="color: #10b981;"><?php echo $term['max_mark']; ?>%</td>
                                    <td style="color: #ef4444;"><?php echo $term['min_mark']; ?>%</td>
                                    <td>
                                        <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                            <?php if ($term['grade_a_count'] > 0): ?>
                                                <span class="grade-item grade-a" style="font-size: 0.8rem; padding: 2px 6px;"><?php echo $term['grade_a_count']; ?>A</span>
                                            <?php endif; ?>
                                            <?php if ($term['grade_b_count'] > 0): ?>
                                                <span class="grade-item grade-b" style="font-size: 0.8rem; padding: 2px 6px;"><?php echo $term['grade_b_count']; ?>B</span>
                                            <?php endif; ?>
                                            <?php if ($term['grade_s_count'] > 0): ?>
                                                <span class="grade-item grade-s" style="font-size: 0.8rem; padding: 2px 6px;"><?php echo $term['grade_s_count']; ?>S</span>
                                            <?php endif; ?>
                                            <?php if ($term['grade_f_count'] > 0): ?>
                                                <span class="grade-item grade-f" style="font-size: 0.8rem; padding: 2px 6px;"><?php echo $term['grade_f_count']; ?>F</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            
        <?php elseif ($selected_year && $selected_subject): ?>
            <div class="card">
                <div class="no-data">
                    <i class="fa-solid fa-chart-line fa-3x"></i>
                    <h3>No Data Available</h3>
                    <p>No statistics available for the selected subject criteria.</p>
                </div>
            </div>
        <?php elseif ($selected_year): ?>
            <div class="card">
                <div class="no-data">
                    <i class="fa-solid fa-book fa-3x"></i>
                    <h3>Select Subject</h3>
                    <p>Please select a subject to view statistics.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="no-data">
                    <i class="fa-solid fa-calendar fa-3x"></i>
                    <h3>Select Academic Year</h3>
                    <p>Please select an academic year to start viewing subject statistics.</p>
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

        // Grade Performance Chart
        <?php if (!empty($grade_stats)): ?>
        const gradeCtx = document.getElementById('gradeChart').getContext('2d');
        new Chart(gradeCtx, {
            type: 'bar',
            data: {
                labels: [<?php echo implode(',', array_map(function($grade) { return "'Grade " . $grade['grade_number'] . "'"; }, $grade_stats)); ?>],
                datasets: [{
                    label: 'Average Mark (%)',
                    data: [<?php echo implode(',', array_map(function($grade) { return round($grade['avg_mark'], 1); }, $grade_stats)); ?>],
                    backgroundColor: 'rgba(16, 185, 129, 0.8)',
                    borderColor: 'rgba(16, 185, 129, 1)',
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
                        text: 'Performance by Grade Level'
                    }
                }
            }
        });
        <?php endif; ?>

        // Term Performance Chart
        <?php if (!empty($term_stats)): ?>
        const termCtx = document.getElementById('termChart').getContext('2d');
        new Chart(termCtx, {
            type: 'line',
            data: {
                labels: [<?php echo implode(',', array_map(function($term) { return "'" . ucfirst($term['term']) . " Term'"; }, $term_stats)); ?>],
                datasets: [{
                    label: 'Average Mark (%)',
                    data: [<?php echo implode(',', array_map(function($term) { return round($term['avg_mark'], 1); }, $term_stats)); ?>],
                    backgroundColor: 'rgba(139, 92, 246, 0.2)',
                    borderColor: 'rgba(139, 92, 246, 1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
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
                        text: 'Performance by Term'
                    }
                }
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>
<?php $conn->close(); ?>
