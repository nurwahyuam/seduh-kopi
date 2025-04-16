<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login.php");
  exit();
}

include '../database/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $id = (INT) $_POST['id'];
  $username = htmlspecialchars(mysqli_real_escape_string($conn, $_POST['username']));
  $email = htmlspecialchars(mysqli_real_escape_string($conn, $_POST['email']));
  $password = $_POST['password'];
  $role = $_POST['role'];
  $profile_photo = $_FILES['profile_photo']['name'];

  if (!empty($profile_photo)) {
    $target_dir = "../images/user/";
    $image_name = time() . '_' . basename($profile_photo);
    $target_file = $target_dir . $image_name;
    $image_tmp = $_FILES['profile_photo']['tmp_name'];
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    if (getimagesize($_FILES["profile_photo"]["tmp_name"]) !== false) {
      if ($_FILES["profile_photo"]["size"] < 1000000) {
        if ($imageFileType == "jpg" || $imageFileType == "png" || $imageFileType == "jpeg") {
          if (move_uploaded_file($image_tmp, $target_file)) {
            if (!empty($password)) {
              $hashed_password = password_hash($password, PASSWORD_DEFAULT);
              $query = "UPDATE users 
                            SET username = '$username', 
                                email = '$email', 
                                password = '$hashed_password',
                                profile_photo = '$image_name', 
                                role = '$role' 
                            WHERE id = $id";
            } else {
              $query = "UPDATE users 
                            SET username = '$username', 
                                email = '$email', 
                                profile_photo = '$image_name', 
                                role = '$role' 
                            WHERE id = $id";
            }
          } else {
            echo "<script>
            sessionStorage.setItem('toastMessageDelete', 'Upload gagal.');
            window.location.href = '../admin/user.php';</script>";
          }
        } else {
          echo "<script>
          sessionStorage.setItem('toastMessageDelete', 'Maaf, hanya file JPG, JPEG, PNG & GIF yang diperbolehkan.');
          window.location.href = '../admin/user.php';
        </script>";
        }
      } else {
        echo "<script>
        sessionStorage.setItem('toastMessageDelete', 'Maaf, ukuran file terlalu besar.');
        window.location.href = '../admin/user.php';
      </script>";
      }
    } else {
      echo "<script>
    sessionStorage.setItem('toastMessageDelete', 'File bukan gambar.');
    window.location.href = '../admin/user.php';
  </script>";
    }
  } else {
    if (!empty($password)) {
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);
      $query = "UPDATE users 
                    SET username = '$username', 
                        email = '$email', 
                        password = '$hashed_password',
                        role = '$role' 
                    WHERE id = $id";
    } else {
      $query = "UPDATE users 
                    SET username = '$username', 
                        email = '$email', 
                        role = '$role' 
                    WHERE id = $id";
    }
  }
  if (mysqli_query($conn, $query)) {
    echo "<script>
    sessionStorage.setItem('toastMessage', 'User berhasil diperbarui.');
    window.location.href = '../admin/user.php';
  </script>";
  } else {
    echo "<script>
    sessionStorage.setItem('toastMessageDelete', 'Error: " . mysqli_error($conn) . "');
    window.location.href = '../admin/user.php';
  </script>";
  }
} else {
  echo "<script>
    sessionStorage.setItem('toastMessageDelete', 'Invalid Request.');
    window.location.href = '../admin/user.php';
  </script>";
}
