<?php
session_start();

include '../database/db.php';

if (!isset($_SESSION['id'])) {
  http_response_code(401);
  echo json_encode(["status" => "error", "message" => "Unauthorized"]);
  exit();
}

$userId = $_SESSION['id'];

$query = "UPDATE notifications SET is_read = 1 WHERE user_id = $userId";
if (mysqli_query($conn, $query)) {
  echo json_encode(["status" => "success"]);
} else {
  echo json_encode(["status" => "error", "message" => mysqli_error($conn)]);
}
?>
