<?php
session_start();
require_once __DIR__ . '/../src/auth.php';
requireLogin();
include 'header.php';

$user_id = $_SESSION['user_id'];

// Fetch balance

$user = get_user_balance($user_id);
$balance = $user ? $user['balance'] : 0;
$full_name = $user ? $user['full_name'] : '';
// Fetch tickets
$tickets = get_user_tickets($user_id, 20);
?>
<div class="main-content">
  <div class="container">
  <h2 class="mb-4">Hoşgeldin, <?= htmlspecialchars($full_name) ?> 👋</h2>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="card text-center shadow-sm">
          <div class="card-body">
            <h5 class="card-title">Bakiye</h5>
            <p class="display-6 text-success">₺<?= htmlspecialchars(number_format($balance, 2)) ?></p>
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
                  <th>Saat</th>
                  <th>Durum</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($tickets)): ?>
                  <tr><td colspan="3" class="text-center">Kayıtlı bilet bulunamadı.</td></tr>
                <?php else: ?>
                  <?php foreach ($tickets as $ticket): ?>
                    <tr>
                      <td><?= htmlspecialchars($ticket['departure_city']) ?> → <?= htmlspecialchars($ticket['destination_city']) ?></td>
                      <td><?= date('d M Y  h:m', strtotime($ticket['departure_time'])) ?></td>
                      <td><?= date('h:m', strtotime($ticket['departure_time'])) ?></td>
                      <td>
                        <?php if ($ticket['status'] === 'active'): ?>
                          <span class="badge bg-success">Aktif</span>
                        <?php elseif ($ticket['status'] === 'cancelled'): ?>
                          <span class="badge bg-danger">İptal</span>
                        <?php else: ?>
                          <span class="badge bg-secondary">Süresi Doldu</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
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
  </div>
</div>
<?php include 'footer.php'; ?>
