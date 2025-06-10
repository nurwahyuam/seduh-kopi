<!DOCTYPE html>
<html lang="id">

<head>
  <!-- HEAD: Bagian kepala dokumen HTML -->
  <?php
  // Variabel untuk konfigurasi halaman
  $title = 'Register'; // Judul halaman
  $link = 'assets/img/favicon.ico'; // Icon untuk tab browser
  $css = 'css/style.css'; // File CSS utama
  $bootstrap = 'bootstrap/css/bootstrap.min.css'; // File CSS Bootstrap
  
  // Include file style.php yang berisi meta tags dan link CSS
  include 'includes/style.php';
  ?>
</head>

<body class="b-secondary">
  <!-- BODY: Konten utama yang ditampilkan di browser -->
  
  <!-- Container utama untuk form register -->
  <div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="w-100" style="max-width: 400px;">

      <!-- Bagian Logo dan Tagline -->
      <div class="text-center mb-3">
        <img src="assets/logo.png" alt="Logo" width="80" height="80" class="mb-2">
        <h1 class="mb-3 fw-bold w300 fs-2">KopiSeduh</h1>
        <h5 class="mb-0 fw-semibold">Create an account</h5>
        <p class="fw-lighter fs-6">Enter your details below to create your account</p>
      </div>

      <!-- Formulir Register -->
      <form action="includes/register_process.php" method="POST">
        <!-- Input Username -->
        <div class="mb-3">
          <label for="username" class="form-label">Username</label>
          <input type="text" class="form-control focus-ring focus-ring-dark border rounded-2 py-2" style="font-size: 13px;" id="username" name="username" required>
        </div>
        
        <!-- Input Email -->
        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control focus-ring focus-ring-dark border rounded-2 py-2" style="font-size: 13px;" id="email" name="email" required>
        </div>
        
        <!-- Input Password -->
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control focus-ring focus-ring-dark border rounded-2 py-2" style="font-size: 13px;" id="password" name="password" required>
        </div>
        
        <!-- Tombol Submit -->
        <button type="submit" name="register" class="w-100 btn btn-dark">Register</button>
        
        <!-- Link ke halaman Login -->
        <div class="mt-2 form-text text-center" id="basic-addon4">Already have an account? <a href="login.php" class="text-decoration-none link-body-emphasis link-offset-2">Log in</a></div>
      </form>
    </div>
  </div>

  <!-- Toast/Pesan Notifikasi -->
  <!-- Akan muncul ketika ada pesan yang disimpan di sessionStorage -->
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="liveToast" class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body" id="toastMessage">
          <!-- Pesan akan ditampilkan di sini melalui JavaScript -->
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  </div>

  <?php
  // Include file script.php yang berisi JavaScript
  $bootstrap = 'bootstrap/js/bootstrap.bundle.min.js'; // File JS Bootstrap
  $js = ''; // File JS tambahan (kosong di sini)
  include 'includes/script.php'
  ?>
  
  <!-- Script untuk menangani toast notification -->
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      // Cek apakah ada pesan toast yang disimpan di sessionStorage
      const toastMsg = sessionStorage.getItem("toastMessage");
      if (toastMsg) {
        // Tampilkan pesan error
        document.getElementById("toastMessage").innerText = toastMsg;
        new bootstrap.Toast(document.getElementById("liveToast")).show();
        sessionStorage.removeItem("toastMessage"); // Hapus pesan setelah ditampilkan
      }

      // Cek apakah ada pesan toast untuk delete (tidak digunakan di halaman ini)
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