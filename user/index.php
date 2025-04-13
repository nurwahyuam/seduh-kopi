<?php
session_start();

if (!isset($_SESSION['role'])) {
  header("Location: ../login.php");
  exit;
} else if ($_SESSION['role'] === 'admin') {
  header("Location: ../admin/dashboard.php");
  exit;
}

include '../database/db.php';

// Ambil data carousel
$carousel_stmt = $conn->prepare("SELECT * FROM carousel");
$carousel_stmt->execute();
$carousel_result = $carousel_stmt->get_result();
$carousel = $carousel_result->fetch_all(MYSQLI_ASSOC);

// Pagination produk
$limit = 16;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Total produk
$total_result = $conn->query("SELECT COUNT(*) AS total FROM products");
$total_row = $total_result->fetch_assoc();
$total_products = $total_row['total'];
$total_pages = ceil($total_products / $limit);

// Ambil data produk dengan limit dan offset
$product_sql = "SELECT * FROM products LIMIT ?, ?";
$product_stmt = $conn->prepare($product_sql);
$product_stmt->bind_param("ii", $offset, $limit);
$product_stmt->execute();
$product_result = $product_stmt->get_result();
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

<body class="b-primary">

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

  <!-- Hero Section (Carousel) -->
  <div class="container pt-4">
    <div id="customCarousel" class="carousel slide" data-bs-ride="carousel">
      <!-- Indicators -->
      <div class="carousel-indicators">
        <?php foreach ($carousel as $index => $item): ?>
          <button type="button" data-bs-target="#customCarousel" data-bs-slide-to="<?= $index ?>" class="<?= $index === 0 ? 'active' : '' ?>" aria-label="Slide <?= $index + 1 ?>"></button>
        <?php endforeach; ?>
      </div>

      <!-- Slides -->
      <div class="carousel-inner rounded-4 overflow-hidden">
        <?php foreach ($carousel as $index => $item): ?>
          <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
            <img src="../<?= $item['image_url'] ?>" class="d-block w-100" style="object-fit: cover; height: 400px;" alt="<?= $item['title'] ?>">
            <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded p-3">
              <h5><?= $item['title'] ?></h5>
              <p><?= $item['description'] ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Controls -->
      <button class="carousel-control-prev" type="button" data-bs-target="#customCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Sebelumnya</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#customCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Berikutnya</span>
      </button>
    </div>
  </div>

  <!-- Produk Section -->
  <section class="produk py-4">
    <div class="container">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="text-center fw-semibold">All Products</h2>
        <a href="product.php" class="d-flex align-items-center gap-2">Shopping cart<i class="bi bi-arrow-right"></i></a>
      </div>
      <div class="row">
        <?php while ($row = $product_result->fetch_assoc()) { ?>
          <div class="col-md-3 col-6 mb-4">
            <div class="card h-100 border-0 shadow-sm">
              <img src="../<?= $row['image'] ?>" class="card-img-top" alt="<?= $row['name'] ?>">
              <div class="card-body">
                <h5 class="card-title"><?= $row['name'] ?></h5>
                <p class="text-muted mb-2"><?= $row['category'] ?></p>
                <p class="fw-bold">Rp.<?= number_format($row['price'], 0, ',', '.') ?></p>
              </div>
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

  <?php
  $bootstrap = '../bootstrap/js/bootstrap.bundle.min.js';
  $js = '../js/script.js';
  include '../includes/script.php';
  ?>
</body>

</html>