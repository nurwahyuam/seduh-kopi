<?php
session_start();

if (!isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit;
} else if ($_SESSION['role'] === 'admin') {
    header("Location: ../admin/dashboard.php");
    exit;
} else if (!isset($_SESSION['start_time'])) {
    header("Location: ../user/index.php");
    exit;
}

require_once '../database/db.php';

// Ambil order ID dari URL
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($order_id < 1) {
    echo "ID order tidak valid";
    exit;
}

// Ambil data order
$orderQuery = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$orderQuery->bind_param("i", $order_id);
$orderQuery->execute();
$orderResult = $orderQuery->get_result();

if ($orderResult->num_rows === 0) {
    echo "Order tidak ditemukan.";
    exit;
}

$order = $orderResult->fetch_assoc();
$timeout = 600;
$date = new DateTime($order['created_at']);
$user_id = $_SESSION['id'];
$notif = "Mohon Maaf, Order #$order_id telah Gagal dipembayaran. Mohon Checkout Ulang";

if (!isset($_SESSION['start_time'])) {
    $_SESSION['start_time'] = time();
} else {
    $elapsed_time = time() - $_SESSION['start_time'];
    if ($elapsed_time > $timeout) {
        $updateQuery = $conn->prepare("UPDATE orders SET status = 'expired' WHERE id = ?");
        $updateQuery->bind_param("i", $order_id);
        $updateQuery->execute();
        $addQuery = $conn->prepare("INSERT INTO notifications (user_id, message, is_read, created_at) VALUES (?, ?, 0, NOW())");
        $addQuery->bind_param("is", $user_id, $notif);
        $addQuery->execute();
        unset($_SESSION['start_time']);
        echo "<script>sessionStorage.setItem('toastMessageDelete', 'Produk berhasil dihapuskan!'); window.location.href = 'product.php?session=expired';</script>";
        header("Location: product.php?session=expired");
        exit();
    }
}

// Ambil item dalam order
$itemQuery = $conn->prepare("
    SELECT 
    oi.id AS order_item_id,
    oi.order_id,
    oi.product_id,
    oi.quantity,
    oi.price,
    oi.subtotal,
    
    p.name AS product_name,
    p.image AS product_image,
    
    o.user_id,
    o.status,
    o.total_amount,
    o.created_at

FROM 
    order_items oi
JOIN 
    products p ON oi.product_id = p.id
JOIN 
    orders o ON oi.order_id = o.id
WHERE 
    oi.order_id = ?;
");
$orderId = $_GET['order_id'];
$itemQuery->bind_param("i", $orderId);
$itemQuery->execute();
$result = $itemQuery->get_result();

$paymentsMethodsQuery = $conn->prepare("SELECT * FROM payment_methods");
$paymentsMethodsQuery->execute();
$paymentsMethodsResult = $paymentsMethodsQuery->get_result();

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
    $title = 'Payment';
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
        '../about_me.php' => 'About Me',
        '../contact.php' => 'Contact',
    ];
    include '../includes/components/navbar.php';
    ?>

    <div class="container py-5">
        <form action="../includes/transaction.php" method="POST" enctype="multipart/form-data" class="row g-5">
            <input type="hidden" id="order_id" name="order_id" value="<?= $order['id'] ?>">
            <input type="hidden" id="amount" name="amount" value="<?= $order['total_amount'] ?>">
            <!-- LEFT SIDE -->
            <div class="col-md-6">
                <h4 class="mb-3 fw-semibold">Contact Information</h4>
                <div class="mb-3">
                    <label for="phone_number">No Telephone</label>
                    <input type="number" class="form-control focus-ring focus-ring-dark border-dark" id="phone_number" name="phone_number" required>
                </div>
                <h4 class="mt-4 mb-3 fw-semibold">Shipping Information</h4>
                <div class="mb-3">
                    <label for="name">Name</label>
                    <input type="text" class="form-control focus-ring focus-ring-dark border-dark" id="name" name="name" value="Custumer <?= htmlspecialchars($_SESSION['username']); ?>" required disabled>
                </div>
                <div class=" mb-3">
                    <label for="address">Address</label>
                    <input type="text" class="form-control focus-ring focus-ring-dark border-dark" id="address" name="address" required>
                </div>
                <h4 class="mt-4 mb-3 fw-semibold">Payment Information</h4>
                <div class="mb-3">
                    <label for="payment_methods">Methods</label>
                    <select name="payment_methods" class="form-select focus-ring focus-ring-dark border-dark" id="payment_methods">
                        <?php foreach ($paymentsMethodsResult as $pay) :
                            if ($pay['method_name'] != 'Cash') : ?>
                                <option value="<?= $pay['id']; ?>"><?= $pay["method_name"]; ?> | <?= $pay["acc_number"] == NULL ? 'Cannot' : $pay["acc_number"]; ?></option>
                        <?php endif;
                        endforeach; ?>
                    </select>
                </div>
                <div class="form-group my-2">
                    <label for="payment_proof">Proof of Payment</label>
                    <input type="file" class="form-control focus-ring focus-ring-dark border-dark" id="payment_proof" name="payment_proof" accept="image/*" required>
                    <div class="form-text" id="basic-addon4">Noted: Adjust to the payment method you choose and do not duplicate or edit.</div>
                </div>
            </div>

            <!-- RIGHT SIDE -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <h4 class="mb-4">Order summary</h4>
                            <p><?= $date->format('d F Y - H:i:s'); ?></p>
                        </div>
                        <div class=" overflow-y-auto" style="height: 450px;">
                            <?php while ($item = $result->fetch_assoc()): ?>
                                <div class="d-flex justify-content-between align-items-start mb-3 border-bottom pb-2">
                                    <div class="d-flex align-items-center gap-3">
                                        <img width="48px" height="48px" class="rounded-3" src="../images/product/<?= htmlspecialchars($item['product_image']) ?>" alt="<?= htmlspecialchars($item['product_name']) ?>">
                                        <div>
                                            <strong><?= htmlspecialchars($item['product_name']) ?></strong><br>
                                            <small>Quantity: <?= htmlspecialchars($item['quantity']) ?></small>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="m-0 text-left">Rp.<?= number_format($item['price'], 2, ',', '.') ?></p>
                                        <p class="m-0 fw-semibold text-left">Rp.<?= number_format($item['price'] * $item['quantity'], 2, ',', '.') ?></p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>

                        <ul class="list-unstyled">
                            <li class="d-flex justify-content-between">
                                <span>Subtotal</span>
                                <span class="fw-bold">Rp.<?= number_format($order['total_amount'], 2, ',', '.') ?></span>
                            </li>
                        </ul>
                        <hr>
                        <button type="submit" class="btn btn-primary w-100">Confirm order</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Toast/Pesan Sementara -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div id="liveToast" class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage">
                    <!-- Pesan akan ditampilkan di sini -->
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>


    <?php
    $footlink = [
        'index.php' => 'Home',
        'about_me.php' => 'About Me',
        'contact.php' => 'Contact',
    ];
    include '../includes/footer.php';
    ?>

    <?php
    $bootstrap = '../bootstrap/js/bootstrap.bundle.min.js';
    $js = '';
    include '../includes/script.php';
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
    <script>
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
    </script>
</body>

</html>