<?php
session_start();
include '../database/db.php';

$email = htmlspecialchars($_POST['email']);
$password = htmlspecialchars($_POST['password']);

// Query untuk mendapatkan data pengguna
$sql = "SELECT * FROM users WHERE email='$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    // Verifikasi password
    if (password_verify($password, $user['password'])) {
        // Set session
        $_SESSION['id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['profile_photo'] = $user['profile_photo'];
        $_SESSION['role'] = $user['role'];

        // Redirect berdasarkan role
        if ($user['role'] == 'admin') {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: ../user/index.php"); // Halaman user
        }
    } else {
        echo "<script>
  sessionStorage.setItem('toastMessageDelete', 'Password salah!');
  window.location.href = '../login.php';
</script>";
    }
} else {
    echo "<script>
        sessionStorage.setItem('toastMessageDelete', 'Pengguna tidak ditemukan!');
        window.location.href = '../login.php';
    </script>";
}

$conn->close();
?>