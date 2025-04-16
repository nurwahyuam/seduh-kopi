<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login.php");
  exit();
}

include '../database/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $id          = $_POST['id'];
  $method_name       = mysqli_real_escape_string($conn, $_POST['method_name']);
  $acc_number = mysqli_real_escape_string($conn, $_POST['acc_number']);
  $is_active   = $_POST['is_active'];
  $query = "UPDATE payment_methods SET method_name = '$method_name', acc_number = '$acc_number', is_active = '$is_active' WHERE id = $id";

  if (mysqli_query($conn, $query)) {
    echo "<script>
      sessionStorage.setItem('toastMessage', 'Payment Method berhasil diperbarui.');
      window.location.href = '../admin/payment_method.php';
    </script>";
  } else {
    echo "<script>
      sessionStorage.setItem('toastMessageDelete', 'Error: " . mysqli_error($conn) . "');
      window.location.href = '../admin/payment_method.php';
    </script>";
  }
} else {
  echo "<script>
      sessionStorage.setItem('toastMessageDelete', 'Invalid Request.');
      window.location.href = '../admin/payment_method.php';
    </script>";
}
