<?php
session_start();
require_once '../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0]);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $conn->prepare('SELECT COUNT(*) FROM wishlist WHERE user_id = ?');
    $stmt->execute([$user_id]);
    $count = $stmt->fetchColumn();
    echo json_encode(['count' => $count]);
} catch (PDOException $e) {
    echo json_encode(['count' => 0]);
}
