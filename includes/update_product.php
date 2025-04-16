<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login.php");
  exit();
}

include '../database/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $id = $_POST['id'];
  $name = htmlspecialchars(mysqli_real_escape_string($conn, $_POST['name']));
  $category = htmlspecialchars(mysqli_real_escape_string($conn, $_POST['category']));
  $description = htmlspecialchars(mysqli_real_escape_string($conn, $_POST['description']));
  $price = htmlspecialchars(mysqli_real_escape_string($conn, $_POST['price']));
  $active = $_POST['active'];
  $image = $_FILES['image']['name'];

  if (!empty($image)) {
    $target_dir = "../images/product/";
    $image_name = time() . '_' . basename($image);
    $target_file = $target_dir . $image_name;
    $image_tmp = $_FILES['image']['tmp_name'];
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    if (getimagesize($_FILES["image"]["tmp_name"]) !== false) {
      if ($_FILES["image"]["size"] < 1000000) {
        if ($imageFileType == "jpg" || $imageFileType == "png" || $imageFileType == "jpeg") {
          if (move_uploaded_file($image_tmp, $target_file)) {
            $query = "UPDATE products 
                            SET name = '$name', 
                                description = '$description', 
                                price = '$price', 
                                image = '$image_name', 
                                category = '$category', 
                                active = '$active' 
                            WHERE id = $id";
          } else {
            echo "<script>
            sessionStorage.setItem('toastMessageDelete', 'Upload gagal.');
            window.location.href = '../admin/product.php';</script>";
          }
        } else {
          echo "<script>
          sessionStorage.setItem('toastMessageDelete', 'Maaf, hanya file JPG, JPEG, PNG & GIF yang diperbolehkan.');
          window.location.href = '../admin/product.php';
        </script>";
        }
      } else {
        echo "<script>
        sessionStorage.setItem('toastMessageDelete', 'Maaf, ukuran file terlalu besar.');
        window.location.href = '../admin/product.php';
      </script>";
      }
    } else {
      echo "<script>
    sessionStorage.setItem('toastMessageDelete', 'File bukan gambar.');
    window.location.href = '../admin/product.php';
  </script>";
    }
  } else {
    $query = "UPDATE products 
                      SET name = '$name', 
                          description = '$description', 
                          price = '$price', 
                          category = '$category', 
                          active = '$active' 
                      WHERE id = $id";
  }
  if (mysqli_query($conn, $query)) {
    echo "<script>
      sessionStorage.setItem('toastMessage', 'Product berhasil diperbarui.');
      window.location.href = '../admin/product.php';
    </script>";
  } else {
    echo "<script>
      sessionStorage.setItem('toastMessageDelete', 'Error: " . mysqli_error($conn) . "');
      window.location.href = '../admin/product.php';
    </script>";
  }
} else {
  echo "<script>
      sessionStorage.setItem('toastMessageDelete', 'Invalid Request.');
      window.location.href = '../admin/product.php';
    </script>";
}
