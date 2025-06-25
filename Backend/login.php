<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "nscet_alumni");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $user_type = isset($_POST['user_type']) ? $_POST['user_type'] : '';

    // Validate user
    $stmt = $conn->prepare("SELECT id, user_type, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $db_user_type, $db_password, $db_role);
        $stmt->fetch();

        if (password_verify($password, $db_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['user_type'] = $db_user_type;
            $_SESSION['role'] = $db_role;

            // Redirect based on user type
            if ($db_user_type == 'student') {
                header("Location: student_dashboard.html");
            } else {
                header("Location: alumini.html");
            }
            exit;
        } else {
            echo json_encode(["status" => "error", "message" => "Invalid password."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Email not found."]);
    }

    $stmt->close();
}

$conn->close();
?>