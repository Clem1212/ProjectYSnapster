<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
  echo json_encode(["error" => "Not logged in"]);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $postId = intval($_POST['postId']);
  $userId = $_SESSION['user']['id'];
  
  $posts = json_decode(file_get_contents('posts.json'), true);
  
  foreach ($posts as &$post) {
    if ($post['id'] === $postId) {
      if (!in_array($userId, $post['likes'])) {
        $post['likes'][] = $userId;
      } else {
        // toggle like off
        $post['likes'] = array_filter($post['likes'], fn($id) => $id !== $userId);
      }
      file_put_contents('posts.json', json_encode($posts, JSON_PRETTY_PRINT));
      echo json_encode(["success" => "Like toggled", "likesCount" => count($post['likes'])]);
      exit;
    }
  }
  
  echo json_encode(["error" => "Post not found"]);
  exit;
}
?>
