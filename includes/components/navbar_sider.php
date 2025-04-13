<?php
  $current_page = basename($_SERVER['PHP_SELF']);
?>
<div id="sidebar" class="sidebar bg-dark text-light d-flex flex-column p-3">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div class="d-flex align-items-center gap-2 me-3">
      <img src="../assets/logo.png" alt="Logo" width="32" height="32" class="me-2 bg-light rounded-5 p-1">
      <span class="fs-3 fw-bold w300 text-light sidebar-title">SeduhKopi</span>
    </div>
  </div>

  <ul class="nav nav-pills flex-column mb-auto">
    <?php foreach ($navlink as $item): ?>
      <li class="py-1">
      <a class="nav-link <?= ($current_page === $item['file']) ? 'bg-light text-dark link-dark' : 'bg-dark text-light link-secondary' ?>" href="<?= $item['file']; ?>">
          <i class="bi <?= $item['icon']; ?>"></i> 
          <span class="ms-2"><?= $item['label']; ?></span>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>

  <div class="mt-auto">
    <hr class="text-white">
    <a href="" class="nav-link text-white mt-3">
      <i class="bi bi-gear"></i> 
      <span class="ms-2">Settings</span>
    </a>
    <a href="../includes/logout.php" class="nav-link text-white mt-3">
      <i class="bi bi-box-arrow-left"></i> 
      <span class="ms-2">Logout</span>
    </a>
  </div>
</div>