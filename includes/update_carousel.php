<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login.php");
  exit();
}

include '../database/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $id          = $_POST['id'];
  $title       = mysqli_real_escape_string($conn, $_POST['title']);
  $description = mysqli_real_escape_string($conn, $_POST['description']);
  $is_active   = $_POST['is_active'];
  $image       = $_FILES['image_url']['name'];

  // Cek jika ada file yang diupload
  if (!empty($image)) {
    $target_dir = "../images/carousel/";
    $image_name = time() . '_' . basename($image); // rename file biar unik
    $target_file = $target_dir . $image_name;
    $image_tmp = $_FILES['image_url']['tmp_name'];

    // Pindahkan file
    if (move_uploaded_file($image_tmp, $target_file)) {
      // Update dengan gambar baru
      $query = "UPDATE carousel 
                      SET title = '$title', 
                          description = '$description', 
                          image_url = '$image_name', 
                          is_active = '$is_active' 
                      WHERE id = $id";
    } else {
      echo "<script>
      sessionStorage.setItem('toastMessageDelete', 'Upload gagal.');
      window.location.href = '../admin/carousel.php';</script>";
    }
  } else {
    // Update tanpa gambar
    $query = "UPDATE carousel 
                  SET title = '$title', 
                      description = '$description', 
                      is_active = '$is_active' 
                  WHERE id = $id";
  }

  if (mysqli_query($conn, $query)) {
    echo "<script>
      sessionStorage.setItem('toastMessage', 'Carousel berhasil diperbarui.');
      window.location.href = '../admin/carousel.php';
    </script>";
  } else {
    echo "<script>
      sessionStorage.setItem('toastMessageDelete', 'Error: " . mysqli_error($conn) . "');
      window.location.href = '../admin/carousel.php';
    </script>";
  }
} else {
  echo "<script>
      sessionStorage.setItem('toastMessageDelete', 'Invalid Request.');
      window.location.href = '../admin/carousel.php';
    </script>";
}
