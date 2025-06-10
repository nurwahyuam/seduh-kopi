<?php
session_start(); // Memulai session

$current_page = basename($_SERVER['PHP_SELF']); // Mendapatkan nama file halaman saat ini

// Cek apakah user sudah login dan memiliki role yang sesuai
if (!isset($_SESSION['role'])) {
  header("Location: ../login.php");
  exit;
} else if (isset($_SESSION['role']) && $_SESSION['role'] === 'user') {
  header("Location: ../user/index.php");
  exit;
}

include '../database/db.php'; // Menghubungkan ke database

// Pagination setup
$limit = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Ambil parameter pencarian (search)
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_param = "%{$search}%";

// Hitung total data (untuk pagination) berdasarkan search atau tidak
if (!empty($search)) {
  $count_sql = "SELECT COUNT(*) AS total FROM products WHERE name LIKE ? OR description LIKE ?";
  $stmt_count = $conn->prepare($count_sql);
  $stmt_count->bind_param("ss", $search_param, $search_param);
  $stmt_count->execute();
  $count_result = $stmt_count->get_result();
} else {
  $count_result = $conn->query("SELECT COUNT(*) AS total FROM products");
}

$total_row = $count_result->fetch_assoc();
$total_products = $total_row['total'];
$total_pages = ceil($total_products / $limit);

// Query ambil data produk berdasarkan search atau tidak
if (!empty($search)) {
  $sql = "SELECT * FROM products WHERE name LIKE ? OR description LIKE ? LIMIT ?, ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ssii", $search_param, $search_param, $offset, $limit);
} else {
  $sql = "SELECT * FROM products LIMIT ?, ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $offset, $limit);
}

$stmt->execute();
$result = $stmt->get_result();

// Ambil jumlah notifikasi yang belum dibaca untuk user saat ini
$userId = $_SESSION['id'];
$query = mysqli_query($conn, "SELECT COUNT(*) AS unread FROM notifications WHERE user_id = $userId AND is_read = 0");
$data = mysqli_fetch_assoc($query);
$unreadCount = $data['unread'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <?php
  $title = 'Products'; // Judul halaman
  $link = '../assets/img/favicon.ico'; // Favicon
  $css = '../css/style.css'; // Path ke file CSS
  $bootstrap = '../bootstrap/css/bootstrap.min.css'; // Path ke Bootstrap
  include '../includes/style.php'; // Include file style tambahan
  ?>
</head>

<body>
  <div class="d-flex vh-100">
    <?php
    // Array untuk navigasi sidebar
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
      [
        'file' => 'order.php',
        'label' => 'Orders',
        'icon' => 'bi-bag-dash-fill'
      ],
      [
        'file' => 'transaction.php',
        'label' => 'Transactions',
        'icon' => 'bi-wallet-fill'
      ],
      [
        'file' => 'carousel.php',
        'label' => 'Carousels',
        'icon' => 'bi-image-fill'
      ],
      [
        'file' => 'payment_method.php',
        'label' => 'Payment Methods',
        'icon' => 'bi-wallet-fill'
      ],
    ];
    include '../includes/components/navbar_sider.php' // Memuat komponen sidebar
    ?>

    <div id="box" class="w-100 bg-light py-3 px-4">
      <?php include '../includes/components/nav_side.php' // Navbar atas ?>

      <div class="border shadow rounded-2 p-4">
        <div class="d-flex align-items-center justify-content-between">
          <h1 class="fs-4 fw-bold">Daftar Products</h1>
          <div class="d-flex align-items-center justify-content-end gap-2">
            <form method="GET" action="" class="d-flex align-items-center gap-2">
              <!-- Form pencarian -->
              <input type="text" name="search" class="form-control form-control-sm" placeholder="Search ..." value="<?= htmlspecialchars($search) ?>">
              <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
              <?php if ($search): ?>
                <!-- Tombol reset pencarian -->
                <a href="product.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-clockwise"></i></a>
              <?php endif; ?>
            </form>
            <!-- Tombol tambah produk (akan memunculkan modal) -->
            <a onclick=showCreateProductsModal() class="btn btn-success btn-sm text-white"><i class="bi bi-plus-circle"></i></a>
          </div>
        </div>
        <hr>
        <!-- Tabel daftar produk -->
        <table class="mt-2 table table-bordered">
          <thead>
            <tr>
              <th class="text-center" style=" vertical-align: middle;">No</th>
              <th class="text-center" style=" vertical-align: middle;">Name</th>
              <th class="text-center" style=" vertical-align: middle;">Description</th>
              <th class="text-center" style=" vertical-align: middle;">Prize</th>
              <th class="text-center" style=" vertical-align: middle;">Category</th>
              <th class="text-center" style=" vertical-align: middle;">Status</th>
              <th class="text-center" style=" vertical-align: middle;">Photo Product</th>
              <th class="text-center" style=" vertical-align: middle;">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result->num_rows > 0): ?>
              <?php $i = 1;
              while ($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                  <!-- Menampilkan data produk -->
                  <td class="text-center" style=" vertical-align: middle;"><?= $i++; ?></td>
                  <td class="text-center" style=" vertical-align: middle;"><?= $row['name'] ?></td>
                  <td class="text-center" style=" vertical-align: middle;"><?= substr($row['description'], 0, 20) ?>...</td>
                  <td class="text-center" style=" vertical-align: middle;">Rp.<?= number_format($row['price'], 0, ',', '.') ?></td>
                  <td class="text-center" style=" vertical-align: middle;"><?= $row['category'] ?></td>
                  <td class="text-center" style=" vertical-align: middle;">
                    <!-- Badge status aktif/tidak -->
                    <?php if ($row['active'] == 1): ?>
                      <span class="badge text-bg-success text-center">Active</span>
                    <?php else : ?>
                      <span class="badge text-bg-danger text-center">Not Active</span>
                    <?php endif; ?>
                  </td>
                  <!-- Gambar produk -->
                  <td class="text-center"><img width="58px" height="58px" src="../images/product/<?= $row['image'] ?>" alt="Image"></td>
                  <!-- Tombol edit dan hapus -->
                  <td class="text-center" style=" vertical-align: middle;">
                    <a onclick='showEditProductsModal(<?= json_encode($row, JSON_HEX_TAG | JSON_HEX_APOS); ?>)' class="btn btn-warning btn-sm text-white"><i class="bi bi-pencil-fill"></i></a>
                    <a href="../includes/delete_product.php?id=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus?')" class="btn btn-danger btn-sm "><i class="bi bi-trash-fill"></i></a>
                  </td>
                </tr>
              <?php } ?>
            <?php else: ?>
              <!-- Jika tidak ada produk -->
              <tr>
                <td class="text-center py-4" colspan="8">
                  <p class="m-0">Tidak ada produk yang tersedia.</p>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
        <?php include '../includes/components/pagination.php' // Komponen pagination ?>
      </div>
    </div>
  </div>
  
  <?php include '../includes/components/toast.php' ?> <!-- Memuat komponen toast (notifikasi) -->

  <!-- Modal Edit Product -->
  <div class="modal fade" id="modalEditProduct" tabindex="-1" aria-labelledby="modalEditProductLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <!-- Form untuk update product -->
      <form id="editProductForm" method="POST" action="../includes/update_product.php" enctype="multipart/form-data">
        <div class="modal-content">
          <div class="modal-header bg-dark text-white">
            <h5 class="modal-title" id="modalEditProductLabel">Edit Product</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="id" id="editProductId" />
            <div class="row">
              <!-- Input nama produk -->
              <div class="col-6 mb-3">
                <label for="editName" class="form-label">Product Name</label>
                <input type="text" class="form-control border-dark" id="editName" name="name" required>
              </div>
              <!-- Input kategori produk -->
              <div class="col-6 mb-3">
                <label for="editCategory" class="form-label">Category</label>
                <input type="text" class="form-control border-dark" id="editCategory" name="category" required>
              </div>
            </div>
            <!-- Input deskripsi produk -->
            <div class="mb-3">
              <label for="editDescription" class="form-label">Description</label>
              <textarea class="form-control border-dark" id="editDescription" name="description" rows="3" required></textarea>
            </div>
            <div class="row">
              <div class="col-md-6">
                <!-- Input harga produk -->
                <div class="mb-3">
                  <label for="editPrice" class="form-label">Price</label>
                  <input type="number" class="form-control border-dark" id="editPrice" name="price" required>
                </div>
                <!-- Input upload foto produk -->
                <div class="mb-3">
                  <label for="editImage" class="form-label">Photo Product</label>
                  <input type="file" class="form-control border-dark" id="editImage" name="image" accept="image/*">
                  <div class="form-text">Note: Leave blank if not updating the photo.</div>
                </div>
                <!-- Pilih status aktif / tidak -->
                <div class="mb-3">
                  <label for="editProductStatus" class="form-label">Status</label>
                  <select class="form-select border-dark" id="editProductStatus" name="active">
                    <option value="1">Active</option>
                    <option value="0">Not Active</option>
                  </select>
                </div>
              </div>
              <!-- Preview gambar produk -->
              <div class="col-md-6 d-flex align-items-center justify-content-center">
                <img id="editImageProduct" src="" class="img-fluid border rounded shadow-sm" style="max-height: 250px;">
              </div>
            </div>
          </div>
          <!-- Tombol aksi simpan dan tutup -->
          <div class="modal-footer">
            <button type="submit" class="btn btn-warning">Save</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal Create Product -->
  <div class="modal fade" id="modalCreateProduct" tabindex="-1" aria-labelledby="modalCreateProductLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <!-- Form untuk membuat produk baru -->
      <form id="createProductForm" method="POST" action="../includes/create_product.php" enctype="multipart/form-data">
        <div class="modal-content">
          <div class="modal-header bg-dark text-white">
            <h5 class="modal-title" id="modalCreateProductLabel">Create Product</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="row">
              <!-- Input nama produk -->
              <div class="col-6 mb-3">
                <label for="createName" class="form-label">Product Name</label>
                <input type="text" class="form-control border-dark" id="createName" name="name" required>
              </div>
              <!-- Input kategori produk -->
              <div class="col-6 mb-3">
                <label for="createCategory" class="form-label">Category</label>
                <input type="text" class="form-control border-dark" id="createCategory" name="category" required>
              </div>
            </div>
            <!-- Input deskripsi produk -->
            <div class="mb-3">
              <label for="createDescription" class="form-label">Description</label>
              <textarea class="form-control border-dark" id="createDescription" name="description" rows="3" required></textarea>
            </div>
            <div class="row">
              <div class="col-md-6">
                <!-- Input harga produk -->
                <div class="mb-3">
                  <label for="createPrice" class="form-label">Price</label>
                  <input type="number" class="form-control border-dark" id="createPrice" name="price" required>
                </div>
                <!-- Upload gambar produk -->
                <div class="mb-3">
                  <label for="createImage" class="form-label">Photo Product</label>
                  <input type="file" class="form-control border-dark" id="createImage" name="image" accept="image/*" required>
                </div>
                <!-- Pilih status produk -->
                <div class="mb-3">
                  <label for="createStatus" class="form-label">Status</label>
                  <select class="form-select border-dark" id="createStatus" name="active">
                    <option value="1" selected>Active</option>
                    <option value="0">Not Active</option>
                  </select>
                </div>
              </div>
              <!-- Preview gambar yang dipilih -->
              <div class="col-md-6 d-flex align-items-center justify-content-center">
                <img id="createImagePreview" src="#" alt="Preview" class="img-fluid border rounded shadow-sm d-none" style="max-height: 250px;">
              </div>
            </div>
          </div>
          <!-- Tombol aksi simpan dan tutup -->
          <div class="modal-footer">
            <button type="submit" class="btn btn-success">Create</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Script JavaScript -->
  <script>
    // Menandai notifikasi sudah dibaca ketika tombol diklik
    document.getElementById('notifButton').addEventListener('click', function() {
      fetch('../includes/mark_read.php')
        .then(response => response.json())
        .then(data => {
          if (data.status === "success") {
            let badge = document.querySelector('.badge.bg-danger');
            if (badge) badge.remove();
          } else {
            console.error(data.message);
          }
        })
        .catch(error => console.error("Gagal menghubungi server:", error));
    });

    // Menampilkan toast message jika ada pesan dari sessionStorage
    document.addEventListener("DOMContentLoaded", function() {
      const toastMsg = sessionStorage.getItem("toastMessage");
      if (toastMsg) {
        document.getElementById("toastMessage").innerText = toastMsg;
        new bootstrap.Toast(document.getElementById("liveToast")).show();
        sessionStorage.removeItem("toastMessage");
      }

      const toastDeleteMsg = sessionStorage.getItem("toastMessageDelete");
      if (toastDeleteMsg) {
        document.getElementById("toastMessageDelete").innerText = toastDeleteMsg;
        new bootstrap.Toast(document.getElementById("liveToastDelete")).show();
        sessionStorage.removeItem("toastMessageDelete");
      }
    });

    // Fungsi untuk menyembunyikan sidebar
    function closeBar() {
      sidebar.classList.remove("w-25");
      sidebar.style.width = "8vh";
      sidebarBig.classList.add("d-none");
      sidebarSmall.classList.remove("d-none");
      box.classList.remove("w-75");
      box.style.width = "calc(100% - 65px)";
    }

    // Fungsi untuk menampilkan sidebar
    function openBar() {
      sidebar.classList.add("w-25");
      sidebar.style.width = "";
      sidebarBig.classList.remove("d-none");
      sidebarSmall.classList.add("d-none");
      box.style.width = "";
      box.classList.add("w-75");
    }

    // Fungsi menampilkan modal edit produk dengan data terisi otomatis
    function showEditProductsModal(product) {
      document.getElementById("editProductId").value = product.id;
      document.getElementById("editName").value = product.name;
      document.getElementById("editDescription").value = product.description;
      document.getElementById("editPrice").value = product.price;
      document.getElementById("editCategory").value = product.category;
      document.getElementById("editProductStatus").value = product.active;

      const linkFile = `../images/product/${product.image}`;
      document.getElementById("editImageProduct").src = linkFile;

      const modal = new bootstrap.Modal(document.getElementById("modalEditProduct"));
      modal.show();
    }

    // Fungsi menampilkan modal tambah produk dan reset isian
    function showCreateProductsModal() {
      document.getElementById('createProductForm').reset();

      const preview = document.getElementById('createImagePreview');
      preview.src = '#';
      preview.classList.add('d-none');

      const imageInput = document.getElementById('createImage');
      if (!imageInput.dataset.listenerAdded) {
        imageInput.addEventListener('change', function(event) {
          const [file] = event.target.files;
          if (file) {
            preview.src = URL.createObjectURL(file);
            preview.classList.remove('d-none');
          } else {
            preview.src = '#';
            preview.classList.add('d-none');
          }
        });
        imageInput.dataset.listenerAdded = "true";
      }

      const modal = new bootstrap.Modal(document.getElementById("modalCreateProduct"));
      modal.show();
    }
  </script>

  <?php
  // Menyertakan file JS Bootstrap dan skrip tambahan
  $bootstrap = '../bootstrap/js/bootstrap.bundle.min.js';
  $js = '';
  include '../includes/script.php'
  ?>
</body>

</html>
