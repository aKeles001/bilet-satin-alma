<?php
session_start();
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ .'/../src/helper.php';
requireLogin();
include 'header.php';

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'] ?? '';

$companies = get_companies();
$coupons = get_coupons($user_id);

?>
<div class="main-content">
  <div class="container">
  <h2 class="mb-4">Hoşgeldin, <?= htmlspecialchars($full_name) ?></h2>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="card text-center shadow-sm">
        </div>
      </div>
      <div class="col-md-8">
        <div class="card shadow-sm">
          <div class="card-body">
            <h5 class="card-title">Kayıtlı Firmalar</h5>
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Firma İsimleri</th>
                  <th>Firma Admin İsimleri</th>
                  <th>Firma Admin E-Mail</th>
                  <th>Firma ID</th>
                  <th>Firma Kupon Tanımla</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($companies)): ?>
                  <tr><td colspan="3" class="text-center">Kayıtlı Firma Bulunamadı..</td></tr>
                <?php else: ?>
                  <?php foreach ($companies as $company): ?>
                    <tr>
                      <td><?= htmlspecialchars($company['name']) ?>
                      <td><?= htmlspecialchars($company['full_name']) ?>
                      <td><?= htmlspecialchars($company['email']) ?>
                      <td><?= htmlspecialchars($company['id']) ?>
                      <td>
                        <form action="add_coupon.php" method="POST" style="display:inline;">
                            <input type="hidden" name="company_id" value="<?= htmlspecialchars($company['id']) ?>">
                            <button type="submit" class="btn btn-success btn-sm">Tanımla</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
            <div class="mt-3 text-end">
              <a href="add_company.php" class="btn btn-success">Firma Ekle</a>
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
                            <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                        </form>
                      </td>
                      <td>
                        <form action="edit_coupon.php" method="POST" style="display:inline;">
                            <input type="hidden" name="coupon_id" value="<?= htmlspecialchars($coupon['id']) ?>">
                            <input type="hidden" name="company_id" value="<?= htmlspecialchars($coupon['company_id']) ?>">
                            <button type="submit" class="btn btn-warning btn-sm">Düzenle</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
            <div class="mt-3 text-end">
              <form action="add_coupon.php" method="POST" style="display:inline;">
                <input type="hidden" name="company_id" value="<?= htmlspecialchars($company['id']) ?>">
                <button type="submit" class="btn btn-success btn">Kupon Ekle</button>
              </form>
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
