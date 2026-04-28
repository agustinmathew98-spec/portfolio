<?php
header('Content-Type: application/json');

// Enable error reporting for debugging (disable in production)
error_reporting(0);
ini_set('display_errors', 0);

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

// Get and sanitize input
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validation
$errors = [];

if (empty($name)) {
    $errors[] = 'Name is required.';
}

if (empty($email)) {
    $errors[] = 'Email is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}

if (empty($subject)) {
    $errors[] = 'Subject is required.';
}

if (empty($message)) {
    $errors[] = 'Message is required.';
}

if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => implode(' ', $errors)
    ]);
    exit;
}

// Sanitize inputs
$name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$email = filter_var($email, FILTER_SANITIZE_EMAIL);
$subject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
$message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

// Create message data
$messageData = [
    'id' => uniqid('msg_'),
    'name' => $name,
    'email' => $email,
    'subject' => $subject,
    'message' => $message,
    'created_at' => date('Y-m-d H:i:s')
];

// Load existing messages or create new array
$messages = [];
if (file_exists($messagesFile)) {
    $content = file_get_contents($messagesFile);
    if ($content) {
        $messages = json_decode($content, true) ?? [];
    }
}

// Add new message
$messages[] = $messageData;

// Save to file
if (file_put_contents($messagesFile, json_encode($messages, JSON_PRETTY_PRINT))) {
    echo json_encode([
        'success' => true,
        'message' => 'Thank you! Your message has been sent successfully.'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save message. Please try again.'
    ]);
}