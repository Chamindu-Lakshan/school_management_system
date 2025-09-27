<?php
session_start();
include 'config.php';

if (!isset($_SESSION['loggedin'])) {
    http_response_code(401);
    echo json_encode(array('error' => 'Unauthorized'));
    exit;
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(array('error' => 'Grade ID is required'));
    exit;
}

$grade_id = (int)$_GET['id'];

$sql = "SELECT id, grade_number, class_name, year, status FROM grades WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $grade_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $grade = $result->fetch_assoc();
    echo json_encode($grade);
} else {
    http_response_code(404);
    echo json_encode(array('error' => 'Grade not found'));
}

$stmt->close();
$conn->close();
?>
