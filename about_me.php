<?php
// Memulai sesi untuk menangani login user
session_start();

// Jika user sudah login, arahkan ke halaman sesuai role
if (isset($_SESSION['role'])) {
  if ($_SESSION['role'] === 'admin') {
    header("Location: admin/dashboard.php");
    exit;
  } else {
    header("Location: user/index.php");
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <?php
    // Menentukan properti head (judul, ikon tab, stylesheet, bootstrap)
    $title = 'SeduhKopi';
    $link = 'assets/img/favicon.ico';
    $css = 'css/style.css';
    $bootstrap = 'bootstrap/css/bootstrap.min.css';
    include 'includes/style.php'; // Memasukkan file style.php
    ?>
</head>

<body>
    <?php
    // Navigasi atas: Menentukan logo dan tautan menu navbar
    $link = 'assets/logo.png';
    $navlink = [
        'index.php' => 'Home',
        'login.php' => 'Products',
    ];
    $droplink = [
        'about_me.php' => 'About Me',
        'contact.php' => 'Contact',
    ];
    include 'includes/components/navbar.php'; // Menyisipkan komponen navbar
    ?>

    <!-- Hero Section -->
    <div class="pt-5 text-center">
        <div class="container">
            <!-- Badge dan heading utama -->
            <span class="badge bg-dark text-light fs-6 px-4 py-2 rounded-pill mb-3">Tentang Kami</span>
            <h1 class="fw-bold display-6">Komitmen Kami untuk Rasa Terbaik</h1>
            <p class="text-muted">Menghadirkan secangkir kebahagiaan dari seduhan terbaik UMKM lokal.</p>
        </div>
    </div>

    <!-- Bagian Tentang Kami -->
    <section class="py-5">
        <div class="container">
            <div class="d-flex align-items-center justify-content-center g-5">
                <!-- Gambar/logo -->
                <div class="w-50 d-flex align-items-center justify-content-center">
                    <img src="images/logo.png" alt="Logo SeduhKopi" class="img-fluid rounded-5 shadow">
                </div>
                <!-- Teks cerita -->
                <div class="w-50">
                    <h2 class="fw-bold mb-4">Cerita di Balik SeduhKopi</h2>
                    <p class="text-muted mb-4">
                        SeduhKopi hadir dari kecintaan mendalam terhadap cita rasa kopi Nusantara.
                        Kami percaya, setiap biji kopi yang baik membawa cerita, tradisi, dan semangat dari para petani lokal.
                        Melalui proses seleksi, roasting, hingga penyajian yang penuh dedikasi, kami berkomitmen menghadirkan kopi dengan rasa terbaik untuk Anda.
                    </p>
                    <!-- Tombol menuju halaman login/produk -->
                    <a href="login.php" class="btn btn-dark rounded-pill px-4 py-2 fw-semibold">Belanja Kopi Sekarang</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Bagian Keunggulan Kami -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center fw-bold mb-5 text-dark">Kenapa Memilih SeduhKopi?</h2>
            <div class="row text-center g-4">
                <!-- Kartu 1: Kualitas Premium -->
                <div class="col-md-4">
                    <div class="p-4 bg-dark rounded-4 shadow h-100">
                        <i class="bi bi-cup-hot-fill display-4 text-light mb-3"></i>
                        <h5 class="fw-bold text-light">Kualitas Premium</h5>
                        <p class="text-light fw-lighter">Kami memilih hanya biji kopi terbaik, diproses dengan penuh perhatian untuk menghasilkan rasa yang konsisten dan memuaskan.</p>
                    </div>
                </div>
                <!-- Kartu 2: Pengiriman Cepat -->
                <div class="col-md-4">
                    <div class="p-4 bg-dark rounded-4 shadow h-100">
                        <i class="bi bi-truck display-4 text-light mb-3"></i>
                        <h5 class="fw-bold text-light">Pengiriman Cepat</h5>
                        <p class="text-light fw-lighter">Pesanan Anda akan dikirimkan dengan cepat dan aman, menjaga kesegaran rasa hingga sampai di tangan Anda.</p>
                    </div>
                </div>
                <!-- Kartu 3: Dukungan UMKM -->
                <div class="col-md-4">
                    <div class="p-4 bg-dark rounded-4 shadow h-100">
                        <i class="bi bi-stars display-4 text-light mb-3"></i>
                        <h5 class="fw-bold text-light">Dukungan UMKM Lokal</h5>
                        <p class="text-light fw-lighter">Setiap pembelian Anda berarti dukungan langsung untuk para petani kopi lokal dan pengrajin kopi Indonesia.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php
    $link = "assets/logo.png";
    $footlink = [
        'index.php' => 'Home',
        'about_me.php' => 'About Me',
        'contact.php' => 'Contact',
    ];
    include 'includes/footer.php'; // Menyisipkan file footer
    ?>

    <!-- Script Bootstrap -->
    <?php
    $bootstrap = 'bootstrap/js/bootstrap.bundle.min.js';
    include 'includes/script.php'; // Menyisipkan file JS/script
    ?>
</body>

</html>
