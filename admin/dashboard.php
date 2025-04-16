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

$userId = $_SESSION['id'];
$query = mysqli_query($conn, "SELECT COUNT(*) AS unread FROM notifications WHERE user_id = $userId AND is_read = 0");
$data = mysqli_fetch_assoc($query);
$unreadCount = $data['unread'];

// Statistik
$total_users = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$total_products = $conn->query("SELECT COUNT(*) AS total FROM products")->fetch_assoc()['total'];
$total_orders = $conn->query("SELECT COUNT(DISTINCT id) AS total FROM orders")->fetch_assoc()['total'];

$total_income_query = mysqli_query($conn, "SELECT SUM(amount) AS income FROM transactions WHERE status = 'completed'");

$total_income_data = mysqli_fetch_assoc($total_income_query);
$total_income = $total_income_data['income'] ?? 0;
$monthly_income_query = mysqli_query($conn, "
  SELECT DATE_FORMAT(transaction_date, '%M') AS month, SUM(amount) AS total 
  FROM transactions 
  WHERE status = 'completed' 
  GROUP BY MONTH(transaction_date)
  ORDER BY MONTH(transaction_date)
");

$months = [];
$incomes = [];

while ($row = mysqli_fetch_assoc($monthly_income_query)) {
  $months[] = $row['month'];
  $incomes[] = $row['total'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <?php
  $title = 'Dashboard';
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
      ['file' => 'dashboard.php', 'label' => 'Home', 'icon' => 'bi-house-fill'],
      ['file' => 'product.php', 'label' => 'Products', 'icon' => 'bi-box-seam-fill'],
      ['file' => 'user.php', 'label' => 'Users', 'icon' => 'bi-people-fill'],
      ['file' => 'order.php', 'label' => 'Orders', 'icon' => 'bi-bag-dash-fill'],
      ['file' => 'transaction.php', 'label' => 'Transactions', 'icon' => 'bi-wallet-fill'],
      ['file' => 'carousel.php', 'label' => 'Carousels', 'icon' => 'bi-image-fill'],
      ['file' => 'payment_method.php', 'label' => 'Payment Methods', 'icon' => 'bi-credit-card-fill'],
    ];
    include '../includes/components/navbar_sider.php';
    ?>

    <div id="box" class="w-100 bg-light py-3 px-4 overflow-auto">
      <?php include '../includes/components/nav_side.php'; ?>

      <div class="card shadow border-0 p-3">
        <h4 class="fw-bold text-end p-3">Welcome, Admin </h4>
        <div class="row g-4 mb-4 px-3">
          <div class="col-md-6">
            <div class="card border-start border-primary border-4 shadow-sm">
              <div class="card-body">
                <h6 class="text-muted">Total Users</h6>
                <h3><?= $total_users ?></h3>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card border-start border-success border-4 shadow-sm">
              <div class="card-body">
                <h6 class="text-muted">Total Products</h6>
                <h3><?= $total_products ?></h3>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card border-start border-warning border-4 shadow-sm">
              <div class="card-body">
                <h6 class="text-muted">Total Orders</h6>
                <h3><?= $total_orders ?></h3>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card border-start border-danger border-4 shadow-sm">
              <div class="card-body">
                <h6 class="text-muted">Total Pendapatan</h6>
                <h3>Rp <?= number_format($total_income, 0, ',', '.') ?></h3>
              </div>
            </div>
          </div>

        </div>
        <div class="card-body text-center text-muted">
          <div class="card-header bg-primary text-white">Pendapatan Bulanan</div>
          <div class="card-body">
            <canvas id="incomeChart" height="100"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include '../includes/components/toast.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    const ctx = document.getElementById('incomeChart').getContext('2d');
    const incomeChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: <?= json_encode($months) ?>,
        datasets: [{
          label: 'Pendapatan',
          data: <?= json_encode($incomes) ?>,
          backgroundColor: 'rgba(54, 162, 235, 0.7)',
          borderColor: 'rgba(54, 162, 235, 1)',
          borderWidth: 1,
          borderRadius: 5
        }]
      },
      options: {
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                return 'Rp ' + value.toLocaleString('id-ID');
              }
            }
          }
        }
      }
    });
    document.getElementById('notifButton')?.addEventListener('click', function() {
      fetch('../includes/mark_read.php')
        .then(res => res.json())
        .then(data => {
          if (data.status === "success") {
            document.querySelector('.badge.bg-danger')?.remove();
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
  </script>

  <?php
  $bootstrap = '../bootstrap/js/bootstrap.bundle.min.js';
  $js = "";
  include '../includes/script.php';
  ?>
</body>

</html>