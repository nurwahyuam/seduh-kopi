<?php
session_start();
session_unset(); // Menghapus semua variabel sesi
session_destroy(); // Menghancurkan sesi
echo "<script>
  sessionStorage.setItem('toastMessageDelete', 'Produk berhasil dihapuskan!');
  window.location.href = '../admin/product.php';
</script>";
header("Location: ../index.php"); // Redirect ke halaman login
exit();
?>