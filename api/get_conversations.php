<?php
require_once '../init.php';
header("Content-Type: application/json");
require "../config/db.php";

$user = $_SESSION['user_id'];

$sql = "
SELECT DISTINCT 
    CASE 
        WHEN sender_id = ? THEN receiver_id 
        ELSE sender_id 
    END AS user_id
FROM messages
WHERE sender_id = ? OR receiver_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii",$user,$user,$user);
$stmt->execute();
$result = $stmt->get_result();

$ids=[];
while($row=$result->fetch_assoc()){
    $ids[] = $row['user_id'];
}

if(empty($ids)){
    echo json_encode([]);
    exit;
}

$id_list = implode(",", array_map('intval',$ids));
$result2 = $conn->query("SELECT id,display_name FROM users WHERE id IN ($id_list)");

$users=[];
while($row=$result2->fetch_assoc()){
    $users[]=$row;
}

echo json_encode($users);