<?php
require_once '../init.php';
require "../config/db.php";

$sender = $_SESSION['user_id'];
$receiver = (int)$_POST['receiver_id'];

if(!isset($_FILES['file'])){
    exit(json_encode(["error"=>"No file selected"]));
}

$file = $_FILES['file'];

// Get extension
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

// MIME
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$fileType = finfo_file($finfo, $file['tmp_name']);
if(!$fileType){
    $fileType = "application/octet-stream";
}
finfo_close($finfo);

// Allowed list
$allowed_ext = ['jpg','jpeg','png','mp4','pdf','npvt','nm','apk','wav','mp3','bat','exe','zip','rar'];
$allowed_mime = ['image/jpeg','image/jpg','image/png','video/mp4','audio/mp3','audio/mpeg','audio/wav','application/pdf','application/octet-stream'];

$media_mimes = [
    'image/jpeg',
    'image/png',
    'image/jpg',
    'video/mp4',
    'audio/mpeg',
    'audio/wav',
    'application/pdf'
];

// Security check
if(in_array($fileType, $media_mimes)){
    if(!in_array($fileType, $allowed_mime)){
        exit(json_encode(["error"=>"File type is not allowed"]));
    }
}



// Create folder
$uploadDir = "../uploads/";
if(!is_dir($uploadDir)){
    mkdir($uploadDir, 0777, true);
}

// Unique name
$fileName = time() . "_" . basename($file['name']);
$target = $uploadDir . $fileName;

// Upload
move_uploaded_file($file['tmp_name'], $target);

// Save in database
$stmt = $conn->prepare("
INSERT INTO messages (sender_id, receiver_id, message, file_path, file_type, is_read)
VALUES (?, ?, '', ?, ?, 0)
");

$stmt->bind_param("iiss", $sender, $receiver, $fileName, $ext);
$stmt->execute();

echo json_encode(["status"=>"ok"]);