<?php
// Start session dan periksa apakah yang mengakses adalah admin
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Jika bukan admin, tendang ke halaman login
    header("Location: ../login.php");
    exit();
}

include '../database/db.php';

//  Ambil ID user dari URL
$user_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$user_id) {
    // Jika ID tidak valid, kembalikan dengan pesan error
    echo "<script>
      sessionStorage.setItem('toastMessageDelete', 'ID User tidak valid!');
      window.location.href = '../admin/user.php';
    </script>";
    exit();
}

//  Perintah SQL prepared statement
$sql = "DELETE FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $user_id); // 'i' berarti tipe datanya integer
    
    //  Jalankan query dan berikan feedback
    if ($stmt->execute()) {
        // Jika berhasil, kirim pesan sukses
        echo "<script>
          sessionStorage.setItem('toastMessage', 'User berhasil dihapus.');
          window.location.href = '../admin/user.php';
        </script>";
    } else {
        // Jika gagal, kirim pesan error
        echo "<script>
          sessionStorage.setItem('toastMessageDelete', 'Gagal menghapus user: " . $stmt->error . "');
          window.location.href = '../admin/user.php';
        </script>";
    }
    $stmt->close();
} else {
    echo "<script>
      sessionStorage.setItem('toastMessageDelete', 'Gagal menyiapkan perintah SQL.');
      window.location.href = '../admin/user.php';
    </script>";
}

$conn->close();
?>