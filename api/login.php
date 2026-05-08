<?php
ob_start();
header("Content-Type: application/json");

// Required files
require "../config/db.php";   // mysqli connection $conn
require "../config/csrf.php"; // csrf session
require_once '../init.php';

// Check CSRF
if(!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf']){
    exit(json_encode(["error"=>"CSRF error"]));
}

// Get data
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if(empty($username) || empty($password)){
    exit(json_encode(["error"=>"Please enter username and password"]));
}

// Get user from database
$stmt = $conn->prepare("SELECT id, password, display_name FROM users WHERE username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Check if user exists
if(!$user){
    exit(json_encode(["error"=>"Username not found"]));
}

// Verify password
if(!password_verify($password, $user['password'])){
    exit(json_encode(["error"=>"Incorrect password"]));
}

// Create secure session
session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['display_name'] = $user['display_name'];
$_SESSION['username'] = $username;

// Success
echo json_encode(["status"=>"ok"]);

ob_end_flush();