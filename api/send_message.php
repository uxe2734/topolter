<?php
require_once '../init.php';
header("Content-Type: application/json");
require "../config/db.php";

$sender = $_SESSION['user_id'];
$receiver = (int)$_POST['receiver_id'];
$message = trim($_POST['message']);

if(strlen($message) > 10000){
    exit(json_encode(["error"=>"Message is too long"]));
}
require_once '../config/crypto.php';

function encryptMessage($message){
    $method = "AES-256-CBC";
    $key = hash('sha256', SECRET_KEY);
    $iv  = substr(hash('sha256', SECRET_IV), 0, 16);
    return base64_encode(openssl_encrypt($message, $method, $key, 0, $iv));
}
$encrypted = encryptMessage($message);
$stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, is_read) VALUES (?,?,?,0)");
$stmt->bind_param("iis", $sender, $receiver, $encrypted);
$stmt->execute();

echo json_encode(["status"=>"ok"]);