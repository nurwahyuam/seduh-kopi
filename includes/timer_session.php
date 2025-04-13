<?php
// Atur waktu sesi dalam detik (contoh: 10 menit = 600 detik)
$timeout = 600; // 10 menit

if (!isset($_SESSION['start_time'])) {
    $_SESSION['start_time'] = time();
} else {
    $elapsed_time = time() - $_SESSION['start_time'];
    if ($elapsed_time > $timeout) {
        session_unset();
        session_destroy();
        header("Location: checkout.php?session=expired");
        exit();
    }
}
