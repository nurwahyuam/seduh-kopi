<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - Kopi Kami</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #121212;
            color: #e0e0e0;
            position: relative;
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .content-wrapper {
            position: relative;
            max-width: 1200px;
            padding: 3rem;
            background-color: rgba(30, 30, 30, 0.8);
            border-radius: 40px;
            min-height: 90vh;
            z-index: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .layer-border {
            position: absolute;
            top: 20px;
            left: 20px;
            right: 20px;
            bottom: 20px;
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            z-index: 0;
        }
        h1 {
            font-weight: 700;
            color: #f5f5f5;
            font-size: 2.5rem;
        }
        h5 {
            font-size: 2rem;
            text-align: center;
            color: #ffcc00;
            letter-spacing: 1px;
        }
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 10px;
        }
        p.text-muted {
            padding: 15px 20px;
            margin: 5px 0;
            line-height: 1.6;
            color: #b0b0b0 !important;
        }
        img {
            border-radius: 15px;
            box-shadow: 0 0 30px rgba(0,0,0,0.5);
            width: 100%;
            object-fit: cover;
        }
        .feature-icon-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
        }
        .feature-item {
            flex: 1 1 300px;
            text-align: center;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>

<div class="layer-border"></div>

<div class="content-wrapper">
    <div class="row align-items-center">
        <div class="col-md-6 mb-4 mb-md-0">
            <h1>Menyajikan Kualitas, Mengutamakan Kepuasan</h1>
            <p class="text-muted" style="margin-left: -18px;">
                Kami hadir dengan komitmen untuk menyajikan produk dan layanan terbaik bagi Anda.
                Setiap proses kami rancang secara detail, mulai dari pemilihan bahan baku unggulan hingga pengemasan,
                demi memastikan setiap produk yang Anda terima mencerminkan kualitas dan profesionalisme.
                Kepuasan Anda adalah prioritas kami.
            </p>
        </div>
        <div class="col-md-6">
        <img src="../images/user/1744693957_carousel3.jpg" alt="Meja Kopi" class="img-fluid">
        </div>
    </div>

    <div class="feature-icon-container mt-5">
        <div class="feature-item">
        <div class="feature-icon"><i class="bi bi-truck"></i></div>
            <h5 class="fw-bold text-light">Pengiriman Kopi ke Seluruh Indonesia</h5>
            <p class="text-muted">
            Kami mengerti, kualitas tak boleh terhalang jarak.
            Produk kopi pilihan kami siap dikirim ke berbagai penjuru negeri dengan layanan pengiriman yang efisien, aman, dan terpercaya. Kami pastikan aroma dan rasa tetap terjaga hingga sampai di meja Anda, di mana pun berada.
            </p>
        </div>
        <div class="feature-item">
        <div class="feature-icon"><i class="bi bi-cup-hot"></i></div>
            <h5 class="fw-bold text-light">Jaminan Kualitas Rasa</h5>
            <p class="text-muted">
            Kepercayaan Anda adalah bagian dari racikan kami.
            Setiap produk yang kami kirim telah melewati proses quality control yang ketat. Jika kopi yang Anda terima tidak sesuai dengan standar mutu yang kami janjikan, kami siap melakukan penggantian produk tanpa keraguan. Kami menghargai setiap momen yang Anda habiskan bersama kopi kami.
            </p>
        </div>
        <div class="feature-item">
        <div class="feature-icon"><i class="bi bi-ticket-perforated"></i></div>
            <h5 class="fw-bold text-light">Program Loyalty</h5>
            <p class="text-muted">
            Setiap cangkir punya arti.
            Melalui program loyalty kami, setiap pembelian produk kopi akan mendapatkan poin yang bisa ditukar dengan potongan harga atau hadiah eksklusif dari kami.
            Karena kami percaya, kopi terbaik layak dinikmati lebih dari sekali.
            </p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
