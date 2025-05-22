<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(["success" => false, "error" => "Not logged in"]);
    exit;
}

if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["success" => false, "error" => "No file uploaded or upload error"]);
    exit;
}

$file = $_FILES['avatar'];
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
$maxSize = 5 * 1024 * 1024; // 5MB

// Validate file type
if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(["success" => false, "error" => "Invalid file type. Only JPEG, PNG, and GIF are allowed."]);
    exit;
}

// Validate file size
if ($file['size'] > $maxSize) {
    echo json_encode(["success" => false, "error" => "File too large. Maximum size is 5MB."]);
    exit;
}

// Create uploads directory if it doesn't exist
$uploadDir = __DIR__ . '/uploads/avatars/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'avatar_' . $_SESSION['user']['id'] . '_' . time() . '.' . $extension;
$filepath = $uploadDir . $filename;

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $filepath)) {
    // Update user's avatar in users file
    $usersFile = __DIR__ . '/users.json';
    $users = [];
    
    if (file_exists($usersFile)) {
        $users = json_decode(file_get_contents($usersFile), true);
        if (!is_array($users)) {
            $users = [];
        }
    }
    
    // Find and update user
    foreach ($users as &$user) {
        if ($user['id'] === $_SESSION['user']['id']) {
            // Delete old avatar if it exists
            if (isset($user['avatar']) && file_exists(__DIR__ . '/' . $user['avatar'])) {
                unlink(__DIR__ . '/' . $user['avatar']);
            }
            
            $user['avatar'] = 'uploads/avatars/' . $filename;
            $_SESSION['user']['avatar'] = $user['avatar'];
            break;
        }
    }
    
    // Save updated users
    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
    
    echo json_encode([
        "success" => true, 
        "avatar" => 'uploads/avatars/' . $filename,
        "message" => "Avatar updated successfully"
    ]);
} else {
    echo json_encode(["success" => false, "error" => "Failed to upload file"]);
}
?>