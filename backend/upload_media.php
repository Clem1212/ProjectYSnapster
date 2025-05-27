<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$caption = $_POST['caption'] ?? '';
$type = $_POST['type'] ?? 'photo';
$upload_dir = '../uploads/';

// Create upload directory if it doesn't exist
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$file_path = '';

if ($type === 'photo' && isset($_FILES['photo'])) {
    $file_extension = 'png';
    $filename = $user_id . '_' . time() . '.' . $file_extension;
    $file_path = $upload_dir . $filename;
    
    if (move_uploaded_file($_FILES['photo']['tmp_name'], $file_path)) {
        // File uploaded successfully
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to save photo']);
        exit;
    }
} elseif ($type === 'video' && isset($_FILES['video'])) {
    $file_extension = 'webm';
    $filename = $user_id . '_' . time() . '.' . $file_extension;
    $file_path = $upload_dir . $filename;
    
    if (move_uploaded_file($_FILES['video']['tmp_name'], $file_path)) {
        // File uploaded successfully
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to save video']);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No media file provided']);
    exit;
}

// Save to database
try {
    $pdo = new PDO("mysql:host=localhost;dbname=your_db", "username", "password");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("INSERT INTO posts (user_id, caption, media_path, media_type, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->execute([$user_id, $caption, $file_path, $type]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>