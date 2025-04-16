<?php
session_start();

if (!isset($_SESSION['role'])) {
  header("Location: ../login.php");
  exit;
} else if ($_SESSION['role'] === 'admin') {
  header("Location: ../admin/dashboard.php");
  exit;
} else if (isset($_SESSION['start_time'])) {
  header("Location: ../.php");
  exit;
}

include '../database/db.php';

$product_id = $_GET['id'];


// Ambil data produk dengan limit dan offset
$product_sql = "SELECT * FROM products WHERE id = ?";
$product_stmt = $conn->prepare($product_sql);
$product_stmt->bind_param("i", $product_id);
$product_stmt->execute();
$product_result = $product_stmt->get_result();

// Ambil data notifikasi
$userId = $_SESSION['id'];
$query = mysqli_query($conn, "SELECT COUNT(*) AS unread FROM notifications WHERE user_id = $userId AND is_read = 0");
$data = mysqli_fetch_assoc($query);
$unreadCount = $data['unread'];

// Ambil data orders
$queryOrders = mysqli_query($conn, "SELECT * FROM orders WHERE user_id = $userId AND status = 'pending' ORDER BY created_at DESC LIMIT 1");
$dataOrders = mysqli_fetch_assoc($queryOrders);
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <?php
  $title = 'SeduhKopi';
  $link = '../assets/img/favicon.ico';
  $css = '../css/style.css';
  $bootstrap = '../bootstrap/css/bootstrap.min.css';
  include '../includes/style.php';
  ?>
</head>

<body>

  <?php
  $link = '../assets/logo.png';
  $navlink = [
    'index.php' => 'Home',
    'product.php' => 'Products',
  ];
  $droplink = [
    '../about_me.php' => 'About Me',
    '../contact.php' => 'Contact',
  ];
  include '../includes/components/navbar.php';
  ?>

  <!-- Produk Section -->
  <section class="produk py-4">
    <div class="container">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="text-center fw-semibold">Detail Product</h2>
        <a href="product.php" class="d-flex align-items-center gap-2">Shopping cart<i class="bi bi-arrow-right"></i></a>
      </div>
      <div class="row">
        <?php while ($row = $product_result->fetch_assoc()) { ?>
          <div class="col-4">
            <div class="card h-100 border-0 shadow-sm">
              <img src="../images/product/<?= $row['image'] ?>" class="card-img-top" alt="<?= $row['name'] ?>">
            </div>
          </div>
          <div class="col-8">
          <div class="card-body">
                <h5 class="fw-semibold fs-3"><?= $row['name'] ?></h5>
                <p><span class="badge bg-secondary rounded-pill"><?= $row['category'] ?></span>
                  <?php if ($row['active'] == 1): ?>
                    <span class=" badge bg-success rounded-pill">Active</span>
                  <?php else : ?>
                    <span class=" badge bg-danger rounded-pill">Not Active</span>
                  <?php endif; ?>
                </p>
                <p class="fw-medium fs-5">Rp.<?= number_format($row['price'], 2, ',', '.') ?></p>
                <p class="fw-normal"><?= $row['description'] ?></p>
                <button class="btn btn-dark btn-sm add-to-cart w-100" data-id="<?= $row['id'] ?>" data-name="<?= $row['name'] ?>" data-price="<?= $row['price'] ?>"><i class="bi bi-cart4"></i> Add to Cart</button>
              </div>
          </div>
        <?php }; ?>
      </div>
    </div>
  </section>

  <!-- Offcanvas Keranjang -->
  <div style="width: 60vh;" class="offcanvas offcanvas-start bg-dark text-light px-3" tabindex="-1" id="offcanvasKeranjang" aria-labelledby="offcanvasKeranjangLabel" data-bs-scroll="true">
    <div class="offcanvas-header d-flex align-items-center">
      <div class="d-flex align-items-center gap-3">
        <img src="../assets/logo.png" width="48" height="48" alt="Logo" class="bg-light rounded-circle p-1">
        <h1 id="offcanvasKeranjangLabel" class="fw-bold fs-2 w300 m-0">SeduhKopi</h1>
      </div>
      <button type="button" class="btn-close bg-light" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <hr class="m-0">
    <div class="offcanvas-body">
      <ul id="keranjangItems" class="list-group">
        <!-- Item keranjang akan ditambahkan di sini -->
      </ul>
      <div class="mt-3" id="bodyTotalHarga">
        <!-- Total Item keranjang -->
      </div>
      <div id="boxButton" class="w-100 gap-2 d-flex align-items-center">
        <!-- Buttons -->
      </div>
    </div>
  </div>
  <!-- END Offcanvas Keranjang -->

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

  <?php
  $footlink = [
    'index.php' => 'Home',
    'about_me.php' => 'About Me',
    'contact.php' => 'Contact',
  ];
  include '../includes/footer.php';
  ?>

  <script>
    document.getElementById('notifBtn')?.addEventListener('click', function() {
      fetch('../includes/mark_read.php')
        .then(res => res.json())
        .then(data => {
          if (data.status === "success") {
            notifCount = document.getElementById('notifCount');
            notifCount.classList.remove('.bagde', 'bg-danger');
          } else {
            console.error(data.message);
          }
        })
        .catch(error => console.error("Gagal menghubungi server:", error));
    });
  </script>
  <?php
  $bootstrap = '../bootstrap/js/bootstrap.bundle.min.js';
  $js = '../js/script.js';
  include '../includes/script.php';
  ?>
</body>

</html>