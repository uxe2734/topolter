<?php
require "../config/db.php";
require "../config/csrf.php";
header("Content-Type: application/json");

if($_POST['csrf'] !== $_SESSION['csrf']){
    exit(json_encode(["error"=>"CSRF error"]));
}

$username = trim($_POST['username']);
$display_name = trim($_POST['display_name']);
$password = $_POST['password'];

if(strlen($username)<3 || strlen($password)<4){
    exit(json_encode(["error"=>"Invalid information"]));
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (username,display_name,password) VALUES (?,?,?)");
$stmt->bind_param("sss",$username,$display_name,$hash);

if($stmt->execute()){
    echo json_encode(["status"=>"ok"]);
}else{
    echo json_encode(["error"=>"Username already exists"]);
}