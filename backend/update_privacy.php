<?php
// Updated update_privacy.php
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
foreach ($posts as &$post) {
    if ($post['id'] == $postId) { // Use == for type flexibility
        // Check ownership by both userId and username
        $isOwner = (isset($post['userId']) && $post['userId'] == $currentUserId) || 
                   (isset($post['username']) && $post['username'] === $currentUsername);
        
        if (!$isOwner) {
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