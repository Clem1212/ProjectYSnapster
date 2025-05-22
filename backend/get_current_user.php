<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['user'])) {
    echo json_encode([
        "success" => true,
        "user" => [
            "id" => $_SESSION['user']['id'],
            "username" => $_SESSION['user']['username'],
            "email" => $_SESSION['user']['email'] ?? '',
            "avatar" => $_SESSION['user']['avatar'] ?? null
        ]
    ]);
} else {
    echo json_encode([
        "success" => false,
        "error" => "Not logged in"
    ]);
}
?>