<?php
session_start();

if (isset($_SESSION['role'])) {
  if ($_SESSION['role'] === 'admin') {
    header("Location: admin/dashboard.php");
    exit;
  } else {
    header("Location: user/index.php");
    exit;
  }
}

include 'database/db.php';

// Tentukan jumlah produk per halaman
$limit = 4;
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
<html lang="id">

<head>
  <?php
  $title = 'SeduhKopi';
  $link = 'assets/img/favicon.ico';
  $css = 'css/style.css';
  $bootstrap = 'bootstrap/css/bootstrap.min.css';
  include 'includes/style.php';
  ?>
</head>

<body>

  <?php
  $link = 'assets/logo.png';
  $navlink = [
    'index.php' => 'Home',
    'product.php' => 'Products',
  ];
  $droplink = [
    'about_me.php' => 'About Me',
    'contact.php' => 'Contact',
  ];
  include 'includes/components/navbar.php';
  ?>

  <!-- Hero Section -->
  <div id="carouselExampleSlidesOnly" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner position-relative">

      <!-- Konten Teks -->
      <div class="carousel-caption-custom">
        <span class="bagde bg-light text-dark fw-bold fs-6 px-4 py-2 mb-2 rounded-pill">Welcome to</span> 
        <h1 class="fw-bold mt-3">Website UMKM SeduhKopi</h1>
        <p class="lead fs-6">Kesempurnaan Rasa Dimulai dari Proses yang Sempurna, <br>Nikmatilah kopi terbaik dari UMKM lokal.</p>
        <a href="#" class="btn btn-outline-light mt-2">Belanja Sekarang</a>
      </div>

      <!-- Gambar -->
      <div class="carousel-item active">
        <img src="assets/img/gambar1.jpg" class="d-block w-100" alt="Gambar"
          style="height: 100vh; object-fit: cover;">
      </div>

    </div>
  </div>


  <!-- Produk Section -->
  <section class="produk py-5">
    <div class="container">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="text-center fw-semibold">Trending Products</h2>
        <a href="login.php" class="d-flex align-items-center gap-2">See everything<i class="bi bi-arrow-right"></i></a>
      </div>
      <div class="row">
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
          <div class="col-md-3 col-6">
            <div class="card border-0">
              <img src="<?= $row['image'] ?>" class="card-img-top" alt="<?= $row['name'] ?>">
              <div class="card-body">
                <h1 class="text-center text-dark fs-6"><?= $row['name'] ?></h1>
                <p class="text-center"><span class=" badge bg-black rounded-pill"><?= $row['category'] ?></span></p>
                <p class="card-text text-center">Rp.<?= number_format($row['price'], 0, ',', '.') ?></p>
              </div>
            </div>
          </div>
        <?php }; ?>
      </div>
    </div>
  </section>

  <?php
  $link = "assets/logo.png";
  $footlink = [
    'index.php' => 'Home',
    'about_me.php' => 'About Me',
    'contact.php' => 'Contact',
  ];
  include 'includes/footer.php'
  ?>

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
  $bootstrap = 'bootstrap/js/bootstrap.bundle.min.js';
  include 'includes/script.php'
  ?>
</body>

</html>