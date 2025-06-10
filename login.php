<?php
// Memulai session untuk menyimpan data pengguna
session_start();

// Cek apakah pengguna belum login (role tidak terdefinisi)
if (!isset($_SESSION['role'])) {
  // Simpan URL sebelumnya untuk redirect setelah login (jika ada referer)
  if (!isset($_SESSION['redirect_after_login']) && isset($_SERVER['HTTP_REFERER'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['HTTP_REFERER'];
  }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <!-- HEAD: Bagian meta dan pengaturan halaman -->
  <?php
  // Variabel untuk konfigurasi halaman
  $title = 'Login'; // Judul halaman
  $link = 'assets/img/favicon.ico'; // Icon untuk tab browser
  $css = 'css/style.css'; // File CSS utama
  $bootstrap = 'bootstrap/css/bootstrap.min.css'; // File CSS Bootstrap
  
  // Include file style.php yang berisi meta tags dan link CSS
  include 'includes/style.php';
  ?>
</head>

<body class="b-secondary">
  <!-- BODY: Konten utama halaman login -->

  <!-- Toast Notifikasi Error (posisi fixed di pojok kanan bawah) -->
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="liveToast" class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body" id="toastMessage">
          <!-- Tempat pesan error akan muncul -->
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  </div>

  <!-- Container Utama untuk Form Login -->
  <div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="w-100" style="max-width: 400px;">

      <!-- Bagian Logo dan Judul -->
      <div class="text-center mb-3">
        <img src="assets/logo.png" alt="Logo" width="80" height="80" class="mb-2">
        <h1 class="mb-3 fw-bold w300 fs-2">KopiSeduh</h1>
        <h5 class="mb-0 fw-semibold">Log in to your account</h5>
        <p class="fw-lighter fs-6">Enter your email and password below to log in</p>
      </div>

      <!-- Form Login -->
      <form action="includes/auth.php" method="POST">
        <!-- Input Email -->
        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control focus-ring focus-ring-dark border rounded-2 py-2" id="email" name="email" required>
        </div>
        
        <!-- Input Password -->
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control focus-ring focus-ring-dark border rounded-2 py-2" id="password" name="password" required>
        </div>
        
        <!-- Tombol Login -->
        <button type="submit" name="login" class="btn btn-dark w-100">Login</button>
        
        <!-- Link ke Halaman Register -->
        <div class="mt-2 form-text text-center" id="basic-addon4">Don't have an account? <a href="register.php" class="text-decoration-none link-body-emphasis link-offset-2">Sign up</a></div>
      </form>
    </div>
  </div>

  <!-- Toast Notifikasi Sukses -->
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="liveToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body" id="toastMessage">
          <!-- Tempat pesan sukses akan muncul -->
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  </div>

  <!-- Toast Notifikasi Delete -->
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="liveToastDelete" class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body" id="toastMessageDelete">
          <!-- Tempat pesan delete akan muncul -->
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  </div>

  <?php
  // Include file JavaScript
  $bootstrap = 'bootstrap/js/bootstrap.bundle.min.js'; // File JS Bootstrap
  $js = ''; // File JS tambahan (kosong di sini)
  include 'includes/script.php'
  ?>

  <!-- Script untuk Menangani Toast Notification -->
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      // Cek dan tampilkan pesan toast biasa
      const toastMsg = sessionStorage.getItem("toastMessage");
      if (toastMsg) {
        document.getElementById("toastMessage").innerText = toastMsg;
        new bootstrap.Toast(document.getElementById("liveToast")).show();
        sessionStorage.removeItem("toastMessage");
      }

      // Cek dan tampilkan pesan toast untuk delete
      const toastDeleteMsg = sessionStorage.getItem("toastMessageDelete");
      if (toastDeleteMsg) {
        document.getElementById("toastMessageDelete").innerText = toastDeleteMsg;
        new bootstrap.Toast(document.getElementById("liveToastDelete")).show();
        sessionStorage.removeItem("toastMessageDelete");
      }
    });
  </script>
</body>

</html>