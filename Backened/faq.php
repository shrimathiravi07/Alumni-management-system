<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "nscet_alumni");

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]);
    exit;
}

$status = $_GET['status'] ?? '';
$sql = "SELECT id, question, answer, status FROM faqs";
if ($status === 'pending') {
    $sql .= " WHERE status = 'pending'";
}
$sql .= " ORDER BY created_at DESC";

$result = $conn->query($sql);

$faqs = [];
while ($row = $result->fetch_assoc()) {
    $faqs[] = $row;
}

echo json_encode($faqs);

$conn->close();
?>