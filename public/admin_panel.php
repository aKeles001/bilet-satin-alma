<?php
session_start();
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ .'/../src/helper.php';
requireLogin();
include 'header.php';

$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'] ?? '';

$companies = get_companies();
$coupons = get_admin_coupons();
$users = get_users();

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
                  <th>Firma ID</th>
                  <th>Firma Kaldır</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($companies)): ?>
                  <tr><td colspan="3" class="text-center">Kayıtlı Firma Bulunamadı..</td></tr>
                <?php else: ?>
                  <?php foreach ($companies as $company): ?>
                    <tr>
                      <td><?= htmlspecialchars($company['name']) ?>
                      <td><?= htmlspecialchars($company['id']) ?>
                      <td>
                        <form action="cancel_company.php" method="POST" style="display:inline;">
                            <input type="hidden" name="company_id" value="<?= htmlspecialchars($company['id']) ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Sil</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
            <div class="mt-3 text-end">
              <a href="add_company.php" class="btn btn-success">Firma Ekle</a>
              <form action="add_company_admin.php" method="POST" style="display:inline;">
                <input type="hidden" name="company_id" value="<?= htmlspecialchars($company['id']) ?>">
                <button type="submit" class="btn btn-success">Firma Admin Ekle</button>
              </form>
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
            
            <h5 class="card-title mt-4">Kullanıcılar</h5>
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>İsim</th>
                  <th>E-mail</th>
                  <th>Role</th>
                  <th>Firma</th>
                  <th>Firma Admini Ata</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($users)): ?>
                  <tr><td colspan="3" class="text-center">Kayıtlı kullanıcı bulunamadı.</td></tr>
                <?php else: ?>
                  <?php foreach ($users as $user): ?>
                    <tr>
                      <td><?= htmlspecialchars($user['full_name']) ?></td>
                      <td><?= htmlspecialchars($user['email']) ?></td>
                      <td>
                        <?php if ($user['role'] === 'user'): ?>
                          <span class="badge bg-secondary">Kullanıcı</span>
                        <?php elseif ($user['role'] === 'company'): ?>
                          <span class="badge bg-primary">Firma Yetkilis</span>
                        <?php elseif ($user['role'] === 'admin'): ?>
                          <span class="badge bg-danger">admin</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <?php if (!($user['company_id'] == NULL)): ?>
                          <?= htmlspecialchars($user['company_name']) ?>
                        <?php else: ?>
                          <span class="badge bg-secondary">-------</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <form action="company_admin_set.php" method="POST" style="display:inline;">
                          <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">
                          <select name="company_id" class="form-select form-select-sm d-inline-block w-auto" required>
                              <option value="">Select Company</option>
                              <?php foreach ($companies as $company): ?>
                                  <option value="<?= htmlspecialchars($company['id']) ?>">
                                      <?= htmlspecialchars($company['name']) ?>
                                  </option>
                              <?php endforeach; ?>
                          </select>

                          <button type="submit" class="btn btn-warning btn-sm">Ata</button>
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
