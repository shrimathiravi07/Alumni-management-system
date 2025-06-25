<?php
session_start();
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "nscet_alumni");

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if user is an alumni
    if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'alumni') {
        echo json_encode(["status" => "error", "message" => "Only alumni can answer FAQs."]);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $faq_id = $data['faq_id'] ?? 0;
    $answer = trim($data['answer'] ?? '');

    if ($faq_id <= 0 || empty($answer)) {
        echo json_encode(["status" => "error", "message" => "Invalid FAQ ID or empty answer."]);
        exit;
    }

    $stmt = $conn->prepare("UPDATE faqs SET answer = ?, status = 'answered' WHERE id = ? AND status = 'pending'");
    $stmt->bind_param("si", $answer, $faq_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(["status" => "success", "message" => "Answer submitted successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "FAQ already answered or not found."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to submit answer."]);
    }

    $stmt->close();
}

$conn->close();
?>