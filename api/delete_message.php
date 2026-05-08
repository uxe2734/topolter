<?php
require_once '../init.php';
require "../config/db.php";

header("Content-Type: application/json");

// Check login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status"=>"error","error"=>"You are not logged in"]);
    exit;
}

if (!isset($_POST['message_id'])) {
    echo json_encode(["status"=>"error","error"=>"Message ID not provided"]);
    exit;
}

$message_id = (int)$_POST['message_id'];
$user_id = $_SESSION['user_id'];

// Only allow sender to delete
$stmt = $conn->prepare("DELETE FROM messages WHERE id=? AND sender_id=?");
$stmt->bind_param("ii", $message_id, $user_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(["status"=>"ok"]);
} else {
    echo json_encode(["status"=>"error","error"=>"You are not allowed to delete this message"]);
}