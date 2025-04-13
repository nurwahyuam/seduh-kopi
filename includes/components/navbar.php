<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container">
    <!-- Logo -->
    <a class="navbar-brand d-flex align-items-center me-5" href="index.php">
      <img src="<?= $link; ?>" width="48" height="48" alt="Logo" class="me-2">
      <span class="fw-bold fs-3 w300">SeduhKopi</span>
    </a>
    <div class="d-flex align-items-center gap-3">
      <?php if (isset($_SESSION['role'])) : ?>
        <div class="d-lg-none d-flex align-items-center gap-3">
          <!-- Username -->

          <span class="d-none d-sm-flex d-lg-none text-dark fw-medium"><?= $_SESSION['username']; ?></span>
          <!-- Profile image or icon -->
          <?php if (!empty($_SESSION['profile_photo'])): ?>
            <img width="44" height="44" src="<?= $_SESSION['profile_photo']; ?>" class="d-none d-sm-flex d-lg-none rounded-circle border border-2 border-black" alt="User">
          <?php else: ?>
            <div class="d-none d-sm-flex d-lg-none justify-content-center align-items-center bg-light border border-2 border-secondary rounded-circle" style="width: 48px; height: 48px;">
              <i class="bi bi-person-circle fs-4 text-secondary"></i>
            </div>
          <?php endif; ?>

          <!-- Cart -->
          <button class="nav-link btn d-flex justify-content-center align-items-center bg-light border border-2 border-black rounded-circle position-relative" style="width: 44px; height: 44px;" data-bs-toggle="offcanvas" data-bs-target="#offcanvasKeranjang" aria-controls="offcanvasKeranjang">
            <i class="bi bi-bag-dash fs-4 fw-bold text-dark"><span class="position-absolute top-0 translate-middle badge rounded-pill bg-danger fw-lighter fst-normal" style="font-size: 10px;" id="keranjangCountOne">
                0
                <span class="visually-hidden"></span>
              </span></i>
          </button>

          <!-- Logout button -->
          <a href="../includes/logout.php" class="nav-link">
            <div class="d-flex justify-content-center align-items-center bg-light border border-2 border-black rounded-circle" style="width: 44px; height: 44px;">
              <i class="bi bi-box-arrow-right fs-4 fw-bold text-dark"></i>
            </div>
          </a>
        </div>
      <?php endif; ?>
      <!-- Tombol collapse -->
      <button class="d-lg-none d-flex nav-link btn d-flex justify-content-center align-items-center bg-light border border-2 border-black rounded-circle position-relative" style="width: 44px; height: 44px;" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
        aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
        <i class="bi bi-list fs-4 fw-bold text-dark"></i>
      </button>
    </div>
    <!-- Menu Utama -->
    <div class="collapse navbar-collapse" id="mainNavbar">
      <ul class="d-none d-lg-flex navbar-nav me-auto mb-2 mb-lg-0">
        <?php foreach ($navlink as $file => $label): ?>
          <li class="nav-item">
            <a class="nav-link <?= ($current_page === $file) ? 'active fw-bold' : '' ?>" href="<?= $file ?>">
              <?= $label ?>
            </a>
          </li>
        <?php endforeach; ?>

        <!-- Dropdown -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" id="navbarDropdown" role="button" data-bs-toggle="dropdown"
            aria-expanded="false">
            Links
          </a>
          <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
            <?php foreach ($droplink as $file => $label): ?>
              <li><a class="dropdown-item" href="<?= $file ?>"><?= $label ?></a></li>
            <?php endforeach; ?>
          </ul>
        </li>
      </ul>
      <ul class="d-lg-none d-lg-flex navbar-nav mb-lg-0">
        <?php foreach ($navlink as $file => $label): ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= $file ?>">
              <button type="button" class="btn btn-light <?= ($current_page === $file) ? 'fw-bold' : '' ?>"><?= $label ?></button>
            </a>
          </li>
        <?php endforeach; ?>
        <?php foreach ($droplink as $file => $label): ?>
          <li class="nav-item">
            <a class="nav-link" href="<?= $file ?>">
              <button type="button" class="btn btn-light <?= ($current_page === $file) ? 'fw-bold' : '' ?>"><?= $label ?></button>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>

      <!-- Hanya tampil di layar besar -->
      <?php if(!isset($_SESSION['role']) && !isset($_SESSION['start_time'])): ?>
        <div class="d-none d-lg-flex align-items-center gap-3">
          <a href="login.php" class="nav-link t-primary">
            <button type="button" class="btn btn-outline-dark">Login</button>
          </a>
          <a href="register.php" class="nav-link t-primary">
            <button type="button" class="btn btn-outline-dark">Register</button>
          </a>
        </div>
        <div class="d-lg-none d-lg-flex align-items-center gap-3 ms-1">
          <a href="login.php" class="nav-link">
            <button type="button" class="btn btn-light my-2">Login</button>
          </a>
          <a href="register.php" class="nav-link">
            <button type="button" class="btn btn-light my-2">Register</button>
          </a>
        </div>
      <?php else : ?>
        <?php if (isset($_SESSION['role']) && isset($_SESSION['start_time'])) : ?>
        <div class="d-none d-lg-flex align-items-center gap-3">
          <!-- Username -->
          <span class="text-dark fw-medium"><?= $_SESSION['username']; ?></span>
          <!-- Profile image or icon -->
          <?php if (!empty($_SESSION['profile_photo'])): ?>
            <img width="44" height="44" src="<?= $_SESSION['profile_photo']; ?>" class="rounded-circle border border-2 border-black" alt="User">
          <?php else: ?>
            <div class="d-flex justify-content-center align-items-center bg-light border border-2 border-secondary rounded-circle" style="width: 48px; height: 48px;">
              <i class="bi bi-person-circle fs-4 text-secondary"></i>
            </div>
          <?php endif; ?>

          <!-- Logout button -->
          <a href="../includes/logout.php" class="nav-link">
            <div class="d-flex justify-content-center align-items-center bg-light border border-2 border-black rounded-circle" style="width: 44px; height: 44px;">
              <i class="bi bi-box-arrow-right fs-4 fw-bold text-dark"></i>
            </div>
          </a>
        </div>
        <?php else : ?>
          <div class="d-none d-lg-flex align-items-center gap-3">
          <!-- Username -->
          <span class="text-dark fw-medium"><?= $_SESSION['username']; ?></span>
          <!-- Profile image or icon -->
          <?php if (!empty($_SESSION['profile_photo'])): ?>
            <img width="44" height="44" src="<?= $_SESSION['profile_photo']; ?>" class="rounded-circle border border-2 border-black" alt="User">
          <?php else: ?>
            <div class="d-flex justify-content-center align-items-center bg-light border border-2 border-secondary rounded-circle" style="width: 48px; height: 48px;">
              <i class="bi bi-person-circle fs-4 text-secondary"></i>
            </div>
          <?php endif; ?>

          <!-- Cart -->
          <button class="nav-link btn d-flex justify-content-center align-items-center bg-light border border-2 border-black rounded-circle position-relative" style="width: 44px; height: 44px;" data-bs-toggle="offcanvas" data-bs-target="#offcanvasKeranjang" aria-controls="offcanvasKeranjang">
            <i class="bi bi-bag-dash fs-4 fw-bold text-dark"><span class="position-absolute top-0 translate-middle badge rounded-pill bg-danger fw-lighter fst-normal" style="font-size: 10px;" id="keranjangCountTwo">
                0
                <span class="visually-hidden"></span>
              </span></i>
          </button>

          <!-- Logout button -->
          <a href="../includes/logout.php" class="nav-link">
            <div class="d-flex justify-content-center align-items-center bg-light border border-2 border-black rounded-circle" style="width: 44px; height: 44px;">
              <i class="bi bi-box-arrow-right fs-4 fw-bold text-dark"></i>
            </div>
          </a>
        </div>
      <?php endif; endif; ?>
    </div>
  </div>
</nav>
<!-- END NAVBAR -->