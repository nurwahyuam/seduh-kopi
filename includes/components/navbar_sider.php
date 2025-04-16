<div id="sidebar" class="w-25 bg-dark">
  <div id="sidebarBig" class="d-flex flex-column align-items-center justify-content-between h-100 p-3">
    <div class="w-100">
      <div class="d-flex align-items-center justify-content-between border-bottom pb-3">
        <div class="d-flex align-items-center gap-3">
          <img src="../assets/logo.png" alt="Logo" width="32" height="32" class="bg-light rounded-5 p-1">
          <span class="fs-3 fw-bold w300 text-light sidebar-title">SeduhKopi</span>
        </div>
        <button onclick="closeBar()" class="btn btn-sm btn-light rounded-2 py-0 px-1">
          <i class="bi bi-caret-left-fill fs-5"></i>
        </button>
      </div>
      <div class="pt-3 d-flex flex-column align-items-center gap-2">
        <?php foreach ($navlink as $item): ?>
          <a class="w-100 px-3 py-2 rounded-2 nav-link <?= ($current_page === $item['file']) ? 'bg-light text-dark link-dark' : 'bg-dark text-light link-secondary' ?>" href="<?= $item['file']; ?>">
            <i class="bi <?= $item['icon']; ?>"></i>
            <span class="ms-2"><?= $item['label']; ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="w-100 border-top pt-3 d-flex flex-column gap-2">
      <a class="px-3 py-2 rounded-2 nav-link nav-link bg-dark text-light link-secondary" href="../includes/logout.php">
        <i class="bi bi-box-arrow-left"></i>
        <span class="ms-2">Logout</span>
      </a>
    </div>
  </div>

  <!-- SIDEBAR MINI -->
  <div id="sidebarSmall" class="d-none d-flex flex-column align-items-center justify-content-between h-100 py-3">
    <div>
      <img src="../assets/logo.png" alt="Logo" width="32" height="32" class="bg-light rounded-5 p-1">
      <div class="d-flex flex-column align-items-center border-1 border-top gap-3 mt-3 pt-2">
        <?php foreach ($navlink as $item): ?>
          <a class="nav-link <?= ($current_page === $item['file']) ? 'bg-light link-dark' : 'bg-dark text-light link-secondary' ?> rounded-2 py-1 px-2" href="<?= $item['file']; ?>">
            <i class="bi <?= $item['icon']; ?>"></i>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="d-flex flex-column gap-3 border-top pt-3">
      <a class="nav-link bg-light link-dark link-secondary rounded-2 py-1 px-2" href="../includes/logout.php">
        <i class="bi bi-box-arrow-left"></i>
      </a>
      <button onclick="openBar()" class="btn btn-sm btn-light rounded-2 py-0 px-1">
        <i class="bi bi-caret-right-fill fs-5"></i>
      </button>
    </div>
  </div>
</div>