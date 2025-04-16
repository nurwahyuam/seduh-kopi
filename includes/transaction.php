<?php
session_start();


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
  header("Location: login.php");
  exit();
}
include '../database/db.php';

// Ambil data dari form
$order_id = htmlspecialchars($_POST['order_id']);
$payment_method_id = htmlspecialchars($_POST['payment_methods']);
$amount = htmlspecialchars($_POST['amount']);
$phone_number = htmlspecialchars($_POST['phone_number']);
$address = htmlspecialchars($_POST['address']);
$status = 'pending';

// Validasi input wajib
if (empty($order_id) || empty($payment_method_id) || empty($amount) || empty($phone_number) || empty($address)) {
  echo "<script>
        sessionStorage.setItem('toastMessage', 'Semua field harus diisi!');
        window.location.href = '../user/payment.php?order_id=$order_id';
    </script>";
  exit();
}

// Validasi upload file
if (!isset($_FILES["payment_proof"]) || $_FILES["payment_proof"]["error"] !== 0) {
  echo "<script>
        sessionStorage.setItem('toastMessage', 'Mohon upload bukti pembayaran.');
        window.location.href = '../user/payment.php?order_id=$order_id';
    </script>";
  exit();
}

$payment_proof = $_FILES['payment_proof']['name'];
$target_dir = "../images/bukti/";
$imageFileType = strtolower(pathinfo($payment_proof, PATHINFO_EXTENSION));
$new_filename = uniqid('bukti_') . '.' . $imageFileType;
$final_path = $target_dir . $new_filename;

// Validasi gambar
$uploadOk = 1;
$check = getimagesize($_FILES["payment_proof"]["tmp_name"]);
if ($check === false) {
  echo "<script>
        sessionStorage.setItem('toastMessage', 'File yang diupload bukan gambar!');
        window.location.href = '../user/payment.php?order_id=$order_id';
    </script>";
  $uploadOk = 0;
}

// Maksimal ukuran 1MB
if ($_FILES["payment_proof"]["size"] > 1000000) {
  echo "<script>
        sessionStorage.setItem('toastMessage', 'Ukuran file terlalu besar. Maksimal 1MB.');
        window.location.href = '../user/payment.php?order_id=$order_id';
    </script>";
  $uploadOk = 0;
}

// Validasi ekstensi
if (!in_array($imageFileType, ['jpg', 'jpeg', 'png'])) {
  echo "<script>
        sessionStorage.setItem('toastMessage', 'Format file harus JPG, JPEG, atau PNG.');
        window.location.href = '../user/payment.php?order_id=$order_id';
    </script>";
  $uploadOk = 0;
}

// Buat folder jika belum ada
if (!is_dir($target_dir)) {
  mkdir($target_dir, 0777, true);
}

// Upload & Simpan data
if ($uploadOk === 1) {
  if (move_uploaded_file($_FILES["payment_proof"]["tmp_name"], $final_path)) {
    $save_path = 'images/bukti/' . $new_filename;
    $sql = "INSERT INTO transactions (order_id, payment_method_id, amount, transaction_date, payment_proof, status, phone_number, address) 
            VALUES (?, ?, ?, NOW(), ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iidssss", $order_id, $payment_method_id, $amount, $save_path, $status, $phone_number, $address);

    if ($stmt->execute()) {
      unset($_SESSION['start_time']);
      $notifAdmin = "Order #$order_id telah berhasil checkout dan dibayar. Mohon dicek min";
      $userQuerry = $conn->query("SELECT DISTINCT id FROM users WHERE role = 'admin'");
      while ($user = $userQuerry->fetch_assoc()) {
        $admin_id = $user['id'];
        $addQuery = $conn->prepare("INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())");
        $addQuery->bind_param("is", $admin_id, $notifAdmin);
        $addQuery->execute();
      }
      $user_id = $_SESSION['id'];
      $notif = "Order #$order_id telah berhasil checkout dan pembayaran. Mohon tunggu konfirmasi lagi";
      $addQuery = $conn->prepare("INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())");
      $addQuery->bind_param("is", $user_id, $notif);
      $addQuery->execute();

      echo "<script>
              sessionStorage.setItem('toastMessage', 'Pembayaran berhasil dikirim. Tunggu konfirmasi dari admin.');
              window.location.href = '../user/index.php';
            </script>";
      exit();
    } else {
      echo "<script>
              sessionStorage.setItem('toastMessage', 'Gagal menyimpan transaksi: " . addslashes($conn->error) . "');
              window.location.href = '../user/payment.php?order_id=$order_id';
            </script>";
    }
  } else {
    $error = error_get_last();
    echo "<script>
            sessionStorage.setItem('toastMessage', 'Upload gambar gagal: " . addslashes($error['message'] ?? 'Unknown error') . "');
            window.location.href = '../user/payment.php?order_id=$order_id';
          </script>";
  }
} else {
  echo "<script>
        sessionStorage.setItem('toastMessage', 'Transaksi gagal diproses. Silakan coba lagi.');
        window.location.href = '../user/payment.php?order_id=$order_id';
    </script>";
}

$conn->close();
