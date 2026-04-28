<?php
// Handle both POST (form submission) and GET (redirect after submission)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['success'])) {
    // Redirect back to index with success message
    header('Location: index.html#contact?sent=1');
    exit;
}

header('Content-Type: application/json');

// Configuration
$messagesFile = 'messages.json';

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit;
}

// Get input
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

// Simple validation
if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    echo json_encode([
        'success' => false,
        'message' => 'All fields are required.'
    ]);
    exit;
}

// Create message data
$messageData = [
    'id' => uniqid('msg_'),
    'name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
    'email' => filter_var($email, FILTER_SANITIZE_EMAIL),
    'subject' => htmlspecialchars($subject, ENT_QUOTES, 'UTF-8'),
    'message' => htmlspecialchars($message, ENT_QUOTES, 'UTF-8'),
    'created_at' => date('Y-m-d H:i:s')
];

// Load existing messages
$messages = [];
if (file_exists($messagesFile)) {
    $content = file_get_contents($messagesFile);
    if ($content && $content !== '[]') {
        $messages = json_decode($content, true);
        if (!is_array($messages)) {
            $messages = [];
        }
    }
}

// Add new message
$messages[] = $messageData;

// Save to file
$result = file_put_contents($messagesFile, json_encode($messages, JSON_PRETTY_PRINT));

if ($result !== false) {
    // Redirect back to the page with success indicator
    header('Location: index.html?sent=1');
    exit;
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save message. Please try again.'
    ]);
}