<?php
require_once '../init.php';
require "../config/db.php";

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT sender_id, COUNT(*) as count
    FROM messages
    WHERE receiver_id = ? AND is_read = 0
    GROUP BY sender_id
");

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$data = [];
while($row = $result->fetch_assoc()){
    $data[] = $row;
}

echo json_encode($data);