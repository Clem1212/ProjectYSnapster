<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(["success" => false, "error" => "Not logged in"]);
    exit;
}

if (!isset($_POST['postId'])) {
    echo json_encode(["success" => false, "error" => "Missing post ID"]);
    exit;
}

$postId = intval($_POST['postId']);
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

// Find the post to delete
$postFound = false;
$postToDelete = null;

foreach ($posts as $index => $post) {
    if ($post['id'] === $postId) {
        // Check if user owns this post
        if ($post['userId'] !== $currentUserId) {
            echo json_encode(["success" => false, "error" => "Unauthorized - you can only delete your own posts"]);
            exit;
        }
        
        $postToDelete = $post;
        $postFound = true;
        
        // Remove the post from array
        array_splice($posts, $index, 1);
        break;
    }
}

if (!$postFound) {
    echo json_encode(["success" => false, "error" => "Post not found"]);
    exit;
}

// Delete the image file if it exists
if (isset($postToDelete['image']) && file_exists($postToDelete['image'])) {
    unlink($postToDelete['image']);
}

// Save updated posts
if (file_put_contents($postsFile, json_encode($posts, JSON_PRETTY_PRINT))) {
    echo json_encode([
        "success" => true, 
        "message" => "Post deleted successfully"
    ]);
} else {
    echo json_encode(["success" => false, "error" => "Failed to save changes"]);
}
?>