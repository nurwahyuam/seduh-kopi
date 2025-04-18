<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Get in Touch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #0d1117;
            color: #c9d1d9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .contact-container {
            background: #161b22;
            border-radius: 15px;
            padding: 30px;
            max-width: 900px;
            width: 100%;
            box-shadow: 0 0 25px rgba(0,0,0,0.4);
        }
        .form-control, .form-control:focus {
            background-color: #0d1117;
            border: 1px solid #30363d;
            color: #c9d1d9;
        }
        .btn-primary {
            background-color: #4f46e5;
            border: none;
        }
        .btn-primary:hover {
            background-color: #4338ca;
        }
    </style>
</head>
<body>

<div class="contact-container row g-4">
    <div class="col-md-6">
        <h2>Hubungi Kami</h2>
        <p>Kami siap membantu Anda! Hubungi kami untuk pertanyaan, kerja sama, atau sekadar menyapa. Tim kami akan dengan senang hati membalas pesan Anda secepat mungkin.</p>
        <ul class="list-unstyled">
            <li><i class="bi bi-geo-alt"></i> Jl. Rungkut Madya, Rungkut, Surabaya</li>
            <li><i class="bi bi-telephone"></i> 08123456789</li>
            <li><i class="bi bi-envelope"></i> admin@gmail.com</li>
        </ul>
    </div>
    <div class="col-md-6">
        <form action="proses.php" method="POST">
            <div class="row mb-3">
                <div class="col">
                    <input type="text" name="first_name" class="form-control" placeholder="First name" required>
                </div>
                <div class="col">
                    <input type="text" name="last_name" class="form-control" placeholder="Last name" required>
                </div>
            </div>
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>
            <div class="mb-3">
                <input type="text" name="phone" class="form-control" placeholder="Phone number">
            </div>
            <div class="mb-3">
                <textarea name="message" class="form-control" rows="4" placeholder="Message" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Send message</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.js"></script>
</body>
</html>
