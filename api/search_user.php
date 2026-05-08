<?php
require_once '../init.php';
header("Content-Type: application/json");
require "../config/db.php";

$q = "%".trim($_GET['q'])."%";

$stmt = $conn->prepare("SELECT id,display_name FROM users WHERE username LIKE ? LIMIT 10");
$stmt->bind_param("s",$q);
$stmt->execute();
$result = $stmt->get_result();

$users=[];
while($row=$result->fetch_assoc()){
    $users[]=$row;
}

echo json_encode($users);
