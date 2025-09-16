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
                $full_name = $_POST['name'];
                $gender = $_POST['gender'];
                $birth_date = $_POST['birth_date'];
                $address = $_POST['address'];
                $phone = $_POST['phone'];
                $email = $_POST['email'];
                $religion = $_POST['religion'];
                $father_name = $_POST['father_name'];
                $mother_name = $_POST['mother_name'];
                $guardian_name = $_POST['guardian_name'];
                $guardian_phone = $_POST['guardian_phone'];
                $guardian_email = $_POST['guardian_email'];
                $special_details = $_POST['special_details'];
                $status = $_POST['status'];
                
                $sql = "INSERT INTO students (full_name, gender, birth_date, address, phone, email, religion, father_name, mother_name, guardian_name, guardian_phone, guardian_email, special_details, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssssssssssss", $full_name, $gender, $birth_date, $address, $phone, $email, $religion, $father_name, $mother_name, $guardian_name, $guardian_phone, $guardian_email, $special_details, $status);
                $stmt->execute();
                break;
                
            case 'edit':
                $id = $_POST['id'];
                $full_name = $_POST['name'];
                $gender = $_POST['gender'];
                $birth_date = $_POST['birth_date'];
                $address = $_POST['address'];
                $phone = $_POST['phone'];
                $email = $_POST['email'];
                $religion = $_POST['religion'];
                $father_name = $_POST['father_name'];
                $mother_name = $_POST['mother_name'];
                $guardian_name = $_POST['guardian_name'];
                $guardian_phone = $_POST['guardian_phone'];
                $guardian_email = $_POST['guardian_email'];
                $special_details = $_POST['special_details'];
                $status = $_POST['status'];
                
                $sql = "UPDATE students SET full_name=?, gender=?, birth_date=?, address=?, phone=?, email=?, religion=?, father_name=?, mother_name=?, guardian_name=?, guardian_phone=?, guardian_email=?, special_details=?, status=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssssssssssssi", $full_name, $gender, $birth_date, $address, $phone, $email, $religion, $father_name, $mother_name, $guardian_name, $guardian_phone, $guardian_email, $special_details, $status, $id);
                $stmt->execute();
                break;
                
            case 'delete':
                $id = $_POST['id'];
                $sql = "DELETE FROM students WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                break;
        }
        header("Location: student_manage.php");
        exit;
    }
}

// Fetch all students
$sql = "SELECT * FROM students ORDER BY id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SchoolSync - Manage Students</title>
    <link rel="stylesheet" href="../css/styles.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="../js/scripts.js"></script>
    <style>
        .student-grid {
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
            cursor: pointer;
        }
        .student-card:hover {
            transform: translateY(-4px);
        }
        .student-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
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
        }
        .student-info p {
            margin: 5px 0 0 0;
            color: #64748b;
            font-size: 0.9rem;
        }
        .student-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .btn-edit, .btn-delete {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
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
        
        /* ID Column Styling */
        .students-table td:first-child {
            font-weight: bold;
            color: #1e3a8a;
            background-color: #f8fafc;
            text-align: center;
            min-width: 60px;
        }
        
        .students-table th:first-child {
            background-color: #1e3a8a;
            color: white;
            text-align: center;
        }
        
        /* Table Styling */
        .students-table-container {
            margin-top: 20px;
            overflow-x: auto;
        }
        
        .students-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .students-table th {
            background-color: #f8fafc;
            color: #374151;
            font-weight: 600;
            padding: 15px 12px;
            text-align: left;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .students-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
        }
        
        .students-table tr:hover {
            background-color: #f9fafb;
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
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            margin-bottom: 5px;
            font-weight: 500;
            color: #1e3a8a;
        }
        .form-group input, .form-group select, .form-group textarea {
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.9rem;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
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
        .students-table-container {
            overflow-x: auto;
        }
        .students-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .students-table th, .students-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        .students-table th {
            background-color: #f3f4f6;
            color: #1e3a8a;
            font-weight: 600;
            position: sticky;
            top: 0;
        }
        .students-table tbody tr:hover {
            background-color: #f9fafb;
        }
        .students-table .btn-edit {
            background: #3b82f6;
            color: white;
            padding: 6px 10px;
        }
        .students-table .btn-delete {
            background: #ef4444;
            color: white;
            padding: 6px 10px;
        }
        .students-table .btn-edit:hover, .students-table .btn-delete:hover {
            transform: scale(1.03);
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>  

    <div class="container">
        <div class="header-container">
            <h1>Manage Students</h1>
            <button onclick="openAddModal()" style="background: linear-gradient(135deg, #10b981, #059669);">
                <i class="fa-solid fa-plus"></i> Add New Student
            </button>
        </div>

        <!-- Students Table -->
        <div class="students-table-container">
            <?php if ($result->num_rows > 0): ?>
                <table class="students-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student</th>
                            <th>Gender</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo $row['id']; ?></strong></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div class="student-avatar">
                                            <i class="fa-solid fa-user"></i>
                                        </div>
                                        <div>
                                            <strong><?php echo htmlspecialchars($row['full_name']); ?></strong>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($row['gender']); ?></td>
                                <td><?php echo $row['phone'] ? htmlspecialchars($row['phone']) : 'Not provided'; ?></td>
                                <td><?php echo $row['email'] ? htmlspecialchars($row['email']) : 'Not provided'; ?></td>
                                <td><?php echo ucfirst($row['status']); ?></td>
                                <td>
                                    <div style="display: flex; gap: 10px;">
                                        <button class="btn-edit" onclick="openStudentModal(<?php echo $row['id']; ?>)">
                                            <i class="fa-solid fa-eye"></i> View
                                        </button>
                                        <button class="btn-edit" onclick="openEditModal(<?php echo $row['id']; ?>)">
                                            <i class="fa-solid fa-edit"></i> Edit
                                        </button>
                                        <button class="btn-delete" onclick="deleteStudent(<?php echo $row['id']; ?>)">
                                            <i class="fa-solid fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #64748b;">
                    <i class="fa-solid fa-users" style="font-size: 3rem; margin-bottom: 20px; display: block;"></i>
                    <h3>No Students Found</h3>
                    <p>Add your first student to get started.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Student Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
            <h2>Add New Student</h2>
                <span class="close" onclick="closeModal('addModal')">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Gender *</label>
                <select name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Birth Date *</label>
                        <input type="date" name="birth_date" required>
                    </div>
                    <div class="form-group">
                        <label>Status *</label>
                        <select name="status" required>
                            <option value="">Select Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="left">Left</option>
                            <option value="transferred">Transferred</option>
                </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="tel" name="phone">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email">
                    </div>
                </div>
                <div class="form-group">
                    <label>Religion</label>
                    <input type="text" name="religion">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Father's Name</label>
                        <input type="text" name="father_name">
                    </div>
                    <div class="form-group">
                        <label>Mother's Name</label>
                        <input type="text" name="mother_name">
                    </div>
                </div>
                <div class="form-group">
                    <label>Guardian's Name</label>
                    <input type="text" name="guardian_name">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Guardian's Phone</label>
                        <input type="tel" name="guardian_phone">
                    </div>
                    <div class="form-group">
                        <label>Guardian's Email</label>
                        <input type="email" name="guardian_email">
                    </div>
                </div>
                <div class="form-group">
                    <label>Special Details</label>
                    <textarea name="special_details" placeholder="Any special requirements or notes..."></textarea>
                </div>
                <div style="display: flex; gap: 15px; margin-top: 20px;">
                    <button type="submit" style="flex: 1;">Add Student</button>
                    <button type="button" onclick="closeModal('addModal')" style="flex: 1; background: #6b7280;">Cancel</button>
                </div>
            </form>
        </div>
        </div>

    <!-- Edit Student Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Student</h2>
                <span class="close" onclick="closeModal('editModal')">&times;</span>
            </div>
            <form method="POST" id="editForm">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="editId">
                <!-- Same form fields as add modal -->
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="name" id="editName" required>
                    </div>
                    <div class="form-group">
                        <label>Gender *</label>
                        <select name="gender" id="editGender" required>
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Birth Date *</label>
                        <input type="date" name="birth_date" id="editBirthDate" required>
                    </div>
                    <div class="form-group">
                        <label>Status *</label>
                        <select name="status" id="editStatus" required>
                            <option value="">Select Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="left">Left</option>
                            <option value="transferred">Transferred</option>
                                </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" id="editAddress"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="tel" name="phone" id="editPhone">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" id="editEmail">
                    </div>
                </div>
                <div class="form-group">
                    <label>Religion</label>
                    <input type="text" name="religion" id="editReligion">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Father's Name</label>
                        <input type="text" name="father_name" id="editFatherName">
                    </div>
                    <div class="form-group">
                        <label>Mother's Name</label>
                        <input type="text" name="mother_name" id="editMotherName">
                    </div>
                </div>
                <div class="form-group">
                    <label>Guardian's Name</label>
                    <input type="text" name="guardian_name" id="editGuardianName">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Guardian's Phone</label>
                        <input type="tel" name="guardian_phone" id="editGuardianPhone">
                    </div>
                    <div class="form-group">
                        <label>Guardian's Email</label>
                        <input type="email" name="guardian_email" id="editGuardianEmail">
                    </div>
                </div>
                <div class="form-group">
                    <label>Special Details</label>
                    <textarea name="special_details" id="editSpecialDetails"></textarea>
                </div>
                <div style="display: flex; gap: 15px; margin-top: 20px;">
                    <button type="submit" style="flex: 1;">Update Student</button>
                    <button type="button" onclick="closeModal('editModal')" style="flex: 1; background: #6b7280;">Cancel</button>
                </div>
                            </form>
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
        function openAddModal() {
            document.getElementById('addModal').style.display = 'block';
        }

        function openEditModal(id) {
            // Fetch student data and populate form
            fetch(`get_student.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('editId').value = data.id;
                    document.getElementById('editName').value = data.full_name;
                    document.getElementById('editGender').value = data.gender;
                    document.getElementById('editBirthDate').value = data.birth_date;
                    document.getElementById('editStatus').value = data.status;
                    document.getElementById('editAddress').value = data.address;
                    document.getElementById('editPhone').value = data.phone;
                    document.getElementById('editEmail').value = data.email;
                    document.getElementById('editReligion').value = data.religion;
                    document.getElementById('editFatherName').value = data.father_name;
                    document.getElementById('editMotherName').value = data.mother_name;
                    document.getElementById('editGuardianName').value = data.guardian_name;
                    document.getElementById('editGuardianPhone').value = data.guardian_phone;
                    document.getElementById('editGuardianEmail').value = data.guardian_email;
                    document.getElementById('editSpecialDetails').value = data.special_details;
                    document.getElementById('editModal').style.display = 'block';
                });
        }

        function openStudentModal(id) {
            // Fetch and display student details
            fetch(`get_student.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('studentDetails').innerHTML = `
                        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 30px; padding: 20px; background: #f8fafc; border-radius: 8px;">
                            <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6, #8b5cf6); display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem;">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <div>
                                <h3 style="margin: 0; color: #1e3a8a; font-size: 1.5rem;">${data.full_name}</h3>
                                <p style="margin: 5px 0; color: #64748b; font-size: 1rem;"><strong>Student ID:</strong> ${data.id}</p>
                                <p style="margin: 5px 0; color: #64748b; font-size: 1rem;"><strong>Gender:</strong> ${data.gender} • <strong>Status:</strong> ${data.status}</p>
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                            <div style="background: #f8fafc; padding: 20px; border-radius: 8px; border-left: 4px solid #3b82f6;">
                                <h4 style="color: #1e3a8a; margin-bottom: 15px; font-size: 1.1rem; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">Personal Information</h4>
                                <div style="display: grid; gap: 12px;">
                                    <div><strong style="color: #374151;">Birth Date:</strong> <span style="color: #64748b;">${data.birth_date}</span></div>
                                    <div><strong style="color: #374151;">Address:</strong> <span style="color: #64748b;">${data.address || 'Not provided'}</span></div>
                                    <div><strong style="color: #374151;">Phone:</strong> <span style="color: #64748b;">${data.phone || 'Not provided'}</span></div>
                                    <div><strong style="color: #374151;">Email:</strong> <span style="color: #64748b;">${data.email || 'Not provided'}</span></div>
                                    <div><strong style="color: #374151;">Religion:</strong> <span style="color: #64748b;">${data.religion || 'Not provided'}</span></div>
                                </div>
                            </div>
                            
                            <div style="background: #f8fafc; padding: 20px; border-radius: 8px; border-left: 4px solid #10b981;">
                                <h4 style="color: #1e3a8a; margin-bottom: 15px; font-size: 1.1rem; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">Family Information</h4>
                                <div style="display: grid; gap: 12px;">
                                    <div><strong style="color: #374151;">Father:</strong> <span style="color: #64748b;">${data.father_name || 'Not provided'}</span></div>
                                    <div><strong style="color: #374151;">Mother:</strong> <span style="color: #64748b;">${data.mother_name || 'Not provided'}</span></div>
                                    <div><strong style="color: #374151;">Guardian:</strong> <span style="color: #64748b;">${data.guardian_name || 'Not provided'}</span></div>
                                    <div><strong style="color: #374151;">Guardian Phone:</strong> <span style="color: #64748b;">${data.guardian_phone || 'Not provided'}</span></div>
                                    <div><strong style="color: #374151;">Guardian Email:</strong> <span style="color: #64748b;">${data.guardian_email || 'Not provided'}</span></div>
                                </div>
                            </div>
                        </div>
                        
                        ${data.special_details ? `
                            <div style="margin-top: 30px; background: #fef3c7; padding: 20px; border-radius: 8px; border-left: 4px solid #f59e0b;">
                                <h4 style="color: #1e3a8a; margin-bottom: 15px; font-size: 1.1rem; border-bottom: 2px solid #fde68a; padding-bottom: 8px;">Special Details</h4>
                                <p style="color: #92400e; margin: 0; line-height: 1.6;">${data.special_details}</p>
                            </div>
                        ` : ''}
                    `;
                    document.getElementById('studentModal').style.display = 'block';
                });
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function deleteStudent(id) {
            if (confirm('Are you sure you want to delete this student?')) {
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