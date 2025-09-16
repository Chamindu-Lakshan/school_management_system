<?php
include 'config.php';

if (!isset($_SESSION['loggedin'])) {
    http_response_code(401);
    exit('Unauthorized');
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $sql = "SELECT * FROM students WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        header('Content-Type: application/json');
        echo json_encode($student);
    } else {
        http_response_code(404);
        echo json_encode(array('error' => 'Student not found'));
    }
} else {
    http_response_code(400);
    echo json_encode(array('error' => 'Student ID required'));
}

$conn->close();
?>
