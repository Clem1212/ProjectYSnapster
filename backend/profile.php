<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
  echo json_encode(["error" => "Not logged in"]);
  exit;
}

$postsFile = __DIR__ . '/posts.json';
$posts = file_exists($postsFile) ? json_decode(file_get_contents($postsFile), true) : [];

$userId = $_SESSION['user']['id'];
$userPosts = array_values(array_filter($posts, fn($post) => $post['userId'] == $userId));

echo json_encode($userPosts);
?>
