<?php
session_start();

if (!isset($_SESSION['role'])) {
  header("Location: ../login.php");
  exit;
} else if (isset($_SESSION['role']) && $_SESSION['role'] === 'user') {
  header("Location: ../user/index.php");
  exit;
}

include '../database/db.php';

// Tentukan jumlah produk per halaman
$limit = 5; // Misalnya, 5 produk per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Ambil total jumlah produk
$total_result = $conn->query("SELECT COUNT(*) AS total FROM products");
$total_row = $total_result->fetch_assoc();
$total_products = $total_row['total'];
$total_pages = ceil($total_products / $limit);

// Ambil data produk dengan pagination
$sql = "SELECT * FROM products LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $offset, $limit);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <?php
  $title = 'Products';
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
        <div class="d-flex align-items-center justify-content-between">
          <h1 class="fs-4 fw-bold">Daftar Products</h1>
          <button type="button" class="btn btn-success p-0">
            <a href="add_product.php" class="btn btn-success">+ Create</a>
          </button>
        </div>
        <hr>
        <table class="mt-2 table table-bordered">
          <thead>
            <tr>
              <th class="text-center" style=" vertical-align: middle;">No</th>
              <th style=" vertical-align: middle;">Name</th>
              <th style=" vertical-align: middle;">Description</th>
              <th style=" vertical-align: middle;">Prize</th>
              <th style=" vertical-align: middle;">Category</th>
              <th class="text-center" style=" vertical-align: middle;">Photo Product</th>
              <th class="text-center" style=" vertical-align: middle;">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result->num_rows > 0): ?>
              <?php $i = 1;
              while ($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                  <td class="text-center" style=" vertical-align: middle;"><?= $i++; ?></td>
                  <td style=" vertical-align: middle;"><?= $row['name'] ?></td>
                  <td style=" vertical-align: middle;"><?= substr($row['description'], 0, 20) ?>...</td>
                  <td style=" vertical-align: middle;"><?= number_format($row['price'], 0, ',', '.') ?></td>
                  <td style=" vertical-align: middle;"><?= $row['category'] ?></td>
                  <td class="text-center"><img width="58px" height="58px" src="../<?= $row['image'] ?>" alt="Image"></td>
                  <td class="text-center" style=" vertical-align: middle;">
                    <a href="edit_product.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm text-white">Edit</a>
                    <a href="../includes/delete_product.php?id=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus?')" class="btn btn-danger btn-sm ">Delete</a>
                  </td>
                </tr>
              <?php } ?>
            <?php else: ?>
              <tr>
                <td colspan="7">
                  <p>Tidak ada produk yang tersedia.</p>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
        <nav aria-label="Page navigation">
          <ul class="pagination justify-content-center m-0">
            <?php if ($page > 1): ?>
              <li class="page-item">
                <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                  <span aria-hidden="true">&laquo;</span>
                </a>
              </li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
              <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
              </li>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
              <li class="page-item">
                <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                  <span aria-hidden="true">&raquo;</span>
                </a>
              </li>
            <?php endif; ?>
          </ul>
        </nav>
      </div>
    </div>
  </div>

  <!-- Toast/Pesan Sementara -->
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="liveToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body" id="toastMessage">
          <!-- Pesan akan ditampilkan di sini -->
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  </div>
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="liveToastDelete" class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body" id="toastMessageDelete">
          <!-- Pesan akan ditampilkan di sini -->
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
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