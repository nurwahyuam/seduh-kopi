<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login.php");
  exit();
}

// Koneksi ke database
include '../database/db.php';

// Pastikan data id terkirim via POST
if (!isset($_POST['id']) || empty($_POST['id'])) {
  echo "User tidak ditemukan.";
  exit();
}

// Ambil data dari form
$id       = $_POST['id'];
$username = $_POST['username'];
$email    = $_POST['email'];
$password = $_POST['password'];
$role     = $_POST['role'];

// Proses upload gambar jika ada
$profile_photo = $_FILES['profile_photo']['name'];
$target_dir    = "images/";
$uploadOk      = 1;

// Jika file diupload, lakukan pengecekan
if (!empty($profile_photo)) {
  // Tentukan target file menggunakan nama file yang diupload
  $target_file   = $target_dir . basename($profile_photo);
  $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

  // Cek apakah file benar-benar gambar
  $check = getimagesize($_FILES["profile_photo"]["tmp_name"]);
  if ($check === false) {
    echo "<script>
      sessionStorage.setItem('toastMessage', 'File bukan gambar.');
      window.location.href = '../admin/edit_user.php?id=".$id."';
    </script>";
    exit();
  }

  // Cek ukuran file (maksimal 500 KB)
  if ($_FILES["profile_photo"]["size"] > 500000) {
    echo "<script>
      sessionStorage.setItem('toastMessage', 'Maaf, ukuran file terlalu besar.');
      window.location.href = '../admin/edit_user.php?id=".$id."';
    </script>";
    exit();
  }

  // Cek format file (hanya JPG, JPEG, PNG)
  if ($imageFileType != "jpg" && $imageFileType != "jpeg" && $imageFileType != "png") {
    echo "<script>
      sessionStorage.setItem('toastMessage', 'Maaf, hanya file JPG, JPEG, & PNG yang diperbolehkan.');
      window.location.href = '../admin/edit_user.php?id=".$id."';
    </script>";
    exit();
  }

  // Jika pengecekan berhasil, upload file
  if (!move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_file)) {
    echo "<script>
      sessionStorage.setItem('toastMessage', 'Maaf, terjadi kesalahan saat mengupload file.');
      window.location.href = '../admin/edit_user.php?id=".$id."';
    </script>";
    exit();
  }

  // Update user beserta profile photo baru
  $sql = "UPDATE users SET username = ?, email = ?, role = ?, profile_photo = ? WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ssssi", $username, $email, $role, $target_file, $id);
} else {
  // Update user tanpa mengubah profile photo
  $sql = "UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sssi", $username, $email, $role, $id);
}

// Eksekusi query update user
if ($stmt->execute()) {
  // Jika field password diisi, update password juga
  if (!empty($password)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql_password    = "UPDATE users SET password = ? WHERE id = ?";
    $stmt_password   = $conn->prepare($sql_password);
    $stmt_password->bind_param("si", $hashed_password, $id);
    $stmt_password->execute();
    $stmt_password->close();
  }
  echo "<script>
    sessionStorage.setItem('toastMessage', 'User berhasil diperbarui!');
    window.location.href = '../admin/user.php';
  </script>";
  exit();
} else {
  echo "<script>
    sessionStorage.setItem('toastMessage', 'Error: " . $stmt->error . "');
    window.location.href = '../admin/edit_user.php?id=".$id."';
  </script>";
}

$stmt->close();
$conn->close();
?>