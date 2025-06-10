<?php
// Memulai session untuk menyimpan data pengguna
session_start();

// Cek jika pengguna sudah login (role terdefinisi)
if (isset($_SESSION['role'])) {
  // Redirect admin ke dashboard admin
  if ($_SESSION['role'] === 'admin') {
    header("Location: admin/dashboard.php");
    exit;
  } else {
    // Redirect user biasa ke halaman user
    header("Location: user/index.php");
    exit;
  }
}

// Menghubungkan ke database
include 'database/db.php';

// PAGINATION SETUP
// Jumlah produk per halaman
$limit = 4;
// Ambil nomor halaman dari URL, default 1 jika tidak ada
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
// Hitung offset untuk query database
$offset = ($page - 1) * $limit;

// Hitung total produk untuk pagination
$total_result = $conn->query("SELECT COUNT(*) AS total FROM products");
$total_row = $total_result->fetch_assoc();
$total_products = $total_row['total'];
// Hitung total halaman yang dibutuhkan
$total_pages = ceil($total_products / $limit);

// Query produk dengan pagination menggunakan prepared statement
$sql = "SELECT * FROM products LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $offset, $limit); // Bind parameter offset dan limit
$stmt->execute();
$result = $stmt->get_result(); // Hasil query
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <!-- HEAD: Pengaturan halaman -->
  <?php
  $title = 'SeduhKopi'; // Judul halaman
  $link = 'assets/img/favicon.ico'; // Icon browser
  $css = 'css/style.css'; // CSS custom
  $bootstrap = 'bootstrap/css/bootstrap.min.css'; // CSS Bootstrap
  include 'includes/style.php'; // Include file style
  ?>
</head>

<body>
  <!-- NAVBAR -->
  <?php
  $link = 'assets/logo.png'; // Logo navbar
  // Link menu navbar
  $navlink = [
    'index.php' => 'Home',
    'login.php' => 'Products',
  ];
  // Link dropdown menu
  $droplink = [
    'about_me.php' => 'About Me',
    'contact.php' => 'Contact',
  ];
  include 'includes/components/navbar.php'; // Include navbar
  ?>

  <!-- HERO SECTION -->
  <div id="carouselExampleSlidesOnly" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner position-relative">
      <!-- Gambar Hero -->
      <div class="carousel-item active">
        <img src="assets/img/gambar1.jpg" class="d-block w-100" alt="Gambar"
          style="height: 100vh; object-fit: cover; object-position: center;">
      </div>

      <!-- Teks Hero -->
      <div class="carousel-caption-custom">
        <div class="container">
          <span class="badge bg-light text-dark fw-bold fs-6 px-4 py-2 mb-2 rounded-pill">Welcome to</span>
          <h1 class="fw-bold mt-3 text-white">Website UMKM SeduhKopi</h1>
          <p class="lead fs-6 text-white">Kesempurnaan Rasa Dimulai dari Proses yang Sempurna,<br>Nikmatilah kopi terbaik dari UMKM lokal.</p>
          <a href="#" class="btn btn-outline-light mt-2">Belanja Sekarang</a>
        </div>
      </div>
    </div>
  </div>

  <!-- PRODUK SECTION -->
  <section class="py-5">
    <div class="container">
      <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="text-center fw-semibold">Trending Products</h2>
        <!-- Link ke halaman produk lengkap -->
        <a href="login.php" class="d-flex align-items-center gap-2">See everything<i class="bi bi-arrow-right"></i></a>
      </div>
      
      <!-- Daftar Produk -->
      <div class="row">
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
          <div class="col-md-3 col-6">
            <div class="card border-0">
              <!-- Gambar Produk -->
              <img src="images/product/<?= $row['image'] ?>" class="card-img-top" alt="<?= $row['name'] ?>">
              <div class="card-body">
                <!-- Nama Produk -->
                <h1 class="text-center text-dark fs-6"><?= $row['name'] ?></h1>
                <!-- Kategori Produk -->
                <p class="text-center"><span class="badge bg-black rounded-pill"><?= $row['category'] ?></span></p>
                <!-- Harga Produk -->
                <p class="card-text text-center">Rp.<?= number_format($row['price'], 0, ',', '.') ?></p>
              </div>
            </div>
          </div>
        <?php }; ?>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <?php
  $link = "assets/logo.png"; // Logo footer
  // Link menu footer
  $footlink = [
    'index.php' => 'Home',
    'about_me.php' => 'About Me',
    'contact.php' => 'Contact',
  ];
  include 'includes/footer.php' // Include footer
  ?>

  <!-- TOAST NOTIFICATION -->
  <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="liveToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body" id="toastMessage">
          <!-- Pesan notifikasi akan muncul di sini -->
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  </div>

  <!-- SCRIPTS -->
  <?php
  $bootstrap = 'bootstrap/js/bootstrap.bundle.min.js'; // JS Bootstrap
  include 'includes/script.php' // Include scripts
  ?>
</body>

</html>