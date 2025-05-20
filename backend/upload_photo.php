<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user']['username'])) {
  echo json_encode(["error" => "Not logged in"]);
  exit;
}

$uploadsDir = __DIR__ . '/uploads';
if (!is_dir($uploadsDir)) {
  mkdir($uploadsDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
  $caption = $_POST['caption'] ?? '';
  $retakes = $_POST['retakes'] ?? 0;

  $filename = uniqid('photo_', true) . '.png';
  $targetPath = $uploadsDir . '/' . $filename;
  
  if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
    $postsFile = __DIR__ . '/posts.json';
    $posts = file_exists($postsFile) ? json_decode(file_get_contents($postsFile), true) : [];

    if (!is_array($posts)) {
      $posts = [];
    }

    $newPost = [
      'id' => count($posts) + 1, // for now keep simple, can improve later
      'username' => $_SESSION['user']['username'],
      'image' => 'uploads/' . $filename,
      'caption' => htmlspecialchars($caption, ENT_QUOTES | ENT_HTML5),
      'retakes' => (int)$retakes,
      'likes' => [],
      'comments' => []
    ];

    $posts[] = $newPost;

    if (file_put_contents($postsFile, json_encode($posts, JSON_PRETTY_PRINT)) === false) {
      echo json_encode(["error" => "Failed to save post"]);
      exit;
    }

    echo json_encode(["success" => true]);
  } else {
    echo json_encode(["error" => "Failed to save photo"]);
  }
} else {
  echo json_encode(["error" => "Invalid request"]);
}

