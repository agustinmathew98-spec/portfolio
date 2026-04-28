<?php
// Simple admin page to view messages
$messagesFile = 'messages.json';

$messages = [];
if (file_exists($messagesFile)) {
    $content = file_get_contents($messagesFile);
    if ($content) {
        $messages = json_decode($content, true) ?? [];
    }
}

// Sort by newest first
usort($messages, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Messages | Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .message-card { transition: .3s; }
        .message-card:hover { transform: translateY(-2px); }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.html">
                <i class="bi bi-arrow-left"></i> Back to Portfolio
            </a>
            <span class="navbar-text">Admin Panel</span>
        </div>
    </nav>

    <div class="container py-5">
        <h1 class="mb-4">Contact Messages</h1>
        
        <?php if (empty($messages)): ?>
            <div class="alert alert-info">No messages yet.</div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($messages as $msg): ?>
                    <div class="col-md-6">
                        <div class="card message-card p-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h5 class="mb-0"><?= htmlspecialchars($msg['name']) ?></h5>
                                    <a href="mailto:<?= htmlspecialchars($msg['email']) ?>" class="text-muted small">
                                        <?= htmlspecialchars($msg['email']) ?>
                                    </a>
                                </div>
                                <small class="text-muted"><?= date('M j, Y g:i A', strtotime($msg['created_at'])) ?></small>
                            </div>
                            <h6 class="text-primary"><?= htmlspecialchars($msg['subject']) ?></h6>
                            <p class="mb-0"><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>