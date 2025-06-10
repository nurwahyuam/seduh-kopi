<?php
// Memulai session
session_start();

// Mengambil nama file saat ini
$current_page = basename($_SERVER['PHP_SELF']);

// Pengecekan role user
if (!isset($_SESSION['role'])) {
  // Jika tidak ada session role, redirect ke halaman login
  header("Location: ../login.php");
  exit;
} else if (isset($_SESSION['role']) && $_SESSION['role'] === 'user') {
  // Jika role adalah user, redirect ke halaman user
  header("Location: ../user/index.php");
  exit;
}

// Menghubungkan ke database
include '../database/db.php';

// Konfigurasi pagination
$limit = 5; // Jumlah data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Halaman saat ini
$offset = ($page - 1) * $limit; // Hitung offset untuk query

// Pencarian data
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_param = "%{$search}%";

// Query untuk menghitung total data (dengan/s tanpa pencarian)
if ($search) {
  $count_sql = "SELECT COUNT(*) AS total FROM carousel WHERE title LIKE ?";
  $stmt_count = $conn->prepare($count_sql);
  $stmt_count->bind_param("s", $search_param);
  $stmt_count->execute();
  $count_result = $stmt_count->get_result();
} else {
  $count_result = $conn->query("SELECT COUNT(*) AS total FROM carousel");
}

// Hitung total halaman
$total_row = $count_result->fetch_assoc();
$total_users = $total_row['total'];
$total_pages = ceil($total_users / $limit);

// Query untuk mengambil data carousel (dengan/s tanpa pencarian)
if ($search) {
  $sql = "SELECT * FROM carousel WHERE title LIKE ? LIMIT ?, ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sii", $search_param, $offset, $limit);
} else {
  $sql = "SELECT * FROM carousel LIMIT ?, ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $offset, $limit);
}

$stmt->execute();
$result = $stmt->get_result();

// Query untuk menghitung notifikasi yang belum dibaca
$userId = $_SESSION['id'];
$query = mysqli_query($conn, "SELECT COUNT(*) AS unread FROM notifications WHERE user_id = $userId AND is_read = 0");
$data = mysqli_fetch_assoc($query);
$unreadCount = $data['unread'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <?php
  // Include bagian head
  $title = 'Carousel';
  $link = '../assets/img/favicon.ico';
  $css = '../css/style.css';
  $bootstrap = '../bootstrap/css/bootstrap.min.css';
  include '../includes/style.php';
  ?>
</head>

<body>
  <div class="d-flex vh-100">
    <?php
    // Navigasi sidebar
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
    include '../includes/components/navbar_sider.php'
    ?>

    <!-- KONTEN UTAMA -->
    <div id="box" class="w-100 bg-light py-3 px-4">
      <?php include '../includes/components/nav_side.php' ?>

      <div class="border shadow rounded-2 p-4">
        <!-- Header dan Search -->
        <div class="d-flex align-items-center justify-content-between">
          <h1 class="fs-4 fw-bold">Daftar Carousels</h1>
          <div class="d-flex align-items-center justify-content-end gap-2">
            <form method="GET" action="" class="d-flex align-items-center gap-2">
              <input type="text" name="search" class="form-control form-control-sm" placeholder="Search ..." value="<?= htmlspecialchars($search) ?>">
              <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
              <?php if ($search): ?>
                <a href="carousel.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-clockwise"></i></a>
              <?php endif; ?>
            </form>
            <a onclick=showCreateCarouselModal() class="btn btn-success btn-sm text-white"><i class="bi bi-plus-circle"></i></a>
          </div>
        </div>
        <hr>
        
        <!-- Tabel Data Carousel -->
        <table class="mt-2 table table-bordered ">
          <thead>
            <tr>
              <th class="text-center" style=" vertical-align: middle;">No</th>
              <th class="text-center" style=" vertical-align: middle;">Carousel Image</th>
              <th class="text-center" style=" vertical-align: middle;">Title</th>
              <th class="text-center" style=" vertical-align: middle;">Description</th>
              <th class="text-center" style=" vertical-align: middle;">Status</th>
              <th class="text-center" style=" vertical-align: middle;">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result->num_rows > 0): ?>
              <?php $i = 1;
              while ($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                  <td class="text-center" style=" vertical-align: middle;"><?= $i++; ?></td>
                  <td class="text-center">
                    <img width="58px" height="58px" src="../images/carousel/<?= $row['image_url'] ?>" alt="Image">
                  </td>
                  <td class="text-center text-capitalize" style="vertical-align: middle;"><?= substr($row['title'], 0, 20) ?>...</td>
                  <td class="text-center" style=" vertical-align: middle;"><?= substr($row['description'], 0, 20) ?>...</td>
                  <td class="text-center" style=" vertical-align: middle;">
                    <?php if ($row['is_active'] === 1): ?>
                      <span class="badge text-bg-success rounded-pill">Active</span>
                    <?php else: ?>
                      <span class="badge text-bg-danger rounded-pill">Not Active</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-center" style="vertical-align: middle;">
                    <a onclick='showEditCarouselModal(<?= json_encode($row, JSON_HEX_TAG | JSON_HEX_APOS); ?>)' class="btn btn-warning btn-sm text-white"><i class="bi bi-pencil-fill"></i></a>
                    <a href="../includes/delete_carousel.php?id=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus?')" class="btn btn-danger btn-sm "><i class="bi bi-trash-fill"></i></a>
                  </td>
                </tr>
              <?php } ?>
            <?php else: ?>
              <tr>
                <td class="text-center py-4" colspan="6">
                  <p class="m-0">Tidak ada produk yang tersedia.</p>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
        <?php include '../includes/components/pagination.php' ?>
      </div>
    </div>
  </div>

  <?php include '../includes/components/toast.php' ?>

  <!-- Modal Edit Carousel -->
  <div class="modal fade" id="modalEditCarousel" tabindex="-1" aria-labelledby="modalEditCarouselLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <form id="editCarouselForm" method="POST" action="../includes/update_carousel.php" enctype="multipart/form-data">
        <div class="modal-content">
          <div class="modal-header bg-dark text-white">
            <h5 class="modal-title" id="modalEditCarouselLabel">Edit Carousel</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="id" id="editId" />

            <div class="mb-3">
              <label for="editTitle" class="form-label">Title</label>
              <input type="text" class="form-control border-dark" id="editTitle" name="title" required>
            </div>

            <div class="mb-3">
              <label for="editDescription" class="form-label">Description</label>
              <textarea class="form-control border-dark" id="editDescription" name="description" rows="3" required></textarea>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="editImage" class="form-label">Photo Carousel</label>
                  <input type="file" class="form-control border-dark" id="editImage" name="image_url" accept="image/*">
                  <div class="form-text">Note: Leave blank if not updating the photo.</div>
                </div>

                <div class="mb-3">
                  <label for="editStatus" class="form-label">Status</label>
                  <select class="form-select border-dark" id="editStatus" name="is_active">
                    <option value="1">Active</option>
                    <option value="0">Not Active</option>
                  </select>
                </div>
              </div>

              <div class="col-md-6 d-flex align-items-center justify-content-center">
                <img id="editImageCarousel" src="" class="img-fluid border rounded shadow-sm" style="max-height: 250px;">
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-warning">Save</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal Create Carousel -->
  <div class="modal fade" id="modalCreateCarousel" tabindex="-1" aria-labelledby="modalCreateCarouselLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <form id="createCarouselForm" method="POST" action="../includes/create_carousel.php" enctype="multipart/form-data">
        <div class="modal-content">
          <div class="modal-header bg-dark text-white">
            <h5 class="modal-title" id="modalCreateCarouselLabel">Create Carousel</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">

            <div class="mb-3">
              <label for="createTitle" class="form-label">Title</label>
              <input type="text" class="form-control border-dark" id="createTitle" name="title" required>
            </div>

            <div class="mb-3">
              <label for="createDescription" class="form-label">Description</label>
              <textarea class="form-control border-dark" id="createDescription" name="description" rows="3" required></textarea>
            </div>

            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="createImage" class="form-label">Photo Carousel</label>
                  <input type="file" class="form-control border-dark" id="createImage" name="image_url" accept="image/*" required>
                </div>

                <div class="mb-3">
                  <label for="createStatus" class="form-label">Status</label>
                  <select class="form-select border-dark" id="createStatus" name="is_active">
                    <option value="1" selected>Active</option>
                    <option value="0">Not Active</option>
                  </select>
                </div>
              </div>

              <div class="col-md-6 d-flex align-items-center justify-content-center">
                <img id="createImagePreview" src="#" alt="Preview" class="img-fluid border rounded shadow-sm d-none" style="max-height: 250px;">
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="submit" class="btn btn-success">Create</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Fungsi untuk menandai notifikasi sebagai sudah dibaca
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
    
    // Fungsi untuk menampilkan toast notifikasi
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

    // Fungsi untuk mengatur sidebar
    const sidebar = document.getElementById("sidebar");
    const sidebarBig = document.getElementById("sidebarBig");
    const sidebarSmall = document.getElementById("sidebarSmall");

    function closeBar() {
      sidebar.classList.remove("w-25");
      sidebar.style.width = "8vh";
      sidebarBig.classList.add("d-none");
      sidebarSmall.classList.remove("d-none");
      box.classList.remove("w-75");
      box.style.width = "calc(100% - 65px)";
    }

    function openBar() {
      sidebar.classList.add("w-25");
      sidebar.style.width = "";
      sidebarBig.classList.remove("d-none");
      sidebarSmall.classList.add("d-none");
      box.style.width = "";
      box.classList.add("w-75");
    }

    // Fungsi untuk menampilkan modal edit carousel
    function showEditCarouselModal(car) {
      document.getElementById("editId").value = car.id;
      document.getElementById("editTitle").value = car.title;
      document.getElementById("editDescription").value = car.description;
      document.getElementById("editStatus").value = car.is_active;

      const linkFile = `../images/carousel/${car.image_url}`;
      document.getElementById("editImageCarousel").src = linkFile;

      const modal = new bootstrap.Modal(document.getElementById("modalEditCarousel"));
      modal.show();
    }

    // Fungsi untuk menampilkan modal create carousel
    function showCreateCarouselModal() {
      document.getElementById('createCarouselForm').reset();

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

      // Tampilkan modal
      const modal = new bootstrap.Modal(document.getElementById("modalCreateCarousel"));
      modal.show();
    }
  </script>

  <?php
  // Include script
  $bootstrap = '../bootstrap/js/bootstrap.bundle.min.js';
  $js = '';
  include '../includes/script.php'
  ?>
</body>

</html>