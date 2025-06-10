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

// Ambil semua kategori
$category_result = $conn->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != ''");
$categories = [];
while ($cat = $category_result->fetch_assoc()) {
  $categories[] = $cat['category'];
}

// Ambil parameter filter dan pencarian
$search = isset($_GET['search']) && $_GET['search'] !== '' ? '%' . $_GET['search'] . '%' : '%';
$category = isset($_GET['category']) && $_GET['category'] !== 'All' ? $_GET['category'] : '%';
$active = $_GET['active'] ?? 'Active';
$priceOrder = $_GET['price'] ?? 'Latest';
// Bangun query
$query = "SELECT * FROM products WHERE name LIKE ? AND category LIKE ?";
$params = [$search, $category];
$types = "ss";

if ($active === "Active") {
  $query .= " AND active = 1";
} elseif ($active === "Not Active") {
  $query .= " AND active = 0";
}

// Sorting harga
switch ($priceOrder) {
  case "Low to High":
    $query .= " ORDER BY price ASC";
    break;
  case "High to Low":
    $query .= " ORDER BY price DESC";
    break;
  default:
    $query .= " ORDER BY id DESC";
    break;
}

// Eksekusi query
$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Ambil data notifikasi
$userId = $_SESSION['id'];
$query = mysqli_query($conn, "SELECT COUNT(*) AS unread FROM notifications WHERE user_id = $userId AND is_read = 0");
$data = mysqli_fetch_assoc($query);
$unreadCount = $data['unread'];
?>


<!DOCTYPE html>
<html lang="en">

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

  <main class="container pb-5">
    <div class="text-center py-5 my-3 bg-dark bg-gradient text-light rounded-4">
      <h1 class="fw-bold fs-3">E-Commerce UMKM SeduhKopi</h1>
      <p class="fs-6 text-wrap">Kopi yang Menggugah Selera, Setiap Seduhan adalah Cerita.</p>
      <div class="w-100 d-flex justify-content-center py-3">
        <form class="d-flex w-50" role="search" method="GET">
          <input name="search" class="form-control focus-ring focus-ring-light" type="search" placeholder="Search" aria-label="Search" value="<?= isset($_GET['search']) ? $_GET['search'] : '' ?>">
          <button class="d-none d-md-flex btn btn-outline-light" type="submit">Search</button>
        </form>
      </div>
    </div>

    <!-- Category -->
    <form method="GET">
      <div class="row mb-3">
        <div class="w-100 d-flex flex-wrap align-items-center justify-content-between">

          <!-- Category -->
          <div class="input-group input-group-sm" style="max-width: 200px;">
            <label class="input-group-text bg-dark text-light border-dark" for="category">Category</label>
            <select name="category" class="form-select focus-ring focus-ring-dark border-dark" id="category">
              <option value="All" <?= (!isset($_GET['category']) || $_GET['category'] == 'All') ? 'selected' : '' ?>>All</option>
              <?php foreach ($categories as $cat) : ?>
                <option value="<?= $cat ?>" <?= (isset($_GET['category']) && $_GET['category'] == $cat) ? 'selected' : '' ?>><?= $cat ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="d-flex align-items-center justify-content-center gap-2">
            <!-- Price -->
            <div class="input-group input-group-sm" style="max-width: 180px;">
              <label class="input-group-text bg-dark text-light border-dark" for="price">Price</label>
              <select name="price" class="form-select focus-ring focus-ring-dark border-dark" id="price">
                <option value="Latest" <?= (!isset($_GET['price']) || $_GET['price'] == 'Latest') ? 'selected' : '' ?>>Latest</option>
                <option value="Low to High" <?= ($_GET['price'] ?? '') == 'Low to High' ? 'selected' : '' ?>>Low to High</option>
                <option value="High to Low" <?= ($_GET['price'] ?? '') == 'High to Low' ? 'selected' : '' ?>>High to Low</option>
              </select>
            </div>

            <!-- Active -->
            <div class="input-group input-group-sm" style="max-width: 180px;">
              <label class="input-group-text bg-dark text-light border-dark" for="active">Status</label>
              <select name="active" class="form-select focus-ring focus-ring-dark border-dark" id="active">
                <option value="Active" <?= (!isset($_GET['active']) || $_GET['active'] == 'Active') ? 'selected' : '' ?>>Active</option>
                <option value="Not Active" <?= ($_GET['active'] ?? '') == 'Not Active' ? 'selected' : '' ?>>Not Active</option>
              </select>
            </div>

            <button type="submit" class="btn btn-dark btn-sm">Filter</button>
            <a href="product.php" class="btn btn-danger btn-sm">Reset</a>
          </div>
        </div>
      </div>
    </form>


    <!-- Product Cards -->
    <?php if (mysqli_num_rows($result) > 0): ?>
      <!-- Product Cards -->
      <div class="row row-cols-lg-6 row-cols-md-4 row-cols-2">
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
          <div class="col-lg-2 col-md-4 col-6 mb-3">
            <div class="card h-100 border-0 shadow-sm">
              <img src="../images/product/<?= $row['image'] ?>" class="card-img-top" alt="<?= $row['name'] ?>">
              <div class="card-body">
                <h5 class="fw-semibold" style="font-size: 14px;"><?= $row['name'] ?></h5>
                <p>
                  <span class="badge bg-secondary rounded-pill"><?= $row['category'] ?></span>
                  <?php if ($row['active'] == 1): ?>
                    <span class="badge bg-success rounded-pill">Active</span>
                  <?php else : ?>
                    <span class="badge bg-danger rounded-pill">Not Active</span>
                  <?php endif; ?>
                </p>
                <p class="fw-medium" style="font-size: 14px;">Rp.<?= number_format($row['price'], 2, ',', '.') ?></p>
                <button class="btn btn-dark btn-sm add-to-cart w-100"
                  data-id="<?= $row['id'] ?>"
                  data-name="<?= $row['name'] ?>"
                  data-price="<?= $row['price'] ?>">
                  <i class="bi bi-cart4"></i> Add to Cart
                </button>
              </div>
            </div>
          </div>
        <?php } ?>
      </div>
    <?php else: ?>
      <div class="alert alert-warning text-center mt-4" role="alert">
        Produk tidak ditemukan.
      </div>
    <?php endif; ?>

    <!-- END Product Cards -->
  </main>

  <?php include '../includes/components/offcanvas.php' ?>

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