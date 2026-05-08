<?php
require_once '../init.php';
require "../config/db.php";

$user_id = $_SESSION['user_id'];
$other_id = (int)$_POST['user_id'];

$stmt = $conn->prepare("
    UPDATE messages 
    SET is_read = 1 
    WHERE receiver_id = ? AND sender_id = ?
");

$stmt->bind_param("ii", $user_id, $other_id);
$stmt->execute();

echo json_encode(["status"=>"ok"]);