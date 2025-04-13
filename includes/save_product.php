<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.php");
  exit();
}
include '../database/db.php';

// Ambil data dari form
$name = $_POST['name'];
$description = $_POST['description'];
$price = $_POST['price'];
$category = $_POST['category'];

// Proses upload gambar
$image = $_FILES['image']['name'];
$target_dir = "images/";
$target_file = $target_dir . basename($image);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

// Cek apakah file gambar adalah gambar
if (isset($_POST["submit"])) {
  $check = getimagesize($_FILES["image"]["tmp_name"]);
  if ($check !== false) {
    echo "<script>
  sessionStorage.setItem('toastMessage', 'File adalah gambar - " . $check["mime"] . ".');
  window.location.href = '../admin/add_product.php';
</script>";
    $uploadOk = 1;
  } else {
    echo "<script>
  sessionStorage.setItem('toastMessage', 'File bukan gambar.');
  window.location.href = '../admin/add_product.php';
</script>";
    $uploadOk = 0;
  }
}

// Cek ukuran file
if ($_FILES["image"]["size"] > 500000) { // 500 KB
  echo "<script>
  sessionStorage.setItem('toastMessage', 'Maaf, ukuran file terlalu besar.');
  window.location.href = '../admin/add_product.php';
</script>";
  $uploadOk = 0;
}

// Cek format file
if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
  echo "<script>
  sessionStorage.setItem('toastMessage', 'Maaf, hanya file JPG, JPEG, PNG & GIF yang diperbolehkan.');
  window.location.href = '../admin/add_product.php';
</script>";
  $uploadOk = 0;
}

// Jika semua cek berhasil, upload file
if ($uploadOk == 1) {
  if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
    // Siapkan dan jalankan query untuk menyimpan data produk
    $sql = "INSERT INTO products (name, description, price, image, category) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdss", $name, $description, $price, $target_file, $category);

    if ($stmt->execute()) {
      echo "<script>
  sessionStorage.setItem('toastMessage', 'Produk berhasil ditambahkan!');
  window.location.href = '../admin/product.php';
</script>";
      // header("Location: ../admin/product.php");
      exit();
    } else {
      echo "<script>
  sessionStorage.setItem('toastMessage', 'Error: " . $stmt->error . "');
  window.location.href = '../admin/add_product.php';
</script>";
    }
  } else {
    echo "<script>
  sessionStorage.setItem('toastMessage', 'Maaf, terjadi kesalahan saat mengupload file.');
  window.location.href = '../admin/add_product.php';
</script>";
  }
} else {
  echo "<script>
  sessionStorage.setItem('toastMessage', 'Maaf, produk tidak dapat ditambahkan.');
  window.location.href = '../admin/add_product.php';
</script>";
}

$conn->close();
