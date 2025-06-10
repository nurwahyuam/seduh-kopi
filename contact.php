<?php
// Memulai session untuk mengelola state pengguna
session_start();

// Cek jika pengguna sudah login (role terdefinisi)
if (isset($_SESSION['role'])) {
    // Redirect admin ke dashboard admin
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
        exit;
    } else {
        // Redirect user biasa ke halaman user
        header("Location: user/index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <!-- HEAD: Pengaturan halaman -->
    <?php
    $title = 'SeduhKopi - Hubungi Kami'; // Judul halaman spesifik
    $link = 'assets/img/favicon.ico'; // Icon browser
    $css = 'css/style.css'; // CSS custom
    $bootstrap = 'bootstrap/css/bootstrap.min.css'; // CSS Bootstrap
    include 'includes/style.php'; // Include file style
    ?>
</head>

<body>
    <!-- NAVBAR -->
    <?php
    $link = 'assets/logo.png'; // Logo navbar
    // Link menu navbar utama
    $navlink = [
        'index.php' => 'Home',
        'login.php' => 'Products',
    ];
    // Link dropdown menu
    $droplink = [
        'about_me.php' => 'About Me',
        'contact.php' => 'Contact',
    ];
    include 'includes/components/navbar.php'; // Include navbar
    ?>

    <!-- CONTACT SECTION -->
    <section class="py-5">
        <div class="container">
            <!-- Header Section -->
            <div class="text-center mb-5">
                <h1 class="fw-bold text-dark">Hubungi Kami</h1>
                <p class="text-muted">Kami siap membantu Anda! Kirimkan pertanyaan, saran, atau sekadar menyapa kami.</p>
            </div>

            <!-- Konten Kontak (2 kolom) -->
            <div class="row g-5 align-items-start">
                <!-- Kolom Informasi Kontak -->
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

                <!-- Kolom Formulir Kontak -->
                <div class="col-md-6">
                    <div class="p-4 bg-white rounded-4 shadow">
                        <h3 class="fw-bold mb-4">Formulir Pesan</h3>
                        <!-- Form akan diproses di proses_pesan.php -->
                        <form action="includes/proses_pesan.php" method="POST">
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
    <?php include 'includes/components/toast.php' ?>

    <!-- FOOTER -->
    <?php
    $link = "assets/logo.png"; // Logo footer
    // Link menu footer
    $footlink = [
        'index.php' => 'Home',
        'about_me.php' => 'About Me',
        'contact.php' => 'Contact',
    ];
    include 'includes/footer.php'; // Include footer
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

    <!-- INCLUDE SCRIPT JS -->
    <?php
    $bootstrap = 'bootstrap/js/bootstrap.bundle.min.js'; // JS Bootstrap
    include 'includes/script.php'; // Include scripts
    ?>

</body>

</html>