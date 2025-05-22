<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

// Check if avatar file is provided
if (!isset($_FILES['avatar'])) {
    echo json_encode(['success' => false, 'error' => 'No avatar file provided']);
    exit;
}

$username = $_SESSION['username'];

try {
    // Handle file upload
    $uploadDir = __DIR__ . '/avatars/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $file = $_FILES['avatar'];
    $fileName = 'avatar_' . $username . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
    $filePath = $uploadDir . $fileName;
    $relativePath = 'avatars/' . $fileName;
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'error' => 'Invalid file type']);
        exit;
    }
    
    // Validate file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'error' => 'File too large']);
        exit;
    }
    
    // Delete old avatar if exists
    $avatarsDir = __DIR__ . '/avatars/';
    if (is_dir($avatarsDir)) {
        $files = glob($avatarsDir . 'avatar_' . $username . '_*');
        foreach ($files as $oldFile) {
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        echo json_encode([
            'success' => true,
            'message' => 'Avatar updated successfully',
            'avatar' => $relativePath
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to upload avatar']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
?>