<?php
// Mulai sesi PHP
session_start();

// Ambil nama file halaman saat ini
$current_page = basename($_SERVER['PHP_SELF']);

// Cek apakah pengguna sudah login dan memiliki role yang sesuai
if (!isset($_SESSION['role'])) {
  header("Location: ../login.php"); // Jika belum login, redirect ke halaman login
  exit;
} else if ($_SESSION['role'] === 'user') {
  header("Location: ../user/index.php"); // Jika role adalah user, redirect ke dashboard user
  exit;
}

// Include koneksi ke database
include '../database/db.php';

// Konfigurasi untuk pagination
$limit = 3;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Ambil kata kunci pencarian (jika ada)
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_param = "%{$search}%";

// Hitung total data transaksi (untuk pagination)
if ($search) {
  // Jika sedang mencari, hitung jumlah transaksi yang sesuai dengan kriteria pencarian
  $stmt_count = $conn->prepare("
    SELECT COUNT(DISTINCT t.id) AS total 
    FROM transactions t
    LEFT JOIN orders o ON t.order_id = o.id
    LEFT JOIN users u ON o.user_id = u.id
    LEFT JOIN payment_methods pm ON t.payment_method_id = pm.id
    WHERE 
      t.id LIKE ? OR 
      u.username LIKE ? OR 
      pm.method_name LIKE ?
  ");
  $stmt_count->bind_param("sss", $search_param, $search_param, $search_param);
  $stmt_count->execute();
  $count_result = $stmt_count->get_result();
} else {
  // Jika tidak mencari, hitung seluruh transaksi
  $count_result = $conn->query("SELECT COUNT(DISTINCT id) AS total FROM transactions");
}

// Ambil jumlah transaksi dari hasil query
$total_row = $count_result->fetch_assoc();
$total_transactions = $total_row['total'];
$total_pages = ceil($total_transactions / $limit); // Hitung jumlah halaman

// Ambil ID transaksi berdasarkan filter dan pagination
$transaction_ids = [];
if ($search) {
  $stmt_ids = $conn->prepare("
    SELECT DISTINCT t.id 
    FROM transactions t
    LEFT JOIN orders o ON t.order_id = o.id
    LEFT JOIN users u ON o.user_id = u.id
    LEFT JOIN payment_methods pm ON t.payment_method_id = pm.id
    WHERE 
      t.id LIKE ? OR 
      u.username LIKE ? OR 
      pm.method_name LIKE ?
    ORDER BY t.transaction_date DESC 
    LIMIT ? OFFSET ?
  ");
  $stmt_ids->bind_param("sssii", $search_param, $search_param, $search_param, $limit, $offset);
  $stmt_ids->execute();
  $result_ids = $stmt_ids->get_result();
} else {
  $result_ids = $conn->query("SELECT id FROM transactions ORDER BY transaction_date DESC LIMIT $limit OFFSET $offset");
}

// Simpan ID transaksi dalam array
while ($row = $result_ids->fetch_assoc()) {
  $transaction_ids[] = $row['id'];
}

// Ambil data lengkap transaksi berdasarkan ID-ID yang sudah didapat
$transactions = [];

if (!empty($transaction_ids)) {
  $id_list = implode(',', $transaction_ids); // Gabungkan ID untuk digunakan dalam query

  // Query utama untuk mengambil semua informasi terkait transaksi
  $sql = "SELECT 
            t.id AS transaction_id, t.order_id, t.payment_method_id, t.amount, t.transaction_date, 
            t.payment_proof, t.status AS transaction_status, t.phone_number, t.address,
            pm.method_name AS payment_method_name,

            o.user_id, o.created_at, o.total_amount AS order_total, o.status AS order_status,

            oi.product_id, oi.quantity, oi.price,
            p.name AS product_name,
            u.username AS customer_name
          FROM transactions t
          LEFT JOIN payment_methods pm ON t.payment_method_id = pm.id
          LEFT JOIN orders o ON t.order_id = o.id
          LEFT JOIN order_items oi ON o.id = oi.order_id
          LEFT JOIN products p ON oi.product_id = p.id
          LEFT JOIN users u ON o.user_id = u.id
          WHERE t.id IN ($id_list)
          ORDER BY t.transaction_date DESC, t.id DESC";

  $result = $conn->query($sql);

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $tid = $row['transaction_id'];

      // Jika transaksi belum disimpan, inisialisasi datanya
      if (!isset($transactions[$tid])) {
        $transactions[$tid] = [
          'transaction_id' => $tid,
          'order_id' => $row['order_id'],
          'payment_method' => $row['payment_method_name'],
          'amount' => $row['amount'],
          'transaction_date' => $row['transaction_date'],
          'payment_proof' => $row['payment_proof'],
          'status' => $row['transaction_status'],
          'phone_number' => $row['phone_number'],
          'address' => $row['address'],
          'customer_name' => $row['customer_name'],
          'order_date' => $row['created_at'],
          'order_total' => $row['order_total'],
          'order_status' => $row['order_status'],
          'items' => [],
        ];
      }

      // Tambahkan item produk ke dalam transaksi
      if (!is_null($row['product_name'])) {
        $transactions[$tid]['items'][] = [
          'product_name' => $row['product_name'],
          'quantity' => $row['quantity'],
          'price' => $row['price']
        ];
      }
    }
  }
}

// Hitung jumlah notifikasi belum dibaca untuk ditampilkan di navbar
$userId = $_SESSION['id'];
$query = mysqli_query($conn, "SELECT COUNT(*) AS unread FROM notifications WHERE user_id = $userId AND is_read = 0");
$data = mysqli_fetch_assoc($query);
$unreadCount = $data['unread'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <?php
  // Variabel untuk disisipkan ke dalam tag <head>
  $title = 'Transactions';
  $link = '../assets/img/favicon.ico';
  $css = '../css/style.css';
  $bootstrap = '../bootstrap/css/bootstrap.min.css';
  include '../includes/style.php'; // Memuat style tambahan
  ?>
</head>

<body>
  <div class="d-flex vh-100">
    <?php
    // Daftar menu navigasi sidebar
    $navlink = [
      ['file' => 'dashboard.php', 'label' => 'Home', 'icon' => 'bi-house-fill'],
      ['file' => 'product.php', 'label' => 'Products', 'icon' => 'bi-box-seam-fill'],
      ['file' => 'user.php', 'label' => 'Users', 'icon' => 'bi-people-fill'],
      ['file' => 'order.php', 'label' => 'Orders', 'icon' => 'bi-bag-dash-fill'],
      ['file' => 'transaction.php', 'label' => 'Transactions', 'icon' => 'bi-wallet-fill'],
      ['file' => 'carousel.php', 'label' => 'Carousels', 'icon' => 'bi-image-fill'],
      ['file' => 'payment_method.php', 'label' => 'Payment Methods', 'icon' => 'bi-wallet-fill'],
    ];
    include '../includes/components/navbar_sider.php'; // Tampilkan sidebar
    ?>

    <div id="box" class="w-100 bg-light py-3 px-4">
      <?php include '../includes/components/nav_side.php' // Tampilkan navbar atas ?>

      <div class="border shadow rounded-2 p-4">
        <!-- Header halaman -->
        <div class="d-flex align-items-center justify-content-between">
          <h1 class="fs-4 fw-bold">Daftar Products</h1>
          <!-- Form pencarian -->
          <div class="w-25">
            <form method="GET" action="" class="d-flex align-items-center gap-2">
              <input type="text" name="search" class="form-control form-control-sm" placeholder="Search ..." value="<?= htmlspecialchars($search) ?>">
              <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
              <?php if ($search): ?>
                <a href="transaction.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-clockwise"></i></a>
              <?php endif; ?>
            </form>
          </div>
        </div>
        <hr>
        <!-- Tabel transaksi -->
        <table class="mt-2 table table-bordered">
          <thead>
            <tr>
              <th class="text-center py-3" style=" vertical-align: middle;">ID Transaction</th>
              <th class="text-center" style=" vertical-align: middle;">ID Orders</th>
              <th class="text-center" style=" vertical-align: middle;">Payment Method</th>
              <th class="text-center" style=" vertical-align: middle;">Total Amount</th>
              <th class="text-center" style=" vertical-align: middle;">Proof of Payment</th>
              <th class="text-center" style=" vertical-align: middle;">Status</th>
              <th class="text-center" style=" vertical-align: middle;">Action</th>
            </tr>
          </thead>
          <tbody>
            <!-- Loop data transaksi -->
            <?php if (!empty($transactions)) : foreach ($transactions as $trans): ?>
                <tr>
                  <td class="text-center" style=" vertical-align: middle;"><?= $trans['transaction_id'] ?></td>

                  <!-- Tombol detail order -->
                  <td class="text-center" style=" vertical-align: middle;">
                    <a onclick='showOrderDetail(<?= json_encode($trans, JSON_HEX_TAG | JSON_HEX_APOS); ?>)' class="btn btn-secondary btn-sm text-white"><?= $trans['order_id'] ?></a>
                  </td>

                  <td class="text-center" style=" vertical-align: middle;"><?= $trans['payment_method'] ?></td>

                  <!-- Format rupiah -->
                  <td class="text-center" style=" vertical-align: middle;">Rp.<?= number_format($trans['amount'], 0, ',', '.') ?></td>

                  <!-- Tampilkan gambar bukti pembayaran -->
                  <td class="text-center" style=" vertical-align: middle;"><img width="58px" height="58px" src="../<?= $trans['payment_proof'] ?>" alt="Proof of Payment"></td>

                  <!-- Badge status transaksi -->
                  <td class="text-center" style=" vertical-align: middle;">
                    <?php if ($trans['status'] == 'pending'): ?>
                      <span class="badge text-light bg-info text-center">Pending</span>
                    <?php elseif ($trans['status'] == 'completed') : ?>
                      <span class="badge text-bg-success text-center">Completed</span>
                    <?php else : ?>
                      <span class="badge text-bg-danger text-center">Failed</span>
                    <?php endif; ?>
                  </td>

                  <!-- Tombol edit & hapus -->
                  <td class="text-center" style=" vertical-align: middle;">
                    <a onclick='showEditTransactionModal(<?= json_encode($trans, JSON_HEX_TAG | JSON_HEX_APOS); ?>)' class="btn btn-warning btn-sm text-white"><i class="bi bi-pencil-fill"></i></a>
                    <a href="../includes/delete_transaction.php?id=<?= $trans['transaction_id'] ?>" onclick="return confirm('Yakin hapus?')" class="btn btn-danger btn-sm "><i class="bi bi-trash-fill"></i></a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <!-- Jika tidak ada data -->
              <tr>
                <td class="text-center py-4" colspan="7">
                  <p class="m-0">Tidak ada produk yang tersedia.</p>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>

        <!-- Komponen pagination -->
        <?php include '../includes/components/pagination.php' ?>
      </div>
    </div>
  </div>
  
<?php include '../includes/components/toast.php' ?>

<!-- Modal: Detail Order -->
<div class="modal fade" id="modalDetailOrder" tabindex="-1" aria-labelledby="modalDetailOrderLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-dark text-light">
        <h5 class="modal-title" id="modalDetailOrderLabel">Detail Order</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex align-items-center justify-content-between">
          <p><strong>ID Order:</strong> <span id="modalOrderId"></span></p>
          <div class="d-flex align-items-center gap-3">
            <p><span class="badge" id="modalOrderStatus"></span></p>
            <p><strong><span id="modalOrderDate"></span></strong></p>
          </div>
        </div>
        <hr>
        <h6>Produk Dipesan:</h6>
        <ul class="list-group" id="modalOrderItems">
          <!-- Produk akan dimasukkan lewat JS -->
        </ul>
      </div>
      <div class="modal-footer d-flex align-items-center justify-content-between">
        <p><strong>Total:</strong> Rp<span id="modalOrderTotal"></span></p>
        <button type="button" class="btn btn-sm btn-secondary text-light" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Edit Transaksi -->
<div class="modal fade" id="modalEditTransaction" tabindex="-1" aria-labelledby="modalEditTransactionLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title" id="modalEditTransactionLabel">Edit Transaksi</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex align-items-center justify-content-between">
          <p><strong>Customer</strong> <span id="editCustomerName"></span></p>
          <p><span id="editDatetimeTransaction"></span></p>
        </div>
        <p><strong>No. HP:</strong> <span id="editPhoneNumber"></span></p>
        <p><strong>Alamat:</strong> <span id="editAddress"></span></p>
        <p><strong>Metode Pembayaran:</strong> <span id="editPaymentMethod"></span></p>

        <div class="d-flex justify-content-between gap-3">
          <div class="w-50">
            <h6>Produk:</h6>
            <ul class="list-group mb-3" id="editOrderItems"></ul>
            <p><strong>Total:</strong> Rp<span id="editTotalAmount"></span></p>
          </div>
          <div class="w-50">
            <strong>Bukti Pembayaran:</strong><br>
            <div class="d-flex align-items-center justify-content-center">
              <img id="editPaymentProof" src="" class="w-100 img-fluid border rounded" style="max-height:300px;">
            </div>
            <form id="editTransactionForm" method="POST" action="../includes/update_transaction.php">
              <input type="hidden" name="transaction_id" id="editTransactionId">
              <div class="mt-3">
                <label for="status" class="form-label">Ubah Status</label>
                <select class="form-select" name="status" id="editStatus">
                  <option value="pending">Pending</option>
                  <option value="completed">Completed</option>
                  <option value="failed">Failed</option>
                </select>
              </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Simpan Perubahan</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
      </form>
    </div>
  </div>
</div>

<script>
  // Menandai notifikasi sebagai telah dibaca
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

  // Tampilkan toast jika ada pesan dari sessionStorage
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

  // Sidebar control
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

  // Tampilkan modal edit transaksi
  function showEditTransactionModal(transaction) {
    document.getElementById("editTransactionId").value = transaction.transaction_id;
    document.getElementById("editCustomerName").innerText = transaction.customer_name;
    document.getElementById("editDatetimeTransaction").innerText = transaction.transaction_date;
    document.getElementById("editPhoneNumber").innerText = transaction.phone_number;
    document.getElementById("editAddress").innerText = transaction.address;
    document.getElementById("editTotalAmount").innerText = Number(transaction.amount).toLocaleString('id-ID');
    document.getElementById("editPaymentMethod").innerText = transaction.payment_method;
    document.getElementById("editStatus").value = transaction.status;

    // Bukti pembayaran
    document.getElementById("editPaymentProof").src = `../${transaction.payment_proof}`;

    // Daftar produk
    const itemList = document.getElementById("editOrderItems");
    itemList.innerHTML = "";
    if (transaction.items && transaction.items.length > 0) {
      transaction.items.forEach(item => {
        const li = document.createElement("li");
        li.className = "list-group-item";
        li.innerText = `${item.quantity} x ${item.product_name} @ Rp${Number(item.price).toLocaleString('id-ID')}`;
        itemList.appendChild(li);
      });
    }

    // Tampilkan modal
    const modal = new bootstrap.Modal(document.getElementById("modalEditTransaction"));
    modal.show();
  }

  // Tampilkan modal detail order
  function showOrderDetail(trans) {
    document.getElementById("modalOrderId").innerText = trans.order_id;
    document.getElementById("modalOrderDate").innerText = trans.order_date;
    document.getElementById("modalOrderTotal").innerText = Number(trans.order_total).toLocaleString('id-ID');

    const status = document.getElementById("modalOrderStatus");
    status.innerText = trans.order_status;
    status.className = "badge";

    // Warna badge berdasarkan status
    if (trans.order_status === 'pending') {
      status.classList.add('bg-info');
    } else if (trans.order_status === 'paid') {
      status.classList.add('bg-success');
    } else {
      status.classList.add('bg-danger');
    }

    const itemList = document.getElementById("modalOrderItems");
    itemList.innerHTML = "";

    // Tambah item ke dalam modal
    if (trans.items && trans.items.length > 0) {
      trans.items.forEach(item => {
        const li = document.createElement("li");
        li.className = "list-group-item";
        li.innerText = `> ${item.quantity} ${item.product_name} @ Rp${Number(item.price).toLocaleString('id-ID')}`;
        itemList.appendChild(li);
      });
    } else {
      const li = document.createElement("li");
      li.className = "list-group-item";
      li.innerText = "Tidak ada produk.";
      itemList.appendChild(li);
    }

    const modal = new bootstrap.Modal(document.getElementById("modalDetailOrder"));
    modal.show();
  }
</script>

<!-- Sisipkan Bootstrap JS dan script tambahan -->
<?php
$bootstrap = '../bootstrap/js/bootstrap.bundle.min.js';
$js = '';
include '../includes/script.php'
?>
</body>
</html>
