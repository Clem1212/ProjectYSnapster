<?php

session_start();

header('Content-Type: application/json');



$postsFile = __DIR__ . '/posts.json';

if (!file_exists($postsFile)) {

  echo json_encode([]);

  exit;

}



$posts = json_decode(file_get_contents($postsFile), true);

if (!is_array($posts)) {

  $posts = [];

}



echo json_encode($posts);
