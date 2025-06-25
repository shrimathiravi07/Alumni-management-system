<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "nscet_alumni");

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Process registration form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_type = $_POST['userType'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $mobile = $_POST['mobile'] ?? '';
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    // Validate inputs
    if (empty($user_type) || !in_array($user_type, ['student', 'alumni'])) {
        echo json_encode(["status" => "error", "message" => "Invalid user type."]);
        exit;
    }

    if (empty($username) || strlen($username) > 50) {
        echo json_encode(["status" => "error", "message" => "Username is required and must be 50 characters or less."]);
        exit;
    }

    if (!preg_match("/^[a-zA-Z0-9_]+$/", $username)) {
        echo json_encode(["status" => "error", "message" => "Username can only contain letters, numbers, and underscores."]);
        exit;
    }

    if (!preg_match("/^\d{10,12}$/", $mobile)) {
        echo json_encode(["status" => "error", "message" => "Mobile number must be 10 to 12 digits."]);
        exit;
    }

    if (empty($fullname)) {
        echo json_encode(["status" => "error", "message" => "Full name is required."]);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Invalid email format."]);
        exit;
    }

    if ($password !== $confirmPassword) {
        echo json_encode(["status" => "error", "message" => "Passwords do not match."]);
        exit;
    }

    // Password strength check
    if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/", $password)) {
        echo json_encode(["status" => "error", "message" => "Password must include uppercase, lowercase, number, special character, and be 8+ characters."]);
        exit;
    }

    // Check if username, email, or mobile already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ? OR mobile = ?");
    $stmt->bind_param("sss", $username, $email, $mobile);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Username, email, or mobile number already registered."]);
        $stmt->close();
        exit;
    }
    $stmt->close();

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into database
    $stmt = $conn->prepare("INSERT INTO users (username, email, mobile, fullname, password, user_type) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $username, $email, $mobile, $fullname, $hashed_password, $user_type);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Registration successful!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Registration failed: " . $conn->error]);
    }

    $stmt->close();
}

$conn->close();
?>