<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login to submit feedback']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $user_id = $_SESSION['user_id'];
    $message = trim($_POST['message']);
    
    if (empty($message)) {
        echo json_encode(['status' => 'error', 'message' => 'Please enter your message']);
        exit;
    }

    try {
        $stmt = $conn->prepare('INSERT INTO feedback (user_id, message, status) VALUES (?, ?, ?)');
        if ($stmt->execute([$user_id, $message, 'inactive'])) {
            echo json_encode(['status' => 'success', 'message' => 'Thank you! Your feedback has been submitted.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to submit feedback']);
        }
    } catch (PDOException $e) {
        error_log("Database error in feedback submission: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'An error occurred. Please try again later.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>
