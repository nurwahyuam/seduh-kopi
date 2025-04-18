<?php
session_start();

$current_page = basename($_SERVER['PHP_SELF']);

if (!isset($_SESSION['role'])) {
  header("Location: ../login.php");
  exit;
} else if (isset($_SESSION['role']) && $_SESSION['role'] === 'user') {
  header("Location: ../user/index.php");
  exit;
}

include '../database/db.php';

$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Ambil keyword pencarian (jika ada)
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_param = "%{$search}%";

// Hitung total data berdasarkan pencarian
if (!empty($search)) {
  $count_sql = "SELECT COUNT(*) AS total FROM users WHERE username LIKE ? OR email LIKE ?";
  $stmt_count = $conn->prepare($count_sql);
  $stmt_count->bind_param("ss", $search_param, $search_param);
  $stmt_count->execute();
  $count_result = $stmt_count->get_result();
} else {
  $count_result = $conn->query("SELECT COUNT(*) AS total FROM users");
}

$total_row = $count_result->fetch_assoc();
$total_users = $total_row['total'];
$total_pages = ceil($total_users / $limit);

// Ambil data user sesuai pagination & search
if (!empty($search)) {
  $sql = "SELECT * FROM users WHERE username LIKE ? OR email LIKE ? LIMIT ?, ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ssii", $search_param, $search_param, $offset, $limit);
} else {
  $sql = "SELECT * FROM users LIMIT ?, ?";
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
  $title = 'Users';
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

    <!-- MAIN CONTENT -->
    <div id="box" class="w-100 bg-light py-3 px-4">
      <?php include '../includes/components/nav_side.php' ?>

      <div class="border shadow rounded-2 p-4">
        <div class="d-flex align-items-center justify-content-between">
          <h1 class="fs-4 fw-bold">Daftar Users</h1>
          <div class="w-25">
            <form method="GET" action="" class="d-flex align-items-center gap-2">
              <input type="text" name="search" class="form-control form-control-sm" placeholder="Search ..." value="<?= htmlspecialchars($search) ?>">
              <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
              <?php if ($search): ?>
                <a href="user.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-clockwise"></i></a>
              <?php endif; ?>
            </form>
          </div>
        </div>
        <hr>
        <table class="mt-2 table table-bordered ">
          <thead>
            <tr>
              <th class="text-center" style=" vertical-align: middle;">No</th>
              <th class="text-center" style=" vertical-align: middle;">Profile Photo</th>
              <th class="text-center" style=" vertical-align: middle;">Username</th>
              <th class="text-center" style=" vertical-align: middle;">Email</th>
              <th class="text-center" style=" vertical-align: middle;">Role</th>
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
                    <?php if ($row['profile_photo'] !== NULL): ?>
                      <img width="58px" height="58px" src="../images/user/<?= $row['profile_photo'] ?>" alt="Image" class="rounded-5">
                    <?php else: ?>
                      <i class="bi bi-person-circle fs-1"></i>
                    <?php endif; ?>
                  </td>
                  <td class="text-center text-capitalize" style="vertical-align: middle;"><?= $row['username'] ?></td>
                  <td class="text-center" style=" vertical-align: middle;"><?= $row['email'] ?></td>
                  <td class="text-center" style=" vertical-align: middle;">
                    <?php if ($row['role'] === 'user'): ?>
                      <span class="badge text-bg-secondary rounded-pill"><?= $row['role'] ?></span>
                    <?php else: ?>
                      <span class="badge text-bg-dark rounded-pill"><?= $row['role'] ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="text-center" style="vertical-align: middle;">
                    <a onclick='showEditUsersModal(<?= json_encode($row, JSON_HEX_TAG | JSON_HEX_APOS); ?>)' class="btn btn-warning btn-sm text-white"><i class="bi bi-pencil-fill"></i></a>
                    <a href="../includes/delete_user.php?id=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus?')" class="btn btn-danger btn-sm"><i class="bi bi-trash-fill"></i></a>
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

  <div class="modal fade" id="modalEditUsers" tabindex="-1" aria-labelledby="modalEditUsersLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <form id="editUsersForm" method="POST" action="../includes/update_user.php" enctype="multipart/form-data">
        <div class="modal-content">
          <div class="modal-header bg-dark text-white">
            <h5 class="modal-title" id="modalEditUsersLabel">Edit Users</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="id" id="editId" />
            <div class="mb-3">
              <label for="editUsername" class="form-label">Username</label>
              <input type="text" class="form-control border-dark" id="editUsername" name="username" required>
            </div>
            <div class="mb-3">
              <label for="editEmail" class="form-label">Email</label>
              <input type="email" class="form-control border-dark" id="editEmail" name="email" required>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label for="editPassword" class="form-label">Password</label>
                  <input type="password" class="form-control border-dark" id="editPassword" name="password">
                </div>
                <div class="mb-3">
                  <label for="editProfilePhoto" class="form-label">Profile Photo</label>
                  <input type="file" class="form-control border-dark" id="editProfilePhoto" name="profile_photo" accept="image/*">
                  <div class="form-text">Note: Leave blank if not updating the photo.</div>
                </div>
                <div class="mb-3">
                  <label for="editRole" class="form-label">Role</label>
                  <select class="form-select border-dark" id="editRole" name="role">
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6 d-flex align-items-center justify-content-center">
                <img id="editImageUser" src="" class="img-fluid border rounded-circle shadow-sm" style="max-height: 250px;">
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

    function showEditUsersModal(user) {
      document.getElementById("editId").value = user.id;
      document.getElementById("editUsername").value = user.username;
      document.getElementById("editEmail").value = user.email;
      document.getElementById("editRole").value = user.role;

      const linkFile = `../images/user/${user.profile_photo}`;
      document.getElementById("editImageUser").src = linkFile;

      const modal = new bootstrap.Modal(document.getElementById("modalEditUsers"));
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