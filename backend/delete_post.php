<?php
// Updated delete_post.php - add this check after reading posts
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
$currentUsername = $_SESSION['user']['username']; // Add this line

$postsFile = __DIR__ . '/posts.json';

if (!file_exists($postsFile)) {
    echo json_encode(["success" => false, "error" => "Posts file not found"]);
    exit;
}

$posts = json_decode(file_get_contents($postsFile), true);
if (!is_array($posts)) {
    $posts = [];
}

$postFound = false;
$postToDelete = null;

foreach ($posts as $index => $post) {
    if ($post['id'] == $postId) { // Use == for type flexibility
        // Check ownership by both userId and username
        $isOwner = (isset($post['userId']) && $post['userId'] == $currentUserId) || 
                   (isset($post['username']) && $post['username'] === $currentUsername);
        
        if (!$isOwner) {
            echo json_encode(["success" => false, "error" => "Unauthorized - you can only delete your own posts"]);
            exit;
        }
        
        $postToDelete = $post;
        $postFound = true;
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

if (file_put_contents($postsFile, json_encode($posts, JSON_PRETTY_PRINT))) {
    echo json_encode([
        "success" => true, 
        "message" => "Post deleted successfully"
    ]);
} else {
    echo json_encode(["success" => false, "error" => "Failed to save changes"]);
}
?>