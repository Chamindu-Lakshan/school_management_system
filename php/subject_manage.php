<?php
include 'config.php';
if (!isset($_SESSION['loggedin'])) {
    header("Location: ../index.html");
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = $_POST['name'];
                $description = $_POST['description'];
                $sql = "INSERT INTO subjects (name, description) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $name, $description);
                $stmt->execute();
                break;
                
            case 'edit':
                $id = $_POST['id'];
    $name = $_POST['name'];
                $description = $_POST['description'];
                $sql = "UPDATE subjects SET name=?, description=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssi", $name, $description, $id);
                $stmt->execute();
                break;
                
            case 'delete':
                $id = $_POST['id'];
                $sql = "DELETE FROM subjects WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                break;
        }
        header("Location: subject_manage.php");
        exit;
    }
}

$sql_subjects = "SELECT * FROM subjects ORDER BY name";
$result_subjects = $conn->query($sql_subjects);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subjects - SchoolSync</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="../js/scripts.js"></script>
    <style>
        .subject-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .subject-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            position: relative;
        }
        .subject-card:hover {
            transform: translateY(-4px);
        }
        .subject-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        .subject-title {
            margin: 0;
            color: #1e3a8a;
            font-size: 1.3rem;
        }
        .subject-actions {
            display: flex;
            gap: 8px;
        }
        .btn-edit, .btn-delete {
            padding: 6px 10px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }
        .btn-edit {
            background: #3b82f6;
            color: white;
        }
        .btn-delete {
            background: #ef4444;
            color: white;
        }
        .btn-edit:hover, .btn-delete:hover {
            transform: scale(1.05);
        }
        .subject-description {
            color: #64748b;
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 15px;
        }
        .subject-stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
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
        
        /* Modal Styles */
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
            max-width: 500px;
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
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #1e3a8a;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
            box-sizing: border-box;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e5e7eb;
        }
        .modal-header h2 {
            margin: 0;
            color: #1e3a8a;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container">
        <div class="header-container">
            <h1>Manage Subjects</h1>
            <button onclick="openAddModal()" style="background: linear-gradient(135deg, #10b981, #059669);">
                <i class="fa-solid fa-plus"></i> Add New Subject
            </button>
        </div>

        <div class="subject-grid">
            <?php if ($result_subjects->num_rows > 0): ?>
                <?php while($row = $result_subjects->fetch_assoc()): ?>
                    <div class="subject-card">
                        <div class="subject-header">
                            <h3 class="subject-title"><?php echo htmlspecialchars($row['name']); ?></h3>
                            <div class="subject-actions">
                                <button class="btn-edit" onclick="openEditModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name']); ?>', '<?php echo htmlspecialchars(isset($row['description']) ? $row['description'] : ''); ?>')">
                                    <i class="fa-solid fa-edit"></i> Edit
                                </button>
                                <button class="btn-delete" onclick="deleteSubject(<?php echo $row['id']; ?>)">
                                    <i class="fa-solid fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                        <div class="subject-description">
                            <?php echo htmlspecialchars(isset($row['description']) ? $row['description'] : 'No description available'); ?>
                        </div>
                        <div class="subject-stats">
                            <div class="stat-item">
                                <div class="stat-value"><?php 
                                    $sql_count = "SELECT COUNT(*) as count FROM enrollments WHERE subject_id = ?";
                                    $stmt = $conn->prepare($sql_count);
                                    $stmt->bind_param("i", $row['id']);
                                    $stmt->execute();
                                    $count_result = $stmt->get_result()->fetch_assoc();
                                    echo $count_result['count'];
                                ?></div>
                                <div class="stat-label">Students</div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #64748b;">
                    <i class="fa-solid fa-book" style="font-size: 3rem; margin-bottom: 20px; display: block;"></i>
                    <h3>No Subjects Found</h3>
                    <p>Add your first subject to get started.</p>
                </div>
            <?php endif; ?>
        </div>
        
    </div>

    <!-- Add Subject Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Subject</h2>
                <span class="close" onclick="closeModal('addModal')">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label>Subject Name *</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" placeholder="Enter subject description..."></textarea>
                </div>
                <div style="display: flex; gap: 15px; margin-top: 20px;">
                    <button type="submit" style="flex: 1;">Add Subject</button>
                    <button type="button" onclick="closeModal('addModal')" style="flex: 1; background: #6b7280;">Cancel</button>
                </div>
            </form>
        </div>
        </div>

    <!-- Edit Subject Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Subject</h2>
                <span class="close" onclick="closeModal('editModal')">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="editId">
                <div class="form-group">
                    <label>Subject Name *</label>
                    <input type="text" name="name" id="editName" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="editDescription" placeholder="Enter subject description..."></textarea>
                </div>
                <div style="display: flex; gap: 15px; margin-top: 20px;">
                    <button type="submit" style="flex: 1;">Update Subject</button>
                    <button type="button" onclick="closeModal('editModal')" style="flex: 1; background: #6b7280;">Cancel</button>
                </div>
            </form>
                </div>
        </div>
        
    <script>
        function openAddModal() {
            document.getElementById('addModal').style.display = 'block';
        }

        function openEditModal(id, name, description) {
            document.getElementById('editId').value = id;
            document.getElementById('editName').value = name;
            document.getElementById('editDescription').value = description;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function deleteSubject(id) {
            if (confirm('Are you sure you want to delete this subject?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
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