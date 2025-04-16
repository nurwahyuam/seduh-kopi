<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login.php");
  exit();
}

include '../database/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $title = mysqli_real_escape_string($conn, $_POST['title']);
  $description = mysqli_real_escape_string($conn, $_POST['description']);
  $is_active = isset($_POST['is_active']) ? (int) $_POST['is_active'] : 1;

  if (isset($_FILES['image_url']) && $_FILES['image_url']['error'] === UPLOAD_ERR_OK) {
    $imageName = $_FILES['image_url']['name'];
    $imageTmp = $_FILES['image_url']['tmp_name'];
    $imageExt = pathinfo($imageName, PATHINFO_EXTENSION);
    $allowedExt = ['jpg', 'jpeg', 'png'];

    if (in_array(strtolower($imageExt), $allowedExt)) {
      $newImageName = uniqid('carousel_', true) . '.' . $imageExt;
      $uploadPath = '../images/carousel/' . $newImageName;

      if (move_uploaded_file($imageTmp, $uploadPath)) {
        $sql = "INSERT INTO carousel (title, description, image_url, is_active) VALUES ('$title', '$description', '$newImageName', $is_active)";
        if (mysqli_query($conn, $sql)) {
          echo "<script>sessionStorage.setItem('toastMessage', 'Carousel berhasil ditambahkan.');window.location.href = '../admin/carousel.php';</script>";
        } else {
          echo "Database error: " . mysqli_error($conn);
        }
      } else {
        echo "<script>sessionStorage.setItem('toastMessageDelete', 'Gagal mengupload gambar.');window.location.href = '../admin/carousel.php';</script>";
      }
    } else {
      echo "<script>sessionStorage.setItem('toastMessageDelete', 'Format file tidak didukung. Hanya jpg, jpeg, dan png yang diperbolehkan.');window.location.href = '../admin/carousel.php';</script>";
    }
  } else {
    echo "<script>sessionStorage.setItem('toastMessageDelete', 'File gambar wajib diunggah.');window.location.href = '../admin/carousel.php';</script>";
  }
} else {
  echo "<script>sessionStorage.setItem('toastMessageDelete', 'Invalid request method.');window.location.href = '../admin/carousel.php';</script>";
}
