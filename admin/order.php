<?php
session_start();

$current_page = basename($_SERVER['PHP_SELF']);

if (!isset($_SESSION['role'])) {
  header("Location: ../login.php");
  exit;
} elseif ($_SESSION['role'] === 'user') {
  header("Location: ../user/index.php");
  exit;
}

include '../database/db.php';

$limit = 4;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_param = "%{$search}%";

// Hitung total order
if ($search) {
  $stmt_count = $conn->prepare("
    SELECT COUNT(DISTINCT o.id) AS total
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.id LIKE ? OR u.username LIKE ? OR o.status LIKE ?
  ");
  $stmt_count->bind_param("sss", $search_param, $search_param, $search_param);
  $stmt_count->execute();
  $count_result = $stmt_count->get_result();
} else {
  $count_result = $conn->query("SELECT COUNT(DISTINCT o.id) AS total FROM orders o");
}

$total_row = $count_result->fetch_assoc();
$total_orders = $total_row['total'];
$total_pages = ceil($total_orders / $limit);

// Ambil ID order
$order_ids = [];
if ($search) {
  $stmt_ids = $conn->prepare("
    SELECT DISTINCT o.id
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.id LIKE ? OR u.username LIKE ? OR o.status LIKE ?
    ORDER BY o.created_at DESC
    LIMIT ? OFFSET ?
  ");
  $stmt_ids->bind_param("sssii", $search_param, $search_param, $search_param, $limit, $offset);
  $stmt_ids->execute();
  $result_ids = $stmt_ids->get_result();
} else {
  $result_ids = $conn->query("SELECT id FROM orders ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
}

while ($row = $result_ids->fetch_assoc()) {
  $order_ids[] = $row['id'];
}

$orders = [];
if (!empty($order_ids)) {
  $id_list = implode(',', $order_ids);

  $sql = "SELECT 
            o.id AS order_id, o.user_id, o.created_at, o.total_amount, o.status,
            oi.product_id, oi.quantity, oi.price,
            p.name AS product_name,
            u.username AS customer_name
          FROM orders o
          LEFT JOIN order_items oi ON o.id = oi.order_id
          LEFT JOIN products p ON oi.product_id = p.id
          LEFT JOIN users u ON o.user_id = u.id
          WHERE o.id IN ($id_list)
          ORDER BY o.created_at DESC, o.id DESC";

  $result = $conn->query($sql);

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $order_id = $row['order_id'];
      if (!isset($orders[$order_id])) {
        $orders[$order_id] = [
          'order_id' => $order_id,
          'user_id' => $row['user_id'],
          'customer_name' => $row['customer_name'],
          'created_at' => $row['created_at'],
          'total_amount' => $row['total_amount'],
          'status' => $row['status'],
          'items' => [],
        ];
      }

      if (!is_null($row['product_name'])) {
        $orders[$order_id]['items'][] = [
          'product_name' => $row['product_name'],
          'quantity' => $row['quantity'],
          'price' => $row['price']
        ];
      }
    }
  }
}

$userId = $_SESSION['id'];
$query = mysqli_query($conn, "SELECT COUNT(*) AS unread FROM notifications WHERE user_id = $userId AND is_read = 0");
$data = mysqli_fetch_assoc($query);
$unreadCount = $data['unread'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <?php
  $title = 'Orders';
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

    <div id="box" class="w-100 bg-light py-3 px-4">
      <?php include '../includes/components/nav_side.php' ?>

      <div class="border shadow rounded-2 p-4">
        <div class="d-flex align-items-center justify-content-between">
          <h1 class="fs-4 fw-bold">Daftar Orders</h1>
          <div class="w-25">
            <form method="GET" action="" class="d-flex align-items-center gap-2">
              <input type="text" name="search" class="form-control form-control-sm" placeholder="Search ..." value="<?= htmlspecialchars($search) ?>">
              <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
              <?php if ($search): ?>
                <a href="order.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-clockwise"></i></a>
              <?php endif; ?>
            </form>
          </div>
        </div>
        <hr>
        <table class="mt-2 table table-bordered">
          <thead>
            <tr>
              <th class="text-center py-3" style=" vertical-align: middle;">ID Order</th>
              <th class="text-center" style=" vertical-align: middle;">Datetime</th>
              <th class="text-center" style=" vertical-align: middle;">Costumer</th>
              <th class="text-center" style=" vertical-align: middle;">Product</th>
              <th class="text-center" style=" vertical-align: middle;">Total Amount</th>
              <th class="text-center" style=" vertical-align: middle;">Status</th>
              <th class="text-center" style=" vertical-align: middle;">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($orders)) : foreach ($orders as $order) : ?>
                <tr>
                  <td class="text-center" style=" vertical-align: middle;"><?= $order['order_id'] ?></td>
                  <td class="text-center" style=" vertical-align: middle;"><?= $order['created_at'] ?></td>
                  <td class="text-center" style=" vertical-align: middle;"><?= htmlspecialchars($order['customer_name']); ?></td>
                  <td class="" style=" vertical-align: middle;">
                    <ul class="m-0 ps-3">
                      <?php
                      $limitList = 1;
                      foreach ($order['items'] as $item):
                        if ($limitList <= 2): ?>
                          <li><?= $item['quantity']; ?> <?= $item['product_name']; ?></li>
                        <?php elseif ($limitList == 3): ?>
                          <li>...</li>
                      <?php break;
                        endif;
                        $limitList++;
                      endforeach; ?>
                    </ul>
                  </td>
                  <td class="text-center" style=" vertical-align: middle;">Rp<?= number_format($order['total_amount'], 0, ',', '.'); ?></td>
                  <td class="text-center" style=" vertical-align: middle;">
                    <?php if ($order['status'] == 'pending'): ?>
                      <span class="badge text-light bg-info text-center">Pending</span>
                    <?php elseif ($order['status'] == 'paid') : ?>
                      <span class="badge text-bg-success text-center">Paid</span>
                    <?php else : ?>
                      <span class="badge text-bg-danger text-center">Expired</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-center" style="vertical-align: middle;">
                    <a onclick='showOrderDetail(<?= json_encode($order, JSON_HEX_TAG | JSON_HEX_APOS); ?>)' class="btn btn-warning btn-sm text-white"><i class="bi bi-eye-fill"></i></a>
                    <a href="../includes/delete_order.php?id=<?= $order['order_id'] ?>" onclick="return confirm('Yakin hapus?')" class="btn btn-danger btn-sm "><i class="bi bi-trash-fill"></i></a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td class="text-center py-4" colspan="7">
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

  <!-- Modal Detail Order -->
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
          <button type="button" class="btn btn-sm btn-warning text-light" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
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

    function showOrderDetail(order) {
      document.getElementById("modalOrderId").innerText = order.order_id;
      document.getElementById("modalOrderDate").innerText = order.created_at
      document.getElementById("modalOrderTotal").innerText = Number(order.total_amount).toLocaleString('id-ID');

      const status = document.getElementById("modalOrderStatus");
      status.innerText = order.status;
      status.className = "badge";

      if (order.status === 'pending') {
        status.classList.add('bg-info');
      } else if (order.status === 'paid') {
        status.classList.add('bg-success');
      } else {
        status.classList.add('bg-danger');
      }

      const itemList = document.getElementById("modalOrderItems");
      itemList.innerHTML = "";

      if (order.items.length > 0) {
        order.items.forEach(item => {
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

  <?php
  $bootstrap = '../bootstrap/js/bootstrap.bundle.min.js';
  $js = '';
  include '../includes/script.php'
  ?>
</body>

</html>