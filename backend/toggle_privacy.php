<?php

session_start();

header('Content-Type: application/json');



if (!isset($_SESSION['user'])) {

  echo json_encode(["error" => "Not logged in"]);

  exit;

}



$postId = intval($_POST['postId']);

$postsFile = __DIR__ . '/posts.json';

$posts = json_decode(file_get_contents($postsFile), true);



foreach ($posts as &$post) {

  if ($post['id'] === $postId && $post['userId'] === $_SESSION['user']['id']) {

    $post['private'] = !$post['private'];

    file_put_contents($postsFile, json_encode($posts, JSON_PRETTY_PRINT));

    echo json_encode(["success" => true, "private" => $post['private']]);

    exit;

  }

}

echo json_encode(["error" => "Unauthorized or post not found"]);

?>
