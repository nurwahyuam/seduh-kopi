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
    'about_me.php' => 'About Me',
    'contact.php' => 'Contact',
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
            <img src="../images/carousel/<?= $item['image_url'] ?>" class="d-block w-100" style="object-fit: cover; height: 400px;" alt="<?= $item['title'] ?>">
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
              <img src="../images/product/<?= $row['image'] ?>" class="card-img-top" alt="<?= $row['name'] ?>">
              <div class="card-body">
                <h5 class="fw-semibold" style="font-size: 14px;"><?= $row['name'] ?></h5>
                <p><span class=" badge bg-secondary rounded-pill"><?= $row['category'] ?></span>
                  <?php if ($row['active'] == 1): ?>
                    <span class=" badge bg-success rounded-pill">Active</span>
                  <?php else : ?>
                    <span class=" badge bg-danger rounded-pill">Not Active</span>
                  <?php endif; ?>
                </p>
                <p class="fw-medium" style="font-size: 14px;">Rp.<?= number_format($row['price'], 2, ',', '.') ?></p>
                <a href="detail_product.php?id=<?= $row['id'] ?>" class="btn btn-dark btn-sm w-100 d-flex align-items-center justify-content-center gap-2"><i class="bi bi-eye"></i> Detail Product</a>
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