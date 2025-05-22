<?php
// upload.php - JSON Database Version

session_start();

if (!isset($_SESSION['user'])) {
    echo json_encode(["success" => false, "error" => "Not logged in"]);
    exit;
}

// JSON database file
$postsFile = __DIR__ . '/data/posts.json';

// Helper function to read posts from JSON file
function readPosts($file) {
    if (!file_exists($file)) {
        // Create directory if it doesn't exist
        $dir = dirname($file);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        // Create empty posts file
        file_put_contents($file, json_encode([]));
        return [];
    }
    
    $content = file_get_contents($file);
    $posts = json_decode($content, true);
    return $posts ?: [];
}

// Helper function to write posts to JSON file
function writePosts($file, $posts) {
    return file_put_contents($file, json_encode($posts, JSON_PRETTY_PRINT));
}

// Helper function to generate unique ID
function generateId() {
    return uniqid() . '_' . time();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle file upload
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(["success" => false, "error" => "No image uploaded or upload error"]);
        exit;
    }

    $uploadDir = __DIR__ . '/uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $imageFile = $_FILES['image'];
    $fileName = uniqid() . '_' . basename($imageFile['name']);
    $targetPath = $uploadDir . $fileName;

    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($imageFile['type'], $allowedTypes)) {
        echo json_encode(["success" => false, "error" => "Invalid file type"]);
        exit;
    }

    // Move uploaded file
    if (move_uploaded_file($imageFile['tmp_name'], $targetPath)) {
        try {
            // Get post data
            $caption = $_POST['caption'] ?? '';
            $isPrivate = isset($_POST['isPrivate']) ? (int)$_POST['isPrivate'] : 0;
            
            // Alternative way to check for private checkbox
            if (isset($_POST['privatePost'])) {
                $isPrivate = 1;
            }

            // Read existing posts
            $posts = readPosts($postsFile);
            
            // Create new post
            $postId = generateId();
            $newPost = [
                'id' => $postId,
                'userId' => $_SESSION['user']['id'],
                'username' => $_SESSION['user']['username'],
                'image' => 'uploads/' . $fileName,
                'caption' => $caption,
                'content' => '', // For consistency with text posts
                'isPrivate' => (bool)$isPrivate,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // Add new post to beginning of array (newest first)
            array_unshift($posts, $newPost);
            
            // Write back to file
            if (writePosts($postsFile, $posts)) {
                echo json_encode([
                    "success" => true,
                    "message" => "Post uploaded successfully",
                    "postId" => $postId,
                    "image" => 'uploads/' . $fileName,
                    "isPrivate" => (bool)$isPrivate
                ]);
            } else {
                // Clean up uploaded file if JSON write fails
                unlink($targetPath);
                echo json_encode(["success" => false, "error" => "Failed to save post data"]);
            }

        } catch(Exception $e) {
            // Clean up uploaded file if there's an error
            unlink($targetPath);
            echo json_encode(["success" => false, "error" => "Failed to save post: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "Failed to upload image"]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Invalid request method"]);
}
?>