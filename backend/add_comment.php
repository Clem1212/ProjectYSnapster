<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
  echo json_encode(["error" => "Not logged in"]);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $postId = intval($_POST['postId']);
  $commentText = trim($_POST['comment']);
  if (!$commentText) {
    echo json_encode(["error" => "Comment cannot be empty"]);
    exit;
  }
  
  $posts = json_decode(file_get_contents('posts.json'), true);
  
  foreach ($posts as &$post) {
    if ($post['id'] === $postId) {
      $post['comments'][] = [
        "userId" => $_SESSION['user']['id'],
        "username" => $_SESSION['user']['username'],
        "comment" => $commentText
      ];
      file_put_contents('posts.json', json_encode($posts, JSON_PRETTY_PRINT));
      echo json_encode(["success" => "Comment added"]);
      exit;
    }
  }
  
  echo json_encode(["error" => "Post not found"]);
  exit;
}
?>
