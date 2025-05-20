<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = trim($_POST['password'] ?? '');
  
  if (!$username || !$password) {
    echo json_encode(["success" => false, "error" => "Username and password required."]);
    exit;
  }
  
  $usersFile = __DIR__ . '/users.json';
  if (!file_exists($usersFile)) {
    file_put_contents($usersFile, json_encode([]));
  }
  
  $users = json_decode(file_get_contents($usersFile), true);
  if (!is_array($users)) {
    $users = [];
  }
  
  foreach ($users as $user) {
    if ($user['username'] === $username) {
      echo json_encode(["success" => false, "error" => "Username already exists"]);
      exit;
    }
  }
  
  $hashed = password_hash($password, PASSWORD_DEFAULT);
  $newUser = [
    "id" => count($users) + 1,
    "username" => $username,
    "password" => $hashed
  ];
  
  $users[] = $newUser;
  
  if (file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT)) === false) {
    echo json_encode(["success" => false, "error" => "Failed to save user data."]);
    exit;
  }
  
  echo json_encode(["success" => true, "message" => "User created"]);
  exit;
}
?>
