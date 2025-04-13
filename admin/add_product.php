<?php
session_start();

if (!isset($_SESSION['role'])) {
  header("Location: ../login.php");
  exit;
} else if (isset($_SESSION['role']) && $_SESSION['role'] === 'user') {
  header("Location: ../user/index.php");
  exit;
}

// include '../includes/db.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <?php
  $title = 'Create Product';
  $link = '../assets/img/favicon.ico';
  $css = '../css/style.css';
  include '../includes/style.php';
  ?>
</head>

<body>
  <div class="d-flex vh-100">
    <!-- Sidebar -->
    <?php
    $navlink = [
      [
        'file' => 'dashboard.php',
        'label' => 'Home',
        'icon' => 'bi-house-fill'
      ],
      [
        'file' => 'add_product.php',
        'label' => 'Products',
        'icon' => 'bi-box-seam-fill'
      ],
      [
        'file' => 'user.php',
        'label' => 'Users',
        'icon' => 'bi-people-fill'
      ],
    ];
    include '../includes/components/navbar_sider.php'
    ?>

    <!-- Main Content -->
    <div class="flex-grow-1 p-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <input type="text" class="form-control w-50" placeholder="Search...">
        <div class="d-flex align-items-center gap-3">
          <i class="bi bi-bell fs-5"></i>
          <?php if ($_SESSION['profile_photo'] !== NULL): ?>
            <img width="32px" height="32px" src="<?= $_SESSION['profile_photo']; ?>" class="profile-img rounded-5" alt="User">
          <?php else: ?>
            <i class="bi bi-person-circle fs-3"></i>
          <?php endif; ?>
          <span><?= $_SESSION['username']; ?></span>
        </div>
      </div>

      <div class="border rounded p-4">
        <!-- <p class="text-muted text-center">Main Content Area</p> -->
        <div class="d-flex align-items-center gap-2">
          <a href="product.php" class="btn b-primary t-secondary border-1 border-dark r-primary rounded-5 py-2 px-2,5"><i class="bi bi-chevron-bar-left"></i></a>
          <h1 class="fs-4 fw-bold t-primary m-0">Create Product</h1>
        </div>
        <div class="container mt-4">
          <form action="../includes/save_product.php" method="POST" enctype="multipart/form-data">
            <div class="form-group my-2">
              <label for="name">Name Product</label>
              <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="form-group my-2">
              <label for="description">Description</label>
              <textarea class="form-control" id="description" name="description" required></textarea>
            </div>
            <div class="form-group my-2">
              <label for="price">Price</label>
              <input type="number" class="form-control" id="price" name="price" step="0.01" required>
            </div>
            <div class="form-group my-2">
              <label for="image">Photo Product</label>
              <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
            </div>
            <div class="form-group my-2">
              <label for="category">Category</label>
              <input type="text" class="form-control" id="category" name="category" required>
            </div>
            <button type="submit" class="btn btn-success my-2 w-100">Create</button>
          </form>
        </div>
      </div>
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

  <?php include '../includes/script.php' ?>
</body>

</html>