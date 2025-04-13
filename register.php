<!DOCTYPE html>
<html lang="id">

<head>
  <?php
  $title = 'Register';
  $link = 'assets/img/favicon.ico';
  $css = 'css/style.css';
  $bootstrap = 'bootstrap/css/bootstrap.min.css';
  include 'includes/style.php';
  ?>
</head>

<body class="b-secondary">
  <!-- Register Form -->
  <div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="w-100" style="max-width: 400px;">

      <!-- Logo dan Tagline -->
      <div class="text-center mb-3">
        <img src="assets/logo.png" alt="Logo" width="80" height="80" class="mb-2">
        <h1 class="mb-3 fw-bold w300 fs-2">KopiSeduh</h1>
        <h5 class="mb-0 fw-semibold">Create an account</h5>
        <p class="fw-lighter fs-6">Enter your details below to create your account</p>
      </div>

      <!-- Formulir -->
      <form action="includes/register_process.php" method="POST">
        <div class="mb-3">
          <label for="username" class="form-label">Username</label>
          <input type="text" class="form-control focus-ring focus-ring-dark border rounded-2 py-2" style="font-size: 13px;" id="username" name="username" required>
        </div>
        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control focus-ring focus-ring-dark border rounded-2 py-2" style="font-size: 13px;" id="email" name="email" required>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control focus-ring focus-ring-dark border rounded-2 py-2" style="font-size: 13px;" id="password" name="password" required>
        </div>
        <button type="submit" name="register" class="w-100 btn btn-dark">Register</button>
        <div class="mt-2 form-text text-center" id="basic-addon4">Already have an account? <a href="login.php" class="text-decoration-none link-body-emphasis link-offset-2">Log in</a></div>
      </form>
    </div>
  </div>

  <!-- Toast/Pesan Sementara -->
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="liveToast" class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body" id="toastMessage">
          <!-- Pesan akan ditampilkan di sini -->
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  </div>

  <?php
  $bootstrap = 'bootstrap/js/bootstrap.bundle.min.js';
  $js = '';
  include 'includes/script.php'
  ?>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const toastMsg = sessionStorage.getItem("toastMessage");
      if (toastMsg) {
        document.getElementById("toastMessage").innerText = toastMsg;
        new bootstrap.Toast(document.getElementById("liveToast")).show();
        sessionStorage.removeItem("toastMessage");
      }

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