<?php
session_start();

if (!isset($_SESSION['role'])) {
  header("Location: ../login.php");
  exit;
} else if ($_SESSION['role'] === 'user') {
  header("Location: ../user/index.php");
  exit;
}

include '../database/db.php';

// Periksa parameter id
if (!isset($_GET['id']) || empty($_GET['id'])) {
  echo "ID user tidak tersedia.";
  exit();
}

$user_id = $_GET['id'];

$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
  echo "User tidak ditemukan.";
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <?php
  $title = 'Edit User';
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
        'file' => 'product.php',
        'label' => 'Products',
        'icon' => 'bi-box-seam-fill'
      ],
      [
        'file' => 'edit_user.php',
        'label' => 'Users',
        'icon' => 'bi-people-fill'
      ],
    ];
    include '../includes/components/navbar_sider.php';
    ?>

    <!-- Main Content -->
    <div class="flex-grow-1 p-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <input type="text" class="form-control w-50" placeholder="Search...">
        <div class="d-flex align-items-center gap-3">
          <i class="bi bi-bell fs-5"></i>
          <?php if (isset($_SESSION['profile_photo']) && $_SESSION['profile_photo'] !== NULL): ?>
            <img width="32px" height="32px" src="<?= $_SESSION['profile_photo']; ?>" class="profile-img rounded-5" alt="User">
          <?php else: ?>
            <i class="bi bi-person-circle fs-3"></i>
          <?php endif; ?>
          <span><?= $_SESSION['username']; ?></span>
        </div>
      </div>

      <div class="border rounded p-4">
        <div class="d-flex align-items-center gap-2">
          <a href="user.php" class="btn b-primary t-secondary border-1 border-dark r-primary rounded-5 py-2 px-2,5">
            <i class="bi bi-chevron-bar-left"></i>
          </a>
          <h1 class="fs-4 fw-bold t-primary m-0">Edit User</h1>
        </div>
        <div class="container mt-4">
          <form action="../includes/update_user.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $user['id']; ?>">
            <div class="form-group my-2">
              <label for="username">Username</label>
              <input type="text" class="form-control" id="username" name="username" value="<?= $user['username']; ?>" required>
            </div>
            <div class="form-group">
              <label for="password">New Password</label>
              <input type="password" class="form-control" id="password" name="password">
              <div class="form-text" id="basic-addon4">Noted: leave the password blank if nothing is updated.</div>
            </div>
            <div class="form-group my-2">
              <label for="email">Email</label>
              <input type="email" class="form-control" id="email" name="email" step="0.01" value="<?= $user['email']; ?>" required>
            </div>
            <div class="form-group my-2">
              <label for="profile_photo">Foto Profil</label>
              <input type="file" class="form-control" id="profile_photo" name="profile_photo" accept="image/*">
              <div class="form-text" id="basic-addon4">Noted: leave profile photo blank if there is nothing to update</div>
            </div>
            <div class="form-group my-2">
              <label for="role">Role</label>
              <select class="form-control" id="role" name="role" required>
                <option value="admin" <?= ($user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                <option value="user" <?= ($user['role'] == 'user') ? 'selected' : ''; ?>>User</option>
              </select>
            </div>
            <button type="submit" class="btn btn-success my-2 w-100">Save</button>
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

  <?php include '../includes/script.php'; ?>
</body>

</html>