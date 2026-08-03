<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'count' => 0]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get cart count - count number of unique items, not quantities
$stmt = $conn->prepare('SELECT COUNT(*) as count FROM cart WHERE user_id = ?');
$stmt->execute([$user_id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    'status' => 'success',
    'count' => (int)($result['count'] ?? 0)
]);
