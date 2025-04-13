<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');
error_reporting(E_ALL);
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

// Validasi
if (!$data || !is_array($data)) {
    echo json_encode(['status' => 'error', 'message' => 'Data tidak valid']);
    exit;
}

require_once '../database/db.php';

$total = 0;
foreach ($data as $item) {
    $total += $item['price'] * $item['quantity'];
}


$stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, created_at) VALUES (?, ?, ?, NOW())");
$userId = $_SESSION['user_id'] ?? 1;
$status = 'pending';
$stmt->bind_param("ids", $userId, $total, $status);
$stmt->execute();
$orderId = $stmt->insert_id;

// Simpan tiap item ke tabel order_items
$itemStmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, subtotal, price) VALUES (?, ?, ?, ?, ?)");
foreach ($data as $item) {
    $subtotal = $item['price'] * $item['quantity'];
    $itemStmt->bind_param("iisdi", $orderId, $item['id'], $item['quantity'], $subtotal, $item['price']);
    $itemStmt->execute();
}

// Simpan waktu mulai session pembayaran
$_SESSION['start_time'] = time();

echo json_encode(['status' => 'success', 'order_id' => $orderId]);
