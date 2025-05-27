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

$caption = $_POST['caption'] ?? '';
$retakes = $_POST['retakes'] ?? 0;
$username = $_SESSION['user']['username'];
$postsFile = __DIR__ . '/posts.json';
$posts = file_exists($postsFile) ? json_decode(file_get_contents($postsFile), true) : [];

if (!is_array($posts)) {
  $posts = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Handle photo upload
  if (isset($_FILES['photo'])) {
    $filename = uniqid('photo_', true) . '.png';
    $targetPath = $uploadsDir . '/' . $filename;

    if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath)) {
      $posts[] = [
        'id' => count($posts) + 1,
        'username' => $username,
        'image' => 'uploads/' . $filename,
        'caption' => htmlspecialchars($caption, ENT_QUOTES | ENT_HTML5),
        'retakes' => (int)$retakes,
        'likes' => [],
        'comments' => []
      ];

      if (file_put_contents($postsFile, json_encode($posts, JSON_PRETTY_PRINT)) === false) {
        echo json_encode(["error" => "Failed to save post"]);
        exit;
      }

      echo json_encode(["success" => true]);
    } else {
      echo json_encode(["error" => "Failed to save photo"]);
    }

  // Handle video upload
  } elseif (isset($_FILES['video'])) {
    $filename = uniqid('video_', true) . '.webm';
    $targetPath = $uploadsDir . '/' . $filename;

    if (move_uploaded_file($_FILES['video']['tmp_name'], $targetPath)) {
      $posts[] = [
        'id' => count($posts) + 1,
        'username' => $username,
        'video' => 'uploads/' . $filename,
        'caption' => htmlspecialchars($caption, ENT_QUOTES | ENT_HTML5),
        'likes' => [],
        'comments' => []
      ];

      if (file_put_contents($postsFile, json_encode($posts, JSON_PRETTY_PRINT)) === false) {
        echo json_encode(["error" => "Failed to save post"]);
        exit;
      }

      echo json_encode(["success" => true]);
    } else {
      echo json_encode(["error" => "Failed to save video"]);
    }

  } else {
    echo json_encode(["error" => "No file uploaded"]);
  }
} else {
  echo json_encode(["error" => "Invalid request"]);
}
