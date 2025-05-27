<?php
// update_note.php - Backend to update an existing note
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
    sendResponse(false, null, 'You must be logged in to update notes');
}

// Validate POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, null, 'Invalid request method');
}

// Get form data
$noteId = $_POST['noteId'] ?? '';
$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$isPrivate = isset($_POST['private']) && $_POST['private'] === '1';

// Validate required fields
if (empty($noteId)) {
    sendResponse(false, null, 'Note ID is required');
}

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

if (!file_exists($notesFile)) {
    sendResponse(false, null, 'Notes file not found');
}

try {
    // Read existing notes
    $notesData = file_get_contents($notesFile);
    $notes = json_decode($notesData, true) ?: [];
    
    // Find the note to update
    $noteIndex = -1;
    $noteFound = false;
    
    for ($i = 0; $i < count($notes); $i++) {
        if ($notes[$i]['id'] === $noteId && $notes[$i]['username'] === $_SESSION['username']) {
            $noteIndex = $i;
            $noteFound = true;
            break;
        }
    }
    
    if (!$noteFound) {
        sendResponse(false, null, 'Note not found or you do not have permission to edit this note');
    }
    
    // Update the note data
    $notes[$noteIndex]['title'] = $title;
    $notes[$noteIndex]['content'] = $content;
    $notes[$noteIndex]['private'] = $isPrivate;
    $notes[$noteIndex]['updated_at'] = date('Y-m-d H:i:s');
    
    // Write updated notes back to file
    $updatedNotesData = json_encode($notes, JSON_PRETTY_PRINT);
    
    if (file_put_contents($notesFile, $updatedNotesData) === false) {
        sendResponse(false, null, 'Failed to save updated note');
    }
    
    // Return the updated note
    $updatedNote = $notes[$noteIndex];
    sendResponse(true, $updatedNote, null);
    
} catch (Exception $e) {
    error_log("Error updating note: " . $e->getMessage());
    sendResponse(false, null, 'An error occurred while updating the note');
}
?>