<?php
header('Content-Type: application/json');

$postsFile = 'posts.json';
if (!file_exists($postsFile)) {
  echo json_encode([]);
  exit;
}

$posts = json_decode(file_get_contents($postsFile), true);

// Sort posts by timestamp descending (newest first)
usort($posts, function($a, $b) {
  return $b['timestamp'] - $a['timestamp'];
});

echo json_encode($posts);
?>
