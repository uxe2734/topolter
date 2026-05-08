<?php
require_once '../init.php';
header("Content-Type: application/json");
require "../config/db.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error"=>"User is not logged in"]);
    exit;
}

$my_id = $_SESSION['user_id'];

// Get other user's id from query string
if (!isset($_GET['user_id'])) {
    echo json_encode(["error"=>"Target user not specified"]);
    exit;
}

$other_id = (int)$_GET['user_id'];

// Get messages between two users
$stmt = $conn->prepare("
    SELECT id, sender_id, receiver_id, message, file_path, file_type, created_at
    FROM messages
    WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?)
    ORDER BY created_at ASC
");
$stmt->bind_param("iiii", $my_id, $other_id, $other_id, $my_id);
$stmt->execute();
$result = $stmt->get_result();
require_once '../config/crypto.php';

function decryptMessage($message){
    $method = "AES-256-CBC";
    $key = hash('sha256', SECRET_KEY);
    $iv  = substr(hash('sha256', SECRET_IV), 0, 16);
    return openssl_decrypt(base64_decode($message), $method, $key, 0, $iv);
}


function timeAgo($datetime){
    $time = strtotime($datetime);
    $diff = time() - $time;

    if($diff < 60){
        return $diff . " seconds ago";
    } elseif($diff < 3600){
        return floor($diff/60) . " minutes ago";
    } elseif($diff < 86400){
        return floor($diff/3600) . " hours ago";
    } else {
        return floor($diff/86400) . " days ago";
    }
}

$messages = [];

while ($row = $result->fetch_assoc()) {

    $message = "";

    if (!empty($row['message'])) {
        $message = decryptMessage($row['message']);
    }

    $messages[] = [
        "id" => $row['id'],
        "sender_id" => $row['sender_id'],
        "receiver_id" => $row['receiver_id'],
        "message" => $message,
        "file_path" => $row['file_path'],
        "file_type" => $row['file_type'],
        "time" => timeAgo($row['created_at'])
    ];
}

// JSON output
echo json_encode($messages);