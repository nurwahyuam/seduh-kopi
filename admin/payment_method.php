<?php
session_start();

$current_page = basename($_SERVER['PHP_SELF']);

if (!isset($_SESSION['role'])) {
  header("Location: ../login.php");
  exit;
} else if ($_SESSION['role'] === 'user') {
  header("Location: ../user/index.php");
  exit;
}

include '../database/db.php';

$limit = 9;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_param = "%{$search}%";

// Hitung total data
if ($search) {
  $count_sql = "SELECT COUNT(*) AS total FROM payment_methods WHERE method_name LIKE ? OR acc_number LIKE ?";
  $stmt_count = $conn->prepare($count_sql);
  $stmt_count->bind_param("ss", $search_param, $search_param);
  $stmt_count->execute();
  $count_result = $stmt_count->get_result();
} else {
  $count_result = $conn->query("SELECT COUNT(*) AS total FROM payment_methods");
}

$total_row = $count_result->fetch_assoc();
$total_users = $total_row['total'];
$total_pages = ceil($total_users / $limit);

// Ambil data sesuai pencarian + pagination
if ($search) {
  $sql = "SELECT * FROM payment_methods WHERE method_name LIKE ? OR acc_number LIKE ? LIMIT ?, ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ssii", $search_param, $search_param, $offset, $limit);
} else {
  $sql = "SELECT * FROM payment_methods LIMIT ?, ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $offset, $limit);
}

$stmt->execute();
$result = $stmt->get_result();

$userId = $_SESSION['id'];
$query = mysqli_query($conn, "SELECT COUNT(*) AS unread FROM notifications WHERE user_id = $userId AND is_read = 0");
$data = mysqli_fetch_assoc($query);
$unreadCount = $data['unread'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <?php
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

    <!-- MAIN CONTENT -->
    <div id="box" class="w-100 bg-light py-3 px-4">
      <?php include '../includes/components/nav_side.php' ?>
      <div class="border shadow rounded-2 p-4">
        <div class="d-flex align-items-center justify-content-between">
          <h1 class="fs-4 fw-bold">Daftar Payment Methods</h1>
          <div class="d-flex align-items-center justify-content-end gap-2">
            <form method="GET" action="" class="d-flex align-items-center gap-2">
              <input type="text" name="search" class="form-control form-control-sm" placeholder="Search ..." value="<?= htmlspecialchars($search) ?>">
              <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
              <?php if ($search): ?>
                <a href="payment_method.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-clockwise"></i></a>
              <?php endif; ?>
            </form>
            <a onclick=showCreatePaymentMethodsModal() class="btn btn-success btn-sm text-white"><i class="bi bi-plus-circle"></i></a>
          </div>
        </div>
        <hr>
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
        <?php include '../includes/components/pagination.php' ?>
      </div>
    </div>
  </div>

  <?php include '../includes/components/toast.php' ?>



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

    function showEditPaymentMethodsModal(car) {
      document.getElementById("editId").value = car.id;
      document.getElementById("editMethodName").value = car.method_name;
      document.getElementById("editAccountNumber").value = car.acc_number;
      document.getElementById("editStatus").value = car.is_active;

      const modal = new bootstrap.Modal(document.getElementById("modalEditPaymentMethods"));
      modal.show();
    }

    function showCreatePaymentMethodsModal() {
      document.getElementById('createPaymentMethodsForm').reset();

      const modal = new bootstrap.Modal(document.getElementById("modalCreatePaymentMethods"));
      modal.show();
    }
  </script>

  <?php
  $bootstrap = '../bootstrap/js/bootstrap.bundle.min.js';
  $js = '';
  include '../includes/script.php'
  ?>
</body>

</html>