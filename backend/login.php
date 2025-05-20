<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(["success" => false, "error" => "Invalid request"]);
  exit;
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if (!$username || !$password) {
  echo json_encode(["success" => false, "error" => "Username and password required."]);
  exit;
}

$usersFile = __DIR__ . '/users.json';
if (!file_exists($usersFile)) {
  echo json_encode(["success" => false, "error" => "No users found."]);
  exit;
}

$users = json_decode(file_get_contents($usersFile), true);
if (!is_array($users)) {
  echo json_encode(["success" => false, "error" => "Users data corrupted."]);
  exit;
}

foreach ($users as $user) {
  if ($user['username'] === $username) {
    if (password_verify($password, $user['password'])) {
      // Set session user as array with id and username
      $_SESSION['user'] = [
        'id' => $user['id'],
        'username' => $user['username']
      ];
      echo json_encode(["success" => true]);
      exit;
    } else {
      echo json_encode(["success" => false, "error" => "Incorrect password."]);
      exit;
    }
  }
}

echo json_encode(["success" => false, "error" => "Username not found."]);
exit;
