<?php
require_once '../init.php';

// Clear all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Delete session cookie (for complete assurance)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

echo json_encode(["status"=>"ok"]);