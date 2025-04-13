  <!-- Offcanvas Keranjang -->
  <div style="width: 60vh;" class="offcanvas offcanvas-start bg-dark text-light px-3" tabindex="-1" id="offcanvasKeranjang" aria-labelledby="offcanvasKeranjangLabel" data-bs-scroll="true">
    <div class="offcanvas-header d-flex align-items-center">
      <div class="d-flex align-items-center gap-3">
        <img src="../assets/logo.png" width="48" height="48" alt="Logo" class="bg-light rounded-circle p-1">
        <h1 id="offcanvasKeranjangLabel" class="fw-bold fs-2 w300 m-0">SeduhKopi</h1>
      </div>
      <button type="button" class="btn-close bg-light" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <hr class="m-0">
    <div class="offcanvas-body">
      <ul id="keranjangItems" class="list-group"></ul>
      <div class="mt-3" id="bodyTotalHarga"></div>
      <div id="boxButton" class="w-100 gap-2 d-flex align-items-center"></div>
    </div>
  </div>