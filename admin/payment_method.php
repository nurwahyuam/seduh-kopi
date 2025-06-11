<?php
// Mulai session
session_start();

// Ambil nama file saat ini
$current_page = basename($_SERVER['PHP_SELF']);

// Cek jika role tidak terdaftar, redirect ke halaman login
if (!isset($_SESSION['role'])) {
  header("Location: ../login.php");
  exit;
} 
// Jika role adalah user, redirect ke halaman user
elseif ($_SESSION['role'] === 'user') {
  header("Location: ../user/index.php");
  exit;
}

// Include koneksi database
include '../database/db.php';

// Konfigurasi pagination
$limit = 4; // Jumlah data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Halaman saat ini
$offset = ($page - 1) * $limit; // Hitung offset

// Ambil parameter search jika ada
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_param = "%{$search}%";

// Hitung total data payment method (dengan atau tanpa search)
if ($search) {
  // Jika ada search, hitung total dengan filter
  $count_sql = "SELECT COUNT(*) AS total FROM payment_methods WHERE method_name LIKE ? OR acc_number LIKE ?";
  $stmt_count = $conn->prepare($count_sql);
  $stmt_count->bind_param("ss", $search_param, $search_param);
  $stmt_count->execute();
  $count_result = $stmt_count->get_result();
} else {
  // Jika tidak ada search, hitung semua payment method
  $count_result = $conn->query("SELECT COUNT(*) AS total FROM payment_methods");
}

// Ambil total data dan hitung total halaman
$total_row = $count_result->fetch_assoc();
$total_users = $total_row['total'];
$total_pages = ceil($total_users / $limit);

// Ambil data payment method dengan pagination (dengan atau tanpa search)
if ($search) {
  // Jika ada search, ambil data dengan filter
  $sql = "SELECT * FROM payment_methods WHERE method_name LIKE ? OR acc_number LIKE ? LIMIT ?, ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ssii", $search_param, $search_param, $offset, $limit);
} else {
  // Jika tidak ada search, ambil semua data
  $sql = "SELECT * FROM payment_methods LIMIT ?, ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $offset, $limit);
}

// Eksekusi query
$stmt->execute();
$result = $stmt->get_result();

// Hitung notifikasi yang belum dibaca
$userId = $_SESSION['id'];
$query = mysqli_query($conn, "SELECT COUNT(*) AS unread FROM notifications WHERE user_id = $userId AND is_read = 0");
$data = mysqli_fetch_assoc($query);
$unreadCount = $data['unread'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <?php
  // Include meta dan CSS
  $title = 'Payment Methods';
  $link = '../assets/img/favicon.ico';
  $css = '../css/style.css';
  $bootstrap = '../bootstrap/css/bootstrap.min.css';
  include '../includes/style.php';
  ?>
</head>

<body>
  <div class="d-flex vh-100">
    <?php
    // Data untuk navigasi sidebar
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
        'icon' => 'bi-cash-coin'
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
        <!-- HEADER DAN SEARCH -->
        <div class="d-flex align-items-center justify-content-between">
          <h1 class="fs-4 fw-bold">Daftar Payment Methods</h1>
          <div class="d-flex align-items-center justify-content-end gap-2">
            <!-- FORM SEARCH -->
            <form method="GET" action="" class="d-flex align-items-center gap-2">
              <input type="text" name="search" class="form-control form-control-sm" placeholder="Search ..." value="<?= htmlspecialchars($search) ?>">
              <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
              <?php if ($search): ?>
                <a href="payment_method.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-clockwise"></i></a>
              <?php endif; ?>
            </form>
            <!-- TOMBOL TAMBAH DATA -->
            <a onclick=showCreatePaymentMethodsModal() class="btn btn-success btn-sm text-white"><i class="bi bi-plus-circle"></i></a>
          </div>
        </div>
        <hr>
        
        <!-- TABEL DATA -->
        <table class="mt-2 table table-bordered ">
          <thead>
            <tr>
              <th class="text-center" style=" vertical-align: middle;">No</th>
              <th class="text-center" style=" vertical-align: middle;">Payment Name</th>
              <th class="text-center" style=" vertical-align: middle;">Account Number</th>
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
                  <td class="text-center" style=" vertical-align: middle;"><?= $row['method_name']; ?></td>
                  <td class="text-center" style=" vertical-align: middle;"><?= $row['acc_number']; ?></td>
                  <td class="text-center" style=" vertical-align: middle;">
                    <?php if ($row['is_active'] === 1): ?>
                      <span class="badge text-bg-success rounded-pill">Active</span>
                    <?php else: ?>
                      <span class="badge text-bg-danger rounded-pill">Not Active</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-center" style="vertical-align: middle;">
                    <!-- TOMBOL EDIT DAN HAPUS -->
                    <a onclick='showEditPaymentMethodsModal(<?= json_encode($row, JSON_HEX_TAG | JSON_HEX_APOS); ?>)' class="btn btn-warning btn-sm text-white"><i class="bi bi-pencil-fill"></i></a>
                    <a href="../includes/delete_payment_method.php?id=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus?')" class="btn btn-danger btn-sm "><i class="bi bi-trash-fill"></i></a>
                  </td>
                </tr>
              <?php } ?>
            <?php else: ?>
              <tr>
                <td class="text-center py-4" colspan="5">
                  <p class="m-0">Tidak ada produk yang tersedia.</p>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
        
        <!-- PAGINATION -->
        <?php include '../includes/components/pagination.php' ?>
      </div>
    </div>
  </div>

  <!-- TOAST NOTIFICATION -->
  <?php include '../includes/components/toast.php' ?>

  <!-- MODAL EDIT PAYMENT METHOD -->
  <div class="modal fade" id="modalEditPaymentMethods" tabindex="-1" aria-labelledby="modalEditPaymentMethodsLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <form id="editPaymentMethodsForm" method="POST" action="../includes/update_payment_method.php" enctype="multipart/form-data">
        <div class="modal-content">
          <div class="modal-header bg-dark text-white">
            <h5 class="modal-title" id="modalEditPaymentMethodsLabel">Edit Payment Methods</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="id" id="editId" />
            <div class="mb-3">
              <label for="editMethodName" class="form-label">Method Name</label>
              <input type="text" class="form-control border-dark" id="editMethodName" name="method_name" required>
            </div>
            <div class="mb-3">
              <label for="editAccountNumber" class="form-label">Account Number</label>
              <input type="number" class="form-control border-dark" id="editAccountNumber" name="acc_number" required>
            </div>
            <div class="mb-3">
              <label for="editStatus" class="form-label">Status</label>
              <select class="form-select border-dark" id="editStatus" name="is_active">
                <option value="1">Active</option>
                <option value="0">Not Active</option>
              </select>
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

  <!-- MODAL CREATE PAYMENT METHOD -->
  <div class="modal fade" id="modalCreatePaymentMethods" tabindex="-1" aria-labelledby="modalCreatePaymentMethodsLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <form id="createPaymentMethodsForm" method="POST" action="../includes/create_payment_method.php" enctype="multipart/form-data">
        <div class="modal-content">
          <div class="modal-header bg-dark text-white">
            <h5 class="modal-title" id="modalCreatePaymentMethodsLabel">Create Payment Methods</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="createMethodName" class="form-label">Method Name</label>
              <input type="text" class="form-control border-dark" id="createMethodName" name="method_name" required>
            </div>
            <div class="mb-3">
              <label for="createAccountNumber" class="form-label">Account Number</label>
              <input type="number" class="form-control border-dark" id="createAccountNumber" name="acc_number" required>
            </div>
            <div class="mb-3">
              <label for="createStatus" class="form-label">Status</label>
              <select class="form-select border-dark" id="createStatus" name="is_active">
                <option value="1" selected>Active</option>
                <option value="0">Not Active</option>
              </select>
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
    
    // Fungsi untuk menampilkan toast message
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

    // Variabel untuk sidebar
    const sidebar = document.getElementById("sidebar");
    const sidebarBig = document.getElementById("sidebarBig");
    const sidebarSmall = document.getElementById("sidebarSmall");

    // Fungsi untuk menutup sidebar
    function closeBar() {
      sidebar.classList.remove("w-25");
      sidebar.style.width = "8vh";
      sidebarBig.classList.add("d-none");
      sidebarSmall.classList.remove("d-none");
      box.classList.remove("w-75");
      box.style.width = "calc(100% - 65px)";
    }

    // Fungsi untuk membuka sidebar
    function openBar() {
      sidebar.classList.add("w-25");
      sidebar.style.width = "";
      sidebarBig.classList.remove("d-none");
      sidebarSmall.classList.add("d-none");
      box.style.width = "";
      box.classList.add("w-75");
    }

    // Fungsi untuk menampilkan modal edit payment method
    function showEditPaymentMethodsModal(car) {
      document.getElementById("editId").value = car.id;
      document.getElementById("editMethodName").value = car.method_name;
      document.getElementById("editAccountNumber").value = car.acc_number;
      document.getElementById("editStatus").value = car.is_active;

      const modal = new bootstrap.Modal(document.getElementById("modalEditPaymentMethods"));
      modal.show();
    }

    // Fungsi untuk menampilkan modal create payment method
    function showCreatePaymentMethodsModal() {
      document.getElementById('createPaymentMethodsForm').reset();

      const modal = new bootstrap.Modal(document.getElementById("modalCreatePaymentMethods"));
      modal.show();
    }
  </script>

  <?php
  // Include script JavaScript
  $bootstrap = '../bootstrap/js/bootstrap.bundle.min.js';
  $js = '';
  include '../includes/script.php'
  ?>
</body>

</html>