<?php
session_start();
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "nscet_alumni");

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if user is a student
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'student') {
        echo json_encode(["status" => "error", "message" => "Only students can submit questions."]);
        exit;
    }

    $student_id = $_SESSION['user_id'];
    $data = json_decode(file_get_contents('php://input'), true);
    $question = trim($data['question'] ?? '');

    if (empty($question)) {
        echo json_encode(["status" => "error", "message" => "Question cannot be empty."]);
        exit;
    }

    if (strlen($question) > 255) {
        echo json_encode(["status" => "error", "message" => "Question must be 255 characters or less."]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO faqs (student_id, question, status) VALUES (?, ?, 'pending')");
    $stmt->bind_param("is", $student_id, $question);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Question submitted successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to submit question."]);
    }

    $stmt->close();
}

$conn->close();
?>