<?php
session_start(); // Memulai sesi untuk melacak data login pengguna

$current_page = basename($_SERVER['PHP_SELF']); // Mendapatkan nama file dari halaman saat ini

// Cek apakah pengguna sudah login dan memiliki role yang sesuai
if (!isset($_SESSION['role'])) {
  header("Location: ../login.php"); // Redirect ke halaman login jika belum login
  exit;
} else if (isset($_SESSION['role']) && $_SESSION['role'] === 'user') {
  header("Location: ../user/index.php"); // Redirect ke halaman user jika role-nya 'user'
  exit;
}

include '../database/db.php'; // Menghubungkan ke database

$limit = 5; // Jumlah data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Ambil nomor halaman dari parameter URL
$offset = ($page - 1) * $limit; // Hitung offset untuk query pagination

// Ambil keyword pencarian dari parameter URL jika ada
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_param = "%{$search}%"; // Format untuk LIKE query di SQL

// Hitung total data pengguna sesuai keyword pencarian
if (!empty($search)) {
  $count_sql = "SELECT COUNT(*) AS total FROM users WHERE username LIKE ? OR email LIKE ?";
  $stmt_count = $conn->prepare($count_sql); // Siapkan statement SQL dengan parameter
  $stmt_count->bind_param("ss", $search_param, $search_param); // Binding parameter
  $stmt_count->execute(); // Eksekusi query
  $count_result = $stmt_count->get_result(); // Ambil hasil query
} else {
  $count_result = $conn->query("SELECT COUNT(*) AS total FROM users"); // Hitung semua user jika tidak ada pencarian
}

$total_row = $count_result->fetch_assoc(); // Ambil hasil dalam bentuk array asosiatif
$total_users = $total_row['total']; // Ambil total user
$total_pages = ceil($total_users / $limit); // Hitung total halaman

// Ambil data user sesuai keyword pencarian dan pagination
if (!empty($search)) {
  $sql = "SELECT * FROM users WHERE username LIKE ? OR email LIKE ? LIMIT ?, ?";
  $stmt = $conn->prepare($sql); // Siapkan statement SQL
  $stmt->bind_param("ssii", $search_param, $search_param, $offset, $limit); // Binding parameter
} else {
  $sql = "SELECT * FROM users LIMIT ?, ?";
  $stmt = $conn->prepare($sql); // Siapkan statement SQL
  $stmt->bind_param("ii", $offset, $limit); // Binding parameter
}

$stmt->execute(); // Eksekusi query
$result = $stmt->get_result(); // Ambil hasil query

// Ambil jumlah notifikasi yang belum dibaca oleh user yang sedang login
$userId = $_SESSION['id'];
$query = mysqli_query($conn, "SELECT COUNT(*) AS unread FROM notifications WHERE user_id = $userId AND is_read = 0");
$data = mysqli_fetch_assoc($query); // Ambil hasil query
$unreadCount = $data['unread']; // Simpan jumlah notifikasi yang belum dibaca
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <?php
  $title = 'Users'; // Judul halaman
  $link = '../assets/img/favicon.ico'; // Link favicon
  $css = '../css/style.css'; // Path file CSS kustom
  $bootstrap = '../bootstrap/css/bootstrap.min.css'; // Path file CSS Bootstrap
  include '../includes/style.php'; // Include file style yang digunakan
  ?>
</head>

<body>
  <div class="d-flex vh-100"> <!-- Container utama dengan layout flex dan tinggi penuh layar -->
    <?php
    // Daftar link navigasi sidebar dengan label dan ikon
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
    include '../includes/components/navbar_sider.php'; // Menyisipkan file komponen sidebar
    ?>
<!-- MAIN CONTENT -->
<div id="box" class="w-100 bg-light py-3 px-4">
  <!-- Menyisipkan komponen navbar sisi atas -->
  <?php include '../includes/components/nav_side.php' ?>

  <!-- Container utama konten halaman -->
  <div class="border shadow rounded-2 p-4">
    <!-- Bagian header halaman dan form pencarian -->
    <div class="d-flex align-items-center justify-content-between">
      <h1 class="fs-4 fw-bold">Daftar Users</h1>
      <!-- Form pencarian -->
      <div class="w-25">
        <form method="GET" action="" class="d-flex align-items-center gap-2">
          <!-- Input pencarian -->
          <input type="text" name="search" class="form-control form-control-sm" placeholder="Search ..." value="<?= htmlspecialchars($search) ?>">
          <!-- Tombol cari -->
          <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
          <!-- Tombol reset pencarian (jika sedang mencari) -->
          <?php if ($search): ?>
            <a href="user.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-clockwise"></i></a>
          <?php endif; ?>
        </form>
      </div>
    </div>

    <hr>

    <!-- Tabel daftar user -->
    <table class="mt-2 table table-bordered ">
      <thead>
        <tr>
          <!-- Header tabel -->
          <th class="text-center" style=" vertical-align: middle;">No</th>
          <th class="text-center" style=" vertical-align: middle;">Profile Photo</th>
          <th class="text-center" style=" vertical-align: middle;">Username</th>
          <th class="text-center" style=" vertical-align: middle;">Email</th>
          <th class="text-center" style=" vertical-align: middle;">Role</th>
          <th class="text-center" style=" vertical-align: middle;">Action</th>
        </tr>
      </thead>
      <tbody>
        <!-- Jika data user ditemukan -->
        <?php if ($result->num_rows > 0): ?>
          <?php $i = 1;
          while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
              <!-- Kolom nomor -->
              <td class="text-center" style=" vertical-align: middle;"><?= $i++; ?></td>
              <!-- Kolom foto profil -->
              <td class="text-center">
                <?php if ($row['profile_photo'] !== NULL): ?>
                  <img width="58px" height="58px" src="../images/user/<?= $row['profile_photo'] ?>" alt="Image" class="rounded-5">
                <?php else: ?>
                  <i class="bi bi-person-circle fs-1"></i>
                <?php endif; ?>
              </td>
              <!-- Kolom username -->
              <td class="text-center text-capitalize" style="vertical-align: middle;"><?= $row['username'] ?></td>
              <!-- Kolom email -->
              <td class="text-center" style=" vertical-align: middle;"><?= $row['email'] ?></td>
              <!-- Kolom role dengan badge berbeda berdasarkan jenis -->
              <td class="text-center" style=" vertical-align: middle;">
                <?php if ($row['role'] === 'user'): ?>
                  <span class="badge text-bg-secondary rounded-pill"><?= $row['role'] ?></span>
                <?php else: ?>
                  <span class="badge text-bg-dark rounded-pill"><?= $row['role'] ?></span>
                <?php endif; ?>
              </td>
              <!-- Kolom aksi edit dan delete -->
              <td class="text-center" style="vertical-align: middle;">
                <!-- Tombol edit dengan memanggil fungsi showEditUsersModal() -->
                <a onclick='showEditUsersModal(<?= json_encode($row, JSON_HEX_TAG | JSON_HEX_APOS); ?>)' class="btn btn-warning btn-sm text-white"><i class="bi bi-pencil-fill"></i></a>
                <!-- Tombol hapus dengan konfirmasi -->
                <a href="../includes/delete_user.php?id=<?= $row['id'] ?>" onclick="return confirm('Yakin hapus?')" class="btn btn-danger btn-sm"><i class="bi bi-trash-fill"></i></a>
              </td>
            </tr>
          <?php } ?>
        <?php else: ?>
          <!-- Jika tidak ada data user -->
          <tr>
            <td class="text-center py-4" colspan="6">
              <p class="m-0">Tidak ada produk yang tersedia.</p>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- Menyisipkan komponen pagination -->
    <?php include '../includes/components/pagination.php' ?>
  </div>
</div>
<!-- Modal Edit User -->
<div class="modal fade" id="modalEditUsers" tabindex="-1" aria-labelledby="modalEditUsersLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <!-- Form edit user, akan mengirimkan data ke update_user.php -->
    <form id="editUsersForm" method="POST" action="../includes/update_user.php" enctype="multipart/form-data">
      <div class="modal-content">
        <!-- Header modal -->
        <div class="modal-header bg-dark text-white">
          <h5 class="modal-title" id="modalEditUsersLabel">Edit Users</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <!-- Body modal -->
        <div class="modal-body">
          <!-- ID disembunyikan -->
          <input type="hidden" name="id" id="editId" />

          <!-- Input username -->
          <div class="mb-3">
            <label for="editUsername" class="form-label">Username</label>
            <input type="text" class="form-control border-dark" id="editUsername" name="username" required>
          </div>

          <!-- Input email -->
          <div class="mb-3">
            <label for="editEmail" class="form-label">Email</label>
            <input type="email" class="form-control border-dark" id="editEmail" name="email" required>
          </div>

          <div class="row">
            <div class="col-md-6">
              <!-- Input password -->
              <div class="mb-3">
                <label for="editPassword" class="form-label">Password</label>
                <input type="password" class="form-control border-dark" id="editPassword" name="password">
              </div>

              <!-- Input file foto profil -->
              <div class="mb-3">
                <label for="editProfilePhoto" class="form-label">Profile Photo</label>
                <input type="file" class="form-control border-dark" id="editProfilePhoto" name="profile_photo" accept="image/*">
                <div class="form-text">Note: Leave blank if not updating the photo.</div>
              </div>

              <!-- Input role -->
              <div class="mb-3">
                <label for="editRole" class="form-label">Role</label>
                <select class="form-select border-dark" id="editRole" name="role">
                  <option value="admin">Admin</option>
                  <option value="user">User</option>
                </select>
              </div>
            </div>

            <!-- Preview foto profil -->
            <div class="col-md-6 d-flex align-items-center justify-content-center">
              <img id="editImageUser" src="" class="img-fluid border rounded-circle shadow-sm" style="max-height: 250px;">
            </div>
          </div>
        </div>

        <!-- Footer modal -->
        <div class="modal-footer">
          <button type="submit" class="btn btn-warning">Save</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </form>
  </div>
</div>
<script>
  // Tombol notifikasi dibaca
  document.getElementById('notifButton').addEventListener('click', function() {
    fetch('../includes/mark_read.php')
      .then(response => response.json())
      .then(data => {
        if (data.status === "success") {
          let badge = document.querySelector('.badge.bg-danger');
          if (badge) badge.remove(); // Hapus badge notifikasi
        } else {
          console.error(data.message);
        }
      })
      .catch(error => console.error("Gagal menghubungi server:", error));
  });

  // Tampilkan toast jika ada pesan di sessionStorage
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

  // Sidebar toggle
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

  // Tampilkan modal edit dan isi field sesuai data user
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
  // Memuat script Bootstrap
  $bootstrap = '../bootstrap/js/bootstrap.bundle.min.js';
  $js = '';
  include '../includes/script.php'
?>
</body>

</html>
