<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../login.php");
  exit();
}

include '../database/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transaction_id']) && isset($_POST['status'])) {
  $transaction_id = intval($_POST['transaction_id']);
  $new_status = $_POST['status'];

  // Cek apakah transaksi valid
  $stmt = $conn->prepare("SELECT order_id FROM transactions WHERE id = ?");
  $stmt->bind_param("i", $transaction_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();

  if (!$row) {
    echo "<script>alert('Transaksi tidak ditemukan!'); window.location.href = '../admin/transaction.php';</script>";
    exit;
  }

  $order_id = $row['order_id'];

  // Update status di tabel transactions
  $stmt = $conn->prepare("UPDATE transactions SET status = ? WHERE id = ?");
  $stmt->bind_param("si", $new_status, $transaction_id);
  $stmt->execute();

  // Jika status 'completed', update juga orders
  if ($new_status === 'completed') {
    $stmt = $conn->prepare("UPDATE orders SET status = 'paid' WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
  } elseif ($new_status === 'failed') {
    $stmt = $conn->prepare("UPDATE orders SET status = 'expired' WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
  }

  // Ambil user_id dari order
  $stmt = $conn->prepare("SELECT user_id FROM orders WHERE id = ?");
  $stmt->bind_param("i", $order_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $order_user = $result->fetch_assoc();
  $user_id = $order_user['user_id'];

  // Insert notifikasi
  if ($new_status === 'completed') {
    $notif = "$new_status, Order #$order_id telah dibayar. Terima kasih sudah Order di SeduhKopi";
  } elseif ($new_status === 'failed') {
    $notif = "$new_status, Mohon Maaf Order #$order_id gagal.";
  } else {
    $notif = "$new_status, Mohon Ditunggu Order #$order_id diproseskan. Tunggu Konfirmasi Setelah ini.";
  }
  $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())");
  $stmt->bind_param("is", $user_id, $notif);
  $stmt->execute();

  echo "<script>
    sessionStorage.setItem('toastMessage', 'Transaksi berhasil diperbarui.');
    window.location.href = '../admin/transaction.php';
  </script>";
} else {
  echo "<script>
    sessionStorage.setItem('toastMessage', 'Data tidak lengkap!');
    window.location.href = '../admin/transaction.php';
  </script>";
}
