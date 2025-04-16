<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Koneksi ke database
include '../database/db.php';

// Ambil ID produk dari URL
$payment_method_id = $_GET['id'];

// Hapus produk dari database
$sql = "DELETE FROM payment_methods WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $payment_method_id);

if ($stmt->execute()) {
  echo "<script>
  sessionStorage.setItem('toastMessage', 'Payment Method berhasil dihapuskan!');
  window.location.href = '../admin/payment_method.php';
</script>";
    exit();
} else {
  echo "<script>
  sessionStorage.setItem('toastMessageDelete', 'Error: " . $stmt->error . "');
  window.location.href = '../admin/payment_method.php';
</script>";
}

$stmt->close();
$conn->close();
?>