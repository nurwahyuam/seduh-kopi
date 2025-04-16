<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container">
    <!-- Logo -->
    <a class="navbar-brand d-sm-none align-items-center" href="index.php">
      <img src="<?= $link; ?>" width="32" height="32" alt="Logo" class="me-2">
      <span class="fw-bold fs-5 w300 fs">SeduhKopi</span>
    </a>
    <a class="navbar-brand d-none d-sm-flex align-items-center" href="index.php">
      <img src="<?= $link; ?>" width="48" height="48" alt="Logo" class="me-2">
      <span class="fw-bold fs-3 w300 fs">SeduhKopi</span>
    </a>
    <div class="d-flex align-items-center gap-1">
      <?php if (isset($_SESSION['role'])) : ?>
        <div class="d-lg-none d-flex align-items-center gap-1">
          <!-- Username -->
          <span class="d-none d-sm-flex d-lg-none text-dark fw-medium"><?= $_SESSION['username']; ?></span>
          <!-- Profile image or icon -->
          <?php if (!empty($_SESSION['profile_photo'])): ?>
            <img width="40" height="40" src="../images/user/<?= $_SESSION['profile_photo']; ?>" class="d-none d-sm-flex d-lg-none rounded-circle border border-2 border-black" alt="User">
          <?php else: ?>
            <div class="d-none d-sm-flex d-lg-none justify-content-center align-items-center bg-light border border-2 border-dark rounded-circle" style="width: 40px; height: 40px;">
              <i class="bi bi-person-circle fs-4 text-dark"></i>
            </div>
          <?php endif; ?>

          <!-- Cart -->
          <button class="nav-link btn d-flex justify-content-center align-items-center bg-light border border-2 border-black rounded-circle position-relative" style="width: 40px; height: 40px;" data-bs-toggle="offcanvas" data-bs-target="#offcanvasKeranjang" aria-controls="offcanvasKeranjang">
            <i class="bi bi-bag-dash-fill fs-4 fw-bold text-dark"><span class="position-absolute top-0 translate-middle badge rounded-pill bg-danger fw-lighter fst-normal" style="font-size: 10px;" id="keranjangCountOne">
                0
                <span class="visually-hidden"></span>
              </span></i>
          </button>

          <!-- Notifikasi -->
          <div class="dropdown">
            <button class="nav-link btn d-flex justify-content-center align-items-center bg-light border border-2 border-black rounded-circle position-relative" style="width: 40px; height: 40px;" id="notifBtn" data-bs-toggle="dropdown" aria-expanded="false">
              <div class="d-flex align-items-center justify-content-center"
                style="width: 44px; height: 44px;">
                <i class="bi bi-bell-fill fs-4 fw-bold text-dark">
                  <?php if ($unreadCount > 0): ?>
                    <span id="notifCount" class="position-absolute top-0 translate-middle badge rounded-pill bg-danger fw-lighter fst-normal" style="font-size: 10px;">
                      <?= $unreadCount  ?>
                      <span class="visually-hidden"></span>
                    </span>
                  <?php endif; ?>
                </i>
              </div>
            </button>
            <ul class="dropdown-menu dropdown-menu-end mt-2 p-0" style="width: 250px;">
              <li>
                <hr class="dropdown-divider">
              </li>
              <?php
              $notifQuery = mysqli_query($conn, "SELECT * FROM notifications WHERE user_id = $userId ORDER BY created_at DESC LIMIT 3");
              while ($row = mysqli_fetch_assoc($notifQuery)) {
                echo '<li class="dropdown-item text-capitalize text-wrap' . ($row['is_read'] == 0 ? ' fw-bold' : '') . '">' .
                  $row['message'] . '<br><small class="text-muted">' . $row['created_at'] . '</small></li><li><hr class="dropdown-divider"></li>';
              }
              ?>
            </ul>
          </div>
        </div>
      <?php endif; ?>
      <!-- Tombol collapse -->
      <button class="d-lg-none d-flex nav-link btn d-flex justify-content-center align-items-center bg-light border border-2 border-black rounded-circle position-relative" style="width: 40px; height: 40px;" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
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
          <li class="nav-item w-100">
            <a class="nav-link w-100" href="<?= $file ?>">
              <button type="button" class="btn btn-light w-100 text-start <?= ($current_page === $file) ? 'fw-bold' : '' ?>"><?= $label ?></button>
            </a>
          </li>
        <?php endforeach; ?>
        <?php foreach ($droplink as $file => $label): ?>
          <li class="nav-item w-100">
            <a class="nav-link w-100" href="<?= $file ?>">
              <button type="button" class="btn btn-light w-100 text-start <?= ($current_page === $file) ? 'fw-bold' : '' ?>"><?= $label ?></button>
            </a>
          </li>
        <?php endforeach; ?>
        <li class="nav-item">
          <a class="nav-link" href="../includes/logout.php">
            <button type="button" class="btn btn-light">Logout</button>
          </a>
        </li>
      </ul>

      <!-- Hanya tampil di layar besar -->
      <?php if (!isset($_SESSION['role']) && !isset($_SESSION['start_time'])): ?>
        <div class="d-none d-lg-flex align-items-center gap-3">
          <a href="login.php" class="nav-link">
            <button type="button" class="btn btn-outline-dark">Login</button>
          </a>
          <a href="register.php" class="nav-link">
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
              <img width="44" height="44" src="../images/user/<?= $_SESSION['profile_photo']; ?>" class="rounded-circle border border-2 border-black" alt="User">
            <?php else: ?>
              <div class="d-flex justify-content-center align-items-center bg-light border border-2 border-secondary rounded-circle" style="width: 44px; height: 44px;">
                <i class="bi bi-person-circle fs-4 text-dark"></i>
              </div>
            <?php endif; ?>

            <!-- Orders -->
            <a href="payment.php?order_id=<?= $dataOrders['id'] ?>" class="nav-link">
              <div class="d-flex justify-content-center align-items-center bg-light border border-2 border-black rounded-circle" style="width: 44px; height: 44px;">
                <i class="bi bi-clipboard2-fill fs-4 fw-bold text-dark"></i>
              </div>
            </a>


            <!-- Logout button -->
            <a href="../includes/logout.php" class="nav-link">
              <div class="d-flex justify-content-center align-items-center bg-light border border-2 border-black rounded-circle" style="width: 44px; height: 44px;">
                <i class="bi bi-door-open-fill fs-4 fw-bold text-dark"></i>
              </div>
            </a>
          </div>
        <?php else : ?>
          <div class="d-none d-lg-flex align-items-center gap-3">
            <!-- Username -->
            <span class="text-dark fw-medium"><?= $_SESSION['username']; ?></span>
            <!-- Profile image or icon -->
            <?php if (!empty($_SESSION['profile_photo'])): ?>
              <img width="44" height="44" src="../images/user/<?= $_SESSION['profile_photo']; ?>" class="rounded-circle border border-2 border-black" alt="User">
            <?php else: ?>
              <div class="d-flex justify-content-center align-items-center bg-light border border-2 border-secondary rounded-circle" style="width: 44px; height: 44px;">
                <i class="bi bi-person-circle fs-4 text-secondary"></i>
              </div>
            <?php endif; ?>

            <!-- Notifikasi -->
            <div class="dropdown">
              <button class="nav-link btn d-flex justify-content-center align-items-center bg-light border border-2 border-black rounded-circle position-relative" style="width: 44px; height: 44px;" id="notifBtn" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="d-flex align-items-center justify-content-center"
                  style="width: 44px; height: 44px;">
                  <i class="bi bi-bell-fill fs-4 fw-bold text-dark">
                    <?php if ($unreadCount > 0): ?>
                      <span id="notifCount" class="position-absolute top-0 translate-middle badge rounded-pill bg-danger fw-lighter fst-normal" style="font-size: 10px;">
                        <?= $unreadCount  ?>
                        <span class="visually-hidden"></span>
                      </span>
                    <?php endif; ?>
                  </i>
                </div>
              </button>
              <ul class="dropdown-menu dropdown-menu-end mt-2 p-0">
                <li>
                  <hr class="dropdown-divider">
                </li>
                <?php
                $notifQuery = mysqli_query($conn, "SELECT * FROM notifications WHERE user_id = $userId ORDER BY created_at DESC LIMIT 5");
                while ($row = mysqli_fetch_assoc($notifQuery)) {
                  echo '<li class="dropdown-item text-capitalize' . ($row['is_read'] == 0 ? ' fw-bold' : '') . '">' .
                    $row['message'] . '<br><small class="text-muted">' . $row['created_at'] . '</small></li><li><hr class="dropdown-divider"></li>';
                }
                ?>
              </ul>
            </div>

            <!-- Cart -->
            <button class="nav-link btn d-flex justify-content-center align-items-center bg-light border border-2 border-black rounded-circle position-relative" style="width: 44px; height: 44px;" data-bs-toggle="offcanvas" data-bs-target="#offcanvasKeranjang" aria-controls="offcanvasKeranjang">
              <i class="bi bi-bag-dash-fill fs-4 fw-bold text-dark"><span class="position-absolute top-0 translate-middle badge rounded-pill bg-danger fw-lighter fst-normal" style="font-size: 10px;" id="keranjangCountTwo">
                  0
                  <span class="visually-hidden"></span>
                </span></i>
            </button>

            <!-- Logout button -->
            <a href="../includes/logout.php" class="nav-link">
              <div class="d-flex justify-content-center align-items-center bg-light border border-2 border-black rounded-circle" style="width: 44px; height: 44px;">
                <i class="bi bi-door-open-fill fs-4 fw-bold text-dark"></i>
              </div>
            </a>
          </div>
      <?php endif;
      endif; ?>
    </div>
  </div>
</nav>
<!-- END NAVBAR -->