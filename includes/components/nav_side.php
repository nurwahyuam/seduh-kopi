<div class="d-flex align-items-center gap-3 mb-4">
  <div class="dropdown">
    <button class="nav-link btn d-flex justify-content-center align-items-center bg-light border border-2 border-black rounded-circle position-relative" style="width: 44px; height: 44px;" id="notifButton" data-bs-toggle="dropdown" aria-expanded="false">
      <div class="d-flex align-items-center justify-content-center"
        style="width: 45px; height: 45px;">
        <i class="bi bi-bell-fill fs-4 fw-bold text-dark">
          <?php if ($unreadCount > 0): ?>
            <span class="position-absolute top-0 translate-middle badge rounded-pill bg-danger fw-lighter fst-normal" style="font-size: 10px;">
              <?= $unreadCount  ?>
              <span class="visually-hidden"></span>
            </span>
          <?php endif; ?>
        </i>
      </div>
    </button>
    <ul class="dropdown-menu dropdown-menu-end mt-2">
      <?php
      $notifQuery = mysqli_query($conn, "SELECT * FROM notifications WHERE user_id = $userId ORDER BY created_at DESC LIMIT 5");
      while ($row = mysqli_fetch_assoc($notifQuery)) {
        echo '<li class="dropdown-item fs-6' . ($row['is_read'] == 0 ? ' fw-bold' : '') . '">' .
          $row['message'] . '<br><small class="text-muted">' . $row['created_at'] . '</small></li>';
      }
      ?>
    </ul>
  </div>

  <!-- Foto Profil -->
  <?php if ($_SESSION['profile_photo'] !== NULL): ?>
    <img width="45px" height="45px" src="../images/user/<?= $_SESSION['profile_photo']; ?>" class="rounded-circle border border-dark border-2 p-1" style="object-fit: cover;" alt="User">
  <?php else: ?>
    <i class="bi bi-person-circle fs-2"></i>
  <?php endif; ?>

  <!-- Username -->
  <span class="fw-semibold text-capitalize"><?= $_SESSION['username']; ?></span>
</div>