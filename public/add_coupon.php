<?php
session_start();

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/helper.php';

$error = '';
$success = '';

requireLogin();
if (isCompany()) {
    $company_id = $_SESSION['company_id'];
    $code = $_POST['code'] ?? '';
    $discount = $_POST['discount'] ?? '';
    $usage_limit = $_POST['usage_limit'] ?? '';
    $expire_date = $_POST['expire_date'] ?? '';
    $result = add_coupon($code, $discount, $usage_limit, $expire_date, company_id: $company_id);
    if ($result['success']) {
        $success = $result['message'];
    } else {
        $error = $result['message'];
    }
}
elseif (isAdmin()) {
    $code = $_POST['code'] ?? '';
    $discount = $_POST['discount'] ?? '';
    $usage_limit = $_POST['usage_limit'] ?? '';
    $expire_date = $_POST['expire_date'] ?? '';
    $result = add_admin_coupon($code, $discount, $usage_limit, $expire_date);
    if ($result['success']) {
        $success = $result['message'];
    } else {
        $error = $result['message'];
    }
} else {
    $error = 'You do not have permission to add a coupon.';
}
include 'header.php'
?>
<div class="main-content">
  <div class="d-flex justify-content-center align-items-center" style="min-height: 80vh;">
      <div class="card shadow-sm p-4" style="width: 400px;">
          <h3 class="text-center mb-3">Kupon Ekle</h3>
          <?php if ($error): ?>
              <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>

          <?php if ($success && isCompany()): ?>
              <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
              <script>
                  setTimeout(function() {
                      window.location.href = "company_panel.php";
                  }, 2000);
              </script>
          <?php endif; ?>
          <?php if ($success && isAdmin()): ?>
              <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
              <script>
                  setTimeout(function() {
                      window.location.href = "admin_panel.php";
                  }, 2000);
              </script>
          <?php endif; ?>
          <form method="POST" action="">
              <div class="mb-3">
                  <label class="form-label">Kod</label>
                  <input type="text" name="code" class="form-control" required>
              </div>
              <div class="mb-3">
                  <label class="form-label">İndirim</label>
                  <input type="number" name="discount" class="form-control" required>
              </div>
              <div class="mb-3">
                  <label class="form-label">Limit</label>
                  <input type="number" name="usage_limit" class="form-control" required>
              </div>
              <div class="mb-3">
              <label class="form-label">Son Kullanma Tarihi</label>
              <input type="datetime-local" name="expire_date" class="form-control" required>
              </div>
              <?php if (isAdmin()): ?>
                <input type="hidden" name="company_id" value="<?= htmlspecialchars($company_id) ?>">
              <?php endif; ?>
              <button type="submit" class="btn btn-primary w-100">Tanımla</button>
          </form>
      </div>
  </div>
</div>
<?php include 'footer.php'; ?>

