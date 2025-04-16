<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Koneksi ke database
include '../database/db.php';

// Ambil ID produk dari URL
$carousel_id = $_GET['id'];

// Hapus produk dari database
$sql = "DELETE FROM carousel WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $carousel_id);

if ($stmt->execute()) {
  echo "<script>
  sessionStorage.setItem('toastMessage', 'Carousel berhasil dihapuskan!');
  window.location.href = '../admin/carousel.php';
</script>";
    exit();
} else {
  echo "<script>
  sessionStorage.setItem('toastMessageDelete', 'Error: " . $stmt->error . "');
  window.location.href = '../admin/carousel.php';
</script>";
}

$stmt->close();
$conn->close();
?>