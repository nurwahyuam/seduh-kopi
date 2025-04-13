<footer class="bg-body-tertiary pt-5">
  <div class="container">
    <div class="row text-center mt-3 mb-5">
      <div class="d-flex align-items-center justify-content-center gap-3">
        <img width="100px" height="100px" src="<?= $link; ?>" alt="Logo">
        <h1 class="fw-bold w300" style="font-size: 58px; color:saddlebrown;">SeduhKopi</h1>
      </div>
    </div>
    <div class="row justify-content-center text-center">
      <!-- Quick Links -->
      <div class="col-md-4 mb-5 d-flex flex-column align-items-center">
        <h5 class="fw-bold">Quick Links</h5>
        <ul class="list-unstyled my-1">
          <?php foreach ($footlink as $file => $label): ?>
            <li class="my-1" style="font-size: 13px;"><a href="<?= $file ?>" class="text-dark text-decoration-none"><?= $label ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- Contact Info -->
      <div class="col-md-4 mb-5 d-flex flex-column align-items-center">
        <h5 class="fw-bold">Contact Info</h5>
        <ul class="list-unstyled my-1">
          <li class="my-1" style="font-size: 13px;"><i class="bi bi-telephone-fill me-2"></i>0812-3456-7890 (Kemitraan)</li>
          <li class="my-1" style="font-size: 13px;"><i class="bi bi-chat-left-dots-fill me-2"></i>0819-9876-5432 (Kolaborasi)</li>
          <li class="my-1" style="font-size: 13px;"><i class="bi bi-envelope me-2"></i>kontak@kopiseduh.id</li>
        </ul>
      </div>
    </div>

    <!-- Copyright -->
  </div>
  <div class="bg-dark text-center border-top border-secondary py-3">
    <small class="text-light">Copyright © 2025 Kopi Seduh UMKM by nurwahyuam - All rights reserved.</small>
  </div>
</footer>