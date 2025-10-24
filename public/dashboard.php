<?php
session_start();
require_once __DIR__ . '/../src/auth.php';
requireLogin();
include 'header.php';
$user_role = $_SESSION['role'] ?? 'user';
$user_id = $_SESSION['user_id'];
$company_id = $_SESSION['company_id'];
$full_name = $_SESSION['full_name'] ?? '';


$balance = get_user_balance($user_id);
$tickets = get_user_tickets($user_id);

?>
<div class="main-content">
  <div class="container">
  <h2 class="mb-4">Hoşgeldin, <?= htmlspecialchars($full_name) ?></h2>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="card text-center shadow-sm">
          <div class="card-body">
            <h5 class="card-title">Bakiye</h5>
            <p class="display-6 text-success">₺<?= htmlspecialchars(string: number_format($balance['balance'], 2)) ?></p>
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
                  <th>Marka</th>
                  <th>Sefer</th>
                  <th>Tarih</th>
                  <th>Saat</th>
                  <th>Durum</th>
                  <th>PDF Bilet</th>
                  <th>İptal Et</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($tickets)): ?>
                  <tr><td colspan="3" class="text-center">Kayıtlı bilet bulunamadı.</td></tr>
                <?php else: ?>
                  <?php foreach ($tickets as $ticket): ?>
                    <tr>
                      <td><?= htmlspecialchars($ticket['name']) ?></td>
                      <td><?= htmlspecialchars($ticket['departure_city']) ?> → <?= htmlspecialchars($ticket['destination_city']) ?></td>
                      <td><?= date('d M Y', strtotime($ticket['departure_time'])) ?></td>
                      <td><?= date('H:m', strtotime($ticket['departure_time'])) ?></td>
                      <td>
                        <?php if ($ticket['status'] === 'active'): ?>
                          <span class="badge bg-success">Aktif</span>
                        <?php elseif ($ticket['status'] === 'cancelled'): ?>
                          <span class="badge bg-danger">İptal</span>
                        <?php else: ?>
                          <span class="badge bg-secondary">Süresi Doldu</span>
                        <?php endif; ?>
                      <td>
                          <form action="ticket_pdf.php" method="POST" style="display:inline;">
                            <input type="hidden" name="full_name" value="<?= htmlspecialchars($_SESSION['full_name'], ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="departure_city" value="<?= htmlspecialchars($ticket['departure_city'], ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="destination_city" value="<?= htmlspecialchars($ticket['destination_city'], ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="date" value="<?= date('d M Y', strtotime($ticket['departure_time'])) ?>">
                            <input type="hidden" name="time" value="<?= date('H:m', strtotime($ticket['departure_time'])) ?>">
                            <input type="hidden" name="name" value="<?= htmlspecialchars($ticket['name'], ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="status" value="<?= htmlspecialchars($ticket['status'] ?? 'Pending', ENT_QUOTES, 'UTF-8') ?>">
                            <button type="submit" class="btn btn-success btn-sm">PDF</button>
                          </form>
                      </td>
                      <td>
                        <form action="cancel_ticket.php" method="POST" style="display:inline;">
                          <input type="hidden" name="ticket_id" value="<?= htmlspecialchars($ticket['id']) ?>">
                          <button type="submit" class="btn btn-danger btn-sm">İptal Et</button>
                        </form>
                      </td>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
            <div class="mt-3 text-end">
              <form action="trips_pdf.php" method="POST">
                  <?php foreach ($tickets as $ticket): ?>
                      <input type="hidden" name="tickets[<?= $ticket['id'] ?>][id]" value="<?= htmlspecialchars($ticket['id']) ?>">
                      <input type="hidden" name="tickets[<?= $ticket['id'] ?>][full_name]" value="<?= htmlspecialchars($_SESSION['full_name']) ?>">
                      <input type="hidden" name="tickets[<?= $ticket['id'] ?>][departure_city]" value="<?= htmlspecialchars($ticket['departure_city']) ?>">
                      <input type="hidden" name="tickets[<?= $ticket['id'] ?>][destination_city]" value="<?= htmlspecialchars($ticket['destination_city']) ?>">
                      <input type="hidden" name="tickets[<?= $ticket['id'] ?>][date]" value="<?= date('d M Y', strtotime($ticket['departure_time'])) ?>">
                      <input type="hidden" name="tickets[<?= $ticket['id'] ?>][time]" value="<?= date('H:i', strtotime($ticket['departure_time'])) ?>">
                      <input type="hidden" name="tickets[<?= $ticket['id'] ?>][company]" value="<?= htmlspecialchars($ticket['name']) ?>">
                      <input type="hidden" name="tickets[<?= $ticket['id'] ?>][status]" value="<?= htmlspecialchars($ticket['status'] ?? 'Pending') ?>">
                  <?php endforeach; ?>
                  <button type="submit" class="btn btn-success btn-sm">Seferleri Dışa Aktar</button>
              </form>
            </div>
            <?php if (isAdmin()): ?>
                <div class="mt-3 text-end">
                    <a href="/public/admin.php" class="btn btn-danger">Admin Panel</a>
                </div>
            <?php endif; ?>
            <?php if (isCompany()): ?>
                <div class="mt-3 text-end">
                    <a href="company_panel.php" class="btn btn-danger">Firma Admin Panel</a>
                </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include 'footer.php'; ?>
