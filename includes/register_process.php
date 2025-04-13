<?php
include '../database/db.php';

if (isset($_POST['register'])) {
  $username = $_POST['username'];
  $email = $_POST['email'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $role = 'user';
};

$query = "INSERT INTO users (username, email, password, role) VALUES ('$username', '$email', '$password', '$role')";

if (mysqli_query($conn, $query)) {
  echo "<script>
        sessionStorage.setItem('toastMessage', 'Pengguna Telah Mendaftarkan Diri');
        window.location.href = '../login.php';
    </script>";
  exit();
} else {
  echo "<script>
        sessionStorage.setItem('toastMessageDelete', 'Error: " . mysqli_error($conn) . "'');
        window.location.href = '../login.php';
    </script>";
  exit();
}