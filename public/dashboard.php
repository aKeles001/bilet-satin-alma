<?php
session_start();
require_once __DIR__ . '/../src/auth.php';
requireLogin();
include 'header.php';
?>

<h2 class="mb-4">Hoşgeldin, <?= htmlspecialchars($_SESSION['full_name']) ?> 👋</h2>

<div class="row g-4">
  <div class="col-md-4">
    <div class="card text-center shadow-sm">
      <div class="card-body">
        <h5 class="card-title">Bakiye</h5>
        <p class="display-6 text-success">₺800</p>
      </div>
    </div>
  </div>
  <div class="col-md-8">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="card-title">Son Biletlerim</h5>
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Sefer</th>
              <th>Tarih</th>
              <th>Durum</th>
            </tr>
          </thead>
          <tbody>
            <tr><td>Ankara → İstanbul</td><td>12 Oct 2025</td><td><span class="badge bg-success">Aktif</span></td></tr>
          </tbody>
        </table>
        <?php if (isAdmin()): ?>
            <div class="mt-3 text-end">
                <a href="/public/admin.php" class="btn btn-danger">Admin Panel</a>
            </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
