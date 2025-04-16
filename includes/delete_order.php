<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Koneksi ke database
include '../database/db.php';

// Ambil ID produk dari URL
$order_id = $_GET['id'];

// Hapus produk dari database
$sql = "DELETE FROM orders WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);

if ($stmt->execute()) {
  echo "<script>
  sessionStorage.setItem('toastMessage', 'Order berhasil dihapuskan!');
  window.location.href = '../admin/order.php';
</script>";
    exit();
} else {
  echo "<script>
  sessionStorage.setItem('toastMessageDelete', 'Error: " . $stmt->error . "');
  window.location.href = '../admin/order.php';
</script>";
}

$stmt->close();
$conn->close();
?>