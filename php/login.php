<?php
include 'config.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Then check database for other users
    $sql = "SELECT * FROM users WHERE username=? AND status='active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['full_name'] = $row['full_name'];
            
                    echo json_encode(array(
            'success' => true,
            'message' => 'Login successful!',
            'redirect' => 'php/dashboard.php'
        ));
            exit();
        } else {
            echo json_encode(array(
                'success' => false,
                'message' => 'Invalid password'
            ));
        }
    } else {
        echo json_encode(array(
            'success' => false,
            'message' => 'Invalid username'
        ));
    }
    $stmt->close();
} else {
    echo json_encode(array(
        'success' => false,
        'message' => 'Invalid request method'
    ));
}

$conn->close();
?>