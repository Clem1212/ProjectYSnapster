<?php
session_start();

if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$username = $_SESSION['username'];
$today = date('Y-m-d');
$bereal = isset($_POST['bereal']) && $_POST['bereal'] === '1';

if ($bereal) {
    $streaksFile = __DIR__ . '/streaks.json';
    $streaks = file_exists($streaksFile) ? json_decode(file_get_contents($streaksFile), true) : [];

    if (!isset($streaks[$username])) {
        $streaks[$username] = ['last' => $today, 'count' => 1];
    } else {
        $last = $streaks[$username]['last'];
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        if ($last === $yesterday) {
            $streaks[$username]['count']++;
        } else if ($last !== $today) {
            $streaks[$username]['count'] = 1;
        }
        $streaks[$username]['last'] = $today;
    }
    file_put_contents($streaksFile, json_encode($streaks, JSON_PRETTY_PRINT));
    echo json_encode(['success' => true, 'streak' => $streaks[$username]['count']]);
} else {
    echo json_encode(['success' => false, 'message' => 'Not a BeReal post']);
}
?>
