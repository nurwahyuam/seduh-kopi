<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Koneksi ke database
include '../database/db.php';

// Ambil ID produk dari URL
$product_id = $_GET['id'];

// Hapus produk dari database
$sql = "DELETE FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);

if ($stmt->execute()) {
  echo "<script>
  sessionStorage.setItem('toastMessageDelete', 'Produk berhasil dihapuskan!');
  window.location.href = '../admin/product.php';
</script>";
    exit();
} else {
  echo "<script>
  sessionStorage.setItem('toastMessage', 'Error: " . $stmt->error . "');
  window.location.href = '../admin/product.php';
</script>";
}

$stmt->close();
$conn->close();
?>