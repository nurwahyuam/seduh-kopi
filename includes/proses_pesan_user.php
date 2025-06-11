<?php
include '../database/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $firstname = htmlspecialchars($_POST['first_name']);
  $lastname = htmlspecialchars($_POST['last_name']);
  $email = htmlspecialchars($_POST['email']);
  $phoneNumber = htmlspecialchars($_POST['phone']);
  $message = htmlspecialchars($_POST['message']);

  $sql = "INSERT INTO contact_messages (first_name, last_name, email, phone, message) 
          VALUES ('$firstname', '$lastname', '$email', '$phoneNumber', '$message')";

  if (mysqli_query($conn, $sql)) {
    echo "<script>
            sessionStorage.setItem('toastMessage', 'Pesan dari anda sudah dikirim.');
            window.location.href = '../user/contact.php';
          </script>";
  } else {
    echo "<script>
            sessionStorage.setItem('toastMessageDelete', 'Database error: " . mysqli_error($conn) . "');
            window.location.href = '../user/contact.php';
          </script>";
  }
} else {
  echo "<script>
          sessionStorage.setItem('toastMessageDelete', 'Permintaan tidak valid.');
          window.location.href = '../user/contact.php';
        </script>";
}
?>