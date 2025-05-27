<?php
// add_note.php - Backend to add a new note
session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

function sendResponse($success, $data = null, $error = null) {
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'error' => $error
    ]);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    sendResponse(false, null, 'You must be logged in to add notes');
}

// Validate POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, null, 'Invalid request method');
}

// Get form data
$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$isPrivate = isset($_POST['private']) && $_POST['private'] === '1';

// Validate required fields
if (empty($content)) {
    sendResponse(false, null, 'Note content is required');
}

// Validate content length
if (strlen($content) > 280) {
    sendResponse(false, null, 'Note content must be 280 characters or less');
}

// Validate title length
if (strlen($title) > 50) {
    sendResponse(false, null, 'Note title must be 50 characters or less');
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
    // Read existing notes
    $notesData = file_get_contents($notesFile);
    $notes = json_decode($notesData, true) ?: [];
    
    // Generate unique ID
    $noteId = uniqid('note_', true);
    
    // Create new note
    $newNote = [
        'id' => $noteId,
        'username' => $_SESSION['username'],
        'title' => $title,
        'content' => $content,
        'private' => $isPrivate,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Add note to array
    $notes[] = $newNote;
    
    // Save back to file
    if (file_put_contents($notesFile, json_encode($notes, JSON_PRETTY_PRINT))) {
        sendResponse(true, ['noteId' => $noteId], 'Note added successfully');
    } else {
        sendResponse(false, null, 'Failed to save note');
    }
    
} catch (Exception $e) {
    sendResponse(false, null, 'Error adding note: ' . $e->getMessage());
}
?>