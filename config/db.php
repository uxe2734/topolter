<?php
$conn = new mysqli("DB_HOST","DB_USERNAME","DB_PASSWORD","DB_NAME");
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    http_response_code(500);
    exit(json_encode(["error"=>"DB error"]));
}

date_default_timezone_set('Asia/Tehran');
$conn->query("SET time_zone = '+03:30'");