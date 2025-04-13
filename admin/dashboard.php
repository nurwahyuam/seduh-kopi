<?php
session_start();

$current_page = basename($_SERVER['PHP_SELF']);

if (!isset($_SESSION['role'])) {
  header("Location: ../login.php");
  exit;
} else if (isset($_SESSION['role']) && $_SESSION['role'] === 'user') {
  header("Location: ../user/index.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <?php
  $title = 'Homepage';
  $link = '../assets/img/favicon.ico';
  $css = '../css/style.css';
  $bootstrap = '../bootstrap/css/bootstrap.min.css';
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
        'file' => 'product.php',
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
      <div class="d-flex justify-content-end align-items-center mb-4">
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

      <div class="border rounded p-5" style="height: 500px; background: repeating-linear-gradient(45deg, #f8f9fa, #f8f9fa 10px, #fff 10px, #fff 20px);">
        <p class="text-muted text-center">Main Content Area</p>
      </div>
    </div>
  </div>

  <?php
  $bootstrap = '../bootstrap/js/bootstrap.bundle.min.js';
  $js = '../js/script.js';
  include '../includes/script.php'
  ?>
</body>

</html>