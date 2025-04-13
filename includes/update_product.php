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
  echo "Product tidak ditemukan.";
  exit();
}

// Ambil data dari form
$id = $_POST['id'];
$name = $_POST['name'];
$description = $_POST['description'];
$price = $_POST['price'];
$category = $_POST['category'];

// Proses upload gambar jika ada
$image = $_FILES['image']['name'];
$target_dir = "images/";
$uploadOk = 1;

// Cek apakah file gambar diupload
if (!empty($image)) {
  $target_file = $target_dir . basename($image);
  $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

  // Cek apakah file gambar adalah gambar
  $check = getimagesize($_FILES["image"]["tmp_name"]);
  if ($check === false) {
    echo "<script>
  sessionStorage.setItem('toastMessage', 'File bukan gambar.');
  window.location.href = '../admin/edit_product.php?id=" . $id . "';
</script>";
    exit();
  }

  // Cek ukuran file
  if ($_FILES["image"]["size"] > 500000) { // 500 KB
    echo "<script>
  sessionStorage.setItem('toastMessage', 'Maaf, ukuran file terlalu besar.');
  window.location.href = '../admin/edit_product.php?id=" . $id . "';
</script>";
    exit();
  }

  // Cek format file
  if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
    echo "<script>
  sessionStorage.setItem('toastMessage', 'Maaf, hanya file JPG, JPEG, PNG & GIF yang diperbolehkan.');
  window.location.href = '../admin/edit_product.php?id=".$id."';
</script>";
exit();
  }
  if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
    echo "<script>
  sessionStorage.setItem('toastMessage', 'Maaf, terjadi kesalahan saat mengupload file.');
  window.location.href = '../admin/edit_product.php?id=".$id."';
  </script>";
    exit();
  }

  // Update data produk dengan gambar baru
  $sql = "UPDATE products SET name = ?, description = ?, price = ?, image = ?, category = ? WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ssdssi", $name, $description, $price, $target_file, $category, $id);
} else {
  // Update data produk tanpa mengubah gambar
  $sql = "UPDATE products SET name = ?, description = ?, price = ?, category = ? WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ssdsi", $name, $description, $price, $category, $id);
}

// Eksekusi query
if ($stmt->execute()) {
  echo "<script>
  sessionStorage.setItem('toastMessage', 'Produk berhasil diperbarui!');
  window.location.href = '../admin/product.php';
</script>";
  // Redirect ke halaman produk atau halaman lain setelah berhasil
  // header("Location: ../admin/product.php");
  exit();
} else {
  echo "<script>
  sessionStorage.setItem('toastMessage', 'Error: " . $stmt->error . "');
  window.location.href = '../admin/edit_product.php?id=".$id."';
  </script>";
}

$stmt->close();
$conn->close();
?>