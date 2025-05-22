<?php

session_start();

require 'db.php';



if (!isset($_SESSION['username'])) {

  http_response_code(401);

  exit('Not logged in');

}



$username = $_SESSION['username'];

$post_id = $_POST['post_id'] ?? null;



if (!$post_id) {

  http_response_code(400);

  exit('Missing post ID');

}



// Verify post belongs to user

$stmt = $pdo->prepare('SELECT * FROM posts WHERE id = ? AND username = ?');

$stmt->execute([$post_id, $username]);

$post = $stmt->fetch(PDO::FETCH_ASSOC);



if (!$post) {

  http_response_code(403);

  exit('Unauthorized');

}



// Delete post

$stmt = $pdo->prepare('DELETE FROM posts WHERE id = ?');

$stmt->execute([$post_id]);



echo json_encode(['success' => true]);
