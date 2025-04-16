<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login.php");
  exit();
}

include '../database/db.php';

// Validasi method POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $method_name = mysqli_real_escape_string($conn, $_POST['method_name']);
  $acc_number = mysqli_real_escape_string($conn, $_POST['acc_number']);
  $is_active = isset($_POST['is_active']) ? (int) $_POST['is_active'] : 1;
  $sql = "INSERT INTO payment_methods (method_name, acc_number, is_active) VALUES ('$method_name', '$acc_number', $is_active)";
  if (mysqli_query($conn, $sql)) {
    echo "<script>sessionStorage.setItem('toastMessage', 'Payment Method berhasil ditambahkan.');window.location.href = '../admin/payment_method.php';</script>";
  } else {
    echo "<script>sessionStorage.setItem('toastMessage', 'Database error: " . mysqli_error($conn) . ".');window.location.href = '../admin/payment_method.php';</script>";
  }
} else {
  echo "<script>sessionStorage.setItem('toastMessageDelete', 'Invalid request method.');window.location.href = '../admin/payment_method.php';</script>";
}
