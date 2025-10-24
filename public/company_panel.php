<?php
session_start();
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ .'/../src/helper.php';
requireLogin();
include 'header.php';

$user_id = $_SESSION['user_id'];
$company_id = $_SESSION['company_id'];
$full_name = $_SESSION['full_name'] ?? '';

$trips = get_company_trips($company_id);
$coupons = get_company_coupons($company_id);
$tickets = get_company_tickets($company_id);
$logo_path = get_company_logo($company_id);


?>
<div class="main-content">
  <div class="container">
  <h2 class="mb-4">Hoşgeldin, <?= htmlspecialchars($full_name) ?></h2>
  <?php if (!empty($logo_path)): ?>
      <img src="<?= htmlspecialchars($logo_path) ?>" alt="Company Logo" style="height: 100px; display: block; margin-bottom: 20px;">
  <?php endif; ?>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="card text-center shadow-sm">
        </div>
      </div>
      <div class="col-md-8">
        <div class="card shadow-sm">
          <div class="card-body">
            <h5 class="card-title">Kayıtlı Seferler</h5>
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Sefer</th>
                  <th>Tarih</th>
                  <th>Saat</th>
                  <th>İptal</th>
                  <th>Düzenle</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($trips)): ?>
                  <tr><td colspan="3" class="text-center">Firmanıza kayıtlı sefer bulunmamaktadır..</td></tr>
                <?php else: ?>
                  <?php foreach ($trips as $trip): ?>
                    <tr>
                      <td><?= htmlspecialchars($trip['departure_city']) ?> → <?= htmlspecialchars($trip['destination_city']) ?></td>
                      <td><?= date('d M Y  h:m', strtotime($trip['departure_time'])) ?></td>
                      <td><?= date('H:m', strtotime($trip['departure_time'])) ?></td>
                      <td>
                        <form action="cancel_trip.php" method="POST" style="display:inline;">
                        <input type="hidden" name="trip_id" value="<?= htmlspecialchars($trip['id']) ?>">
                        <button type="submit" class="btn btn-danger btn-sm">İptal Et</button>
                        </form>
                      </td>
                      <td>
                        <form action="edit_trip.php" method="POST" style="display:inline;">
                        <input type="hidden" name="trip_id" value="<?= htmlspecialchars($trip['id']) ?>">
                        <a href="edit_trip.php?trip_id=<?= $trip['id'] ?>" class="btn btn-sm btn-warning">Düzenle</a>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
            <div class="mt-3 text-end">
                <a href="add_trip.php" class="btn btn-success">Sefer Ekle</a>
            </div>
            <h5 class="card-title">Kayıtlı Kuponlar</h5>
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Kod</th>
                  <th>İndirim</th>
                  <th>Limit</th>
                  <th>Son Kullanma Tarihi</th>
                  <th>Düzenle</th>
                  <th>Sil</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($coupons)): ?>
                  <tr><td colspan="3" class="text-center">Firmanıza kayıtlı Kupon bulunmamaktadır..</td></tr>
                <?php else: ?>
                  <?php foreach ($coupons as $coupon): ?>
                    <tr>
                      <td><?= htmlspecialchars($coupon['code']) ?></td>
                      <td>₺<?= number_format($coupon['discount'], 2, ',', '.') ?></td>
                      <td><?= htmlspecialchars($coupon['usage_limit']) ?></td>
                      <td><?= date('Y-m-d H:i', strtotime($coupon['expire_date'])) ?></td>
                      <td>
                        <form action="cancel_coupon.php" method="POST" style="display:inline;">
                            <input type="hidden" name="coupon_id" value="<?= htmlspecialchars($coupon['id']) ?>">
                            <button type="submit" class="btn btn-danger btn-sm">İptal Et</button>
                        </form>
                      </td>
                      <td>
                      <form action="edit_coupon.php" method="POST" style="display:inline;">
                            <input type="hidden" name="coupon_id" value="<?= htmlspecialchars($coupon['id']) ?>">
                            <input type="hidden" name="company_id" value="<?= htmlspecialchars($company_id) ?>">
                            <button type="submit" class="btn btn-warning btn-sm">Düzenle</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
            <div class="mt-3 text-end">
                <a href="add_coupon.php" class="btn btn-success">Kupon Ekle</a>
            </div>
            <h5 class="card-title mt-4">Satılan Biletler</h5>
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Sefer</th>
                  <th>Tarih</th>
                  <th>Saat</th>
                  <th>İsim</th>
                  <th>Durum</th>
                  <th>İptal Et</th>
                  <th>Sil</th>
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
                      <td><?= date('H:m', strtotime($ticket['departure_time'])) ?></td>
                      <td><?= htmlspecialchars($ticket['full_name']) ?></td>
                      <td>
                        <?php if ($ticket['status'] === 'active'): ?>
                          <span class="badge bg-success">Aktif</span>
                        <?php elseif ($ticket['status'] === 'cancelled'): ?>
                          <span class="badge bg-danger">İptal Edildi</span>
                        <?php else: ?>
                          <span class="badge bg-secondary">Süresi Doldu</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <form action="cancel_ticket.php" method="POST" style="display:inline;">
                            <input type="hidden" name="ticket_id" value="<?= htmlspecialchars($ticket['id']) ?>">
                            <button type="submit" class="btn btn-danger btn-sm">İptal Et</button>
                        </form>
                      </td>
                      <td>
                        <form action="delete_history.php" method="POST" style="display:inline;">
                            <input type="hidden" name="ticket_id" value="<?= htmlspecialchars($ticket['id']) ?>">
                            <button type="submit" class="btn btn-secondary btn-sm">Sil</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include 'footer.php'; ?>
