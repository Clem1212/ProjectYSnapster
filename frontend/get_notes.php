<?php
// get_notes.php - Backend to fetch user notes
session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

function sendResponse($success, $data = null, $error = null) {
    echo json_encode([
        'success' => $success,
        'notes' => $data,
        'error' => $error
    ]);
    exit;
}

// Get username from query parameter
$username = $_GET['username'] ?? null;

if (!$username) {
    sendResponse(false, null, 'Username is required');
}

// Path to notes file
$notesFile = "data/notes.json";

// Create data directory if it doesn't exist
if (!file_exists('data')) {
    mkdir('data', 0777, true);
}

// Create notes file if it doesn't exist
if (!file_exists($notesFile)) {
    file_put_contents($notesFile, json_encode([]));
}

try {
    // Read notes file
    $notesData = file_get_contents($notesFile);
    $allNotes = json_decode($notesData, true) ?: [];
    
    // Filter notes for the specific user
    $userNotes = array_filter($allNotes, function($note) use ($username) {
        return $note['username'] === $username;
    });
    
    // Sort by creation date (newest first)
    usort($userNotes, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
    
    // Reset array keys
    $userNotes = array_values($userNotes);
    
    sendResponse(true, $userNotes);
    
} catch (Exception $e) {
    sendResponse(false, null, 'Error reading notes: ' . $e->getMessage());
}
?>