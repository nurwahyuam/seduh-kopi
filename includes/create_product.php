<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login.php");
  exit();
}

include '../database/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $name = htmlspecialchars(mysqli_real_escape_string($conn, $_POST['name']));
  $category = htmlspecialchars(mysqli_real_escape_string($conn, $_POST['category']));
  $description = htmlspecialchars(mysqli_real_escape_string($conn, $_POST['description']));
  $price = htmlspecialchars(mysqli_real_escape_string($conn, $_POST['price']));
  $active = isset($_POST['active']) ? (int) $_POST['active'] : 1;

  // Validasi dan upload gambar
  if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $imageName = $_FILES['image']['name'];
    $imageTmp = $_FILES['image']['tmp_name'];
    $imageExt = pathinfo($imageName, PATHINFO_EXTENSION);
    $allowedExt = ['jpg', 'jpeg', 'png'];

    if (in_array(strtolower($imageExt), $allowedExt)) {
      $newImageName = uniqid('product_', true) . '.' . $imageExt;
      $uploadPath = '../images/product/' . $newImageName;

      if ($_FILES["image"]["size"] < 1000000) {
        if (move_uploaded_file($imageTmp, $uploadPath)) {
          $sql = "INSERT INTO products (name, description, price, image, category, active) VALUES ('$name', '$description', '$price', '$newImageName', '$category', $active)";
          if (mysqli_query($conn, $sql)) {
            echo "<script>sessionStorage.setItem('toastMessage', 'Product berhasil ditambahkan.');window.location.href = '../admin/product.php';</script>";
          } else {
            echo "<script>sessionStorage.setItem('toastMessageDelete', 'Database error: " . mysqli_error($conn) . ".');window.location.href = '../admin/product.php';</script>";
          }
        } else {
          echo "<script>sessionStorage.setItem('toastMessageDelete', 'Gagal mengupload gambar.');window.location.href = '../admin/product.php';</script>";
        }
      } else {
        echo "<script>
    sessionStorage.setItem('toastMessageDelete', 'File bukan gambar.');
    window.location.href = '../admin/product.php';
  </script>";
      }
    } else {
      echo "<script>sessionStorage.setItem('toastMessageDelete', 'Format file tidak didukung. Hanya jpg, jpeg, dan png yang diperbolehkan.');window.location.href = '../admin/product.php';</script>";
    }
  } else {
    echo "<script>sessionStorage.setItem('toastMessageDelete', 'File gambar wajib diunggah.');window.location.href = '../admin/product.php';</script>";
  }
} else {
  echo "<script>sessionStorage.setItem('toastMessageDelete', 'Invalid request method.');window.location.href = '../admin/product.php';</script>";
}
