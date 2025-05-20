<?php
session_start();
header('Content-Type: application/json');

$username = $_SESSION['username'] ?? '';
$streaks = file_exists('streaks.json') ? json_decode(file_get_contents('streaks.json'), true) : [];

echo json_encode(['streak' => $streaks[$username]['count'] ?? 0]);
?>
