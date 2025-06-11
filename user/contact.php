<?php
session_start();

if (!isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit;
} else if ($_SESSION['role'] === 'admin') {
    header("Location: ../admin/dashboard.php");
    exit;
}

include '../database/db.php';

// Ambil data notifikasi
$userId = $_SESSION['id'];
$query = mysqli_query($conn, "SELECT COUNT(*) AS unread FROM notifications WHERE user_id = $userId AND is_read = 0");
$data = mysqli_fetch_assoc($query);
$unreadCount = $data['unread'];

// Ambil data orders
$queryOrders = mysqli_query($conn, "SELECT * FROM orders WHERE user_id = $userId AND status = 'pending' ORDER BY created_at DESC LIMIT 1");
$dataOrders = mysqli_fetch_assoc($queryOrders);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <?php
    $title = 'Contact';
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
        'about_me.php' => 'About Me',
        'contact.php' => 'Contact',
    ];
    include '../includes/components/navbar.php';
    ?>

    <!-- Contact Section -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h1 class="fw-bold text-dark">Hubungi Kami</h1>
                <p class="text-muted">Kami siap membantu Anda! Kirimkan pertanyaan, saran, atau sekadar menyapa kami.</p>
            </div>

            <div class="row g-5 align-items-start">
                <div class="col-md-6">
                    <div class="p-4 bg-white rounded-4 shadow h-100">
                        <h3 class="fw-bold mb-4">Informasi Kontak</h3>
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <i class="bi bi-geo-alt-fill text-dark me-2"></i>
                                <span>Jl. Rungkut Madya, Rungkut, Surabaya</span>
                            </li>
                            <li class="mb-3">
                                <i class="bi bi-telephone-fill text-dark me-2"></i>
                                <span>0812-3456-789</span>
                            </li>
                            <li class="mb-3">
                                <i class="bi bi-envelope-fill text-dark me-2"></i>
                                <span>admin@gmail.com</span>
                            </li>
                        </ul>
                        <p class="mt-4 text-muted">Kami akan membalas pesan Anda sesegera mungkin selama jam kerja Senin - Jumat, pukul 09:00 - 17:00.</p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-4 bg-white rounded-4 shadow">
                        <h3 class="fw-bold mb-4">Formulir Pesan</h3>
                        <!-- Form akan diproses di proses_pesan.php -->
                        <form action="../includes/proses_pesan_user.php" method="POST">
                            <!-- Baris Nama (First Name + Last Name) -->
                            <div class="row mb-3">
                                <div class="col">
                                    <input type="text" name="first_name" class="form-control" placeholder="First Name" required>
                                </div>
                                <div class="col">
                                    <input type="text" name="last_name" class="form-control" placeholder="Last Name" required>
                                </div>
                            </div>
                            <!-- Input Email -->
                            <div class="mb-3">
                                <input type="email" name="email" class="form-control" placeholder="Email" required>
                            </div>
                            <!-- Input Nomor Telepon -->
                            <div class="mb-3">
                                <input type="text" name="phone" class="form-control" placeholder="Phone Number">
                            </div>
                            <!-- Textarea Pesan -->
                            <div class="mb-3">
                                <textarea name="message" class="form-control" rows="4" placeholder="Message" required></textarea>
                            </div>
                            <!-- Tombol Submit -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-dark fw-semibold">Kirim Pesan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- TOAST NOTIFICATION -->
    <?php include '../includes/components/toast.php' ?>

    <?php
    $link = "../assets/logo.png";
    $footlink = [
        'index.php' => 'Home',
        'about_me.php' => 'About Me',
        'contact.php' => 'Contact',
    ];
    include '../includes/footer.php';
    ?>

    <!-- SCRIPT UNTUK TOAST NOTIFICATION -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Cek dan tampilkan pesan toast biasa
            const toastMsg = sessionStorage.getItem("toastMessage");
            if (toastMsg) {
                document.getElementById("toastMessage").innerText = toastMsg;
                new bootstrap.Toast(document.getElementById("liveToast")).show();
                sessionStorage.removeItem("toastMessage");
            }

            // Cek dan tampilkan pesan toast untuk delete
            const toastDeleteMsg = sessionStorage.getItem("toastMessageDelete");
            if (toastDeleteMsg) {
                document.getElementById("toastMessageDelete").innerText = toastDeleteMsg;
                new bootstrap.Toast(document.getElementById("liveToastDelete")).show();
                sessionStorage.removeItem("toastMessageDelete");
            }
        });
    </script>

    <?php
    $bootstrap = '../bootstrap/js/bootstrap.bundle.min.js';
    include '../includes/script.php';
    ?>
</body>

</html>