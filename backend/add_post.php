<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
  echo json_encode(["error" => "Not logged in"]);
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $caption = trim($_POST['caption']);
  
  if (!isset($_FILES['image'])) {
    echo json_encode(["error" => "No image uploaded"]);
    exit;
  }
  
  $image = $_FILES['image'];
  $ext = pathinfo($image['name'], PATHINFO_EXTENSION);
  $allowed = ['jpg', 'jpeg', 'png', 'gif'];
  
  if (!in_array(strtolower($ext), $allowed)) {
    echo json_encode(["error" => "Invalid image type"]);
    exit;
  }
  
  $targetDir = 'uploads/';
  if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
  }
  
  $filename = uniqid() . '.' . $ext;
  $targetFile = $targetDir . $filename;
  
  if (!move_uploaded_file($image['tmp_name'], $targetFile)) {
    echo json_encode(["error" => "Failed to move uploaded file"]);
    exit;
  }
  
  $posts = json_decode(file_get_contents('posts.json'), true);
  
  $newPost = [
    "id" => count($posts) + 1,
    "userId" => $_SESSION['user']['id'],
    "username" => $_SESSION['user']['username'],
    "image" => $targetFile,
    "caption" => $caption,
    "likes" => [],
    "comments" => [],
    "timestamp" => date('c')
  ];
  
  $posts[] = $newPost;
  file_put_contents('posts.json', json_encode($posts, JSON_PRETTY_PRINT));
  
  echo json_encode(["success" => "Post added"]);
  exit;
}
?>
