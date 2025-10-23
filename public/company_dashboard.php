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

?>
<div class="main-content">
  <div class="container">
  <h2 class="mb-4">Hoşgeldin, <?= htmlspecialchars($full_name) ?> 👋</h2>
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
                  <th>Kupon Tanımla</th>
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
                          <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                        </form>
                      </td>
                      <td>
                        <form action="edit_trip.php" method="POST" style="display:inline;">
                          <input type="hidden" name="trip_id" value="<?= htmlspecialchars($trip['id']) ?>">
                          <a href="edit_trip.php?trip_id=<?= $trip['id'] ?>" class="btn btn-sm btn-warning">Düzenle</a>
                        </form>
                      </td>
                      <td>
                        <form action="add_coupon.php" method="POST" style="display:inline;">
                          <input type="hidden" name="trip_id" value="<?= htmlspecialchars($trip['id']) ?>">
                          <a href="edit_trip.php?trip_id=<?= $trip['id'] ?>" class="btn btn-sm btn-success">Tanımla</a>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
            <div class="mt-3 text-end">
                <a href="sefer_ekle.php" class="btn btn-danger">Sefer Ekle</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include 'footer.php'; ?>
