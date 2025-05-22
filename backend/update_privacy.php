<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(["success" => false, "error" => "Not logged in"]);
    exit;
}

if (!isset($_POST['postId']) || !isset($_POST['isPrivate'])) {
    echo json_encode(["success" => false, "error" => "Missing required parameters"]);
    exit;
}

$postId = intval($_POST['postId']);
$isPrivate = intval($_POST['isPrivate']) === 1;
$currentUserId = $_SESSION['user']['id'];

$postsFile = __DIR__ . '/posts.json';

// Check if posts file exists
if (!file_exists($postsFile)) {
    echo json_encode(["success" => false, "error" => "Posts file not found"]);
    exit;
}

// Read posts
$posts = json_decode(file_get_contents($postsFile), true);
if (!is_array($posts)) {
    $posts = [];
}

// Find and update the post
$postFound = false;
foreach ($posts as &$post) {
    if ($post['id'] === $postId) {
        // Check if user owns this post
        if ($post['userId'] !== $currentUserId) {
            echo json_encode(["success" => false, "error" => "Unauthorized - you can only modify your own posts"]);
            exit;
        }
        
        $post['isPrivate'] = $isPrivate;
        $post['private'] = $isPrivate; // Keep both for compatibility
        $postFound = true;
        break;
    }
}

if (!$postFound) {
    echo json_encode(["success" => false, "error" => "Post not found"]);
    exit;
}

// Save updated posts
if (file_put_contents($postsFile, json_encode($posts, JSON_PRETTY_PRINT))) {
    echo json_encode([
        "success" => true, 
        "isPrivate" => $isPrivate,
        "message" => "Privacy settings updated successfully"
    ]);
} else {
    echo json_encode(["success" => false, "error" => "Failed to save changes"]);
}
?>