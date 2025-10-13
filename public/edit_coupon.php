<?php
session_start();

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/helper.php';

requireLogin();

$error = '';
$success = '';
$coupon = [];

if (!isCompany()) {
    header("Location: company_panel.php");
    exit;
}

$company_id = $_SESSION['company_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['coupon_id'])) {
    $coupon_id = $_GET['coupon_id'];
    $coupon = get_company_coupon($coupon_id);
    if (!$coupon) {
        $error = 'Coupon not found.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $coupon_id = $_POST['coupon_id'] ?? '';
    $code = $_POST['code'] ?? '';
    $discount = $_POST['discount'] ?? '';
    $usage_limit = $_POST['usage_limit'] ?? '';
    $expire_date = $_POST['expire_date'] ?? '';

    $result = edit_coupon(
        $coupon_id,
        $code,
        $discount,
        $usage_limit,
        $expire_date,
        $company_id,
    );

    if ($result['success']) {
        $success = $result['message'];
        echo "<script>
                setTimeout(function() {
                    window.location.href = 'company_panel.php';
                }, 2000);
              </script>";
    } else {
        $error = $result['message'];
    }
    $coupon = get_company_coupon($coupon_id);
}

include 'header.php';
?>

<div class="main-content">
  <div class="d-flex justify-content-center align-items-center" style="min-height: 80vh;">
      <div class="card shadow-sm p-4" style="width: 400px;">
          <h3 class="text-center mb-3">Seferi Güncelle</h3>

          <?php if ($error): ?>
              <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>

          <?php if ($success): ?>
              <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
          <?php endif; ?>

          <form method="POST" action="">
              <input type="hidden" name="coupon_id" value="<?= htmlspecialchars($coupon['id'] ?? '') ?>">

              <div class="mb-3">
                  <label class="form-label">Kod</label>
                  <input type="text" name="code" class="form-control" 
                         value="<?= htmlspecialchars($coupon['code'] ?? '') ?>" required>
              </div>

              <div class="mb-3">
                  <label class="form-label">İndirim</label>
                  <input type="text" name="discount" class="form-control" 
                         value="<?= htmlspecialchars($coupon['discount'] ?? '') ?>" required>
              </div>

              <div class="mb-3">
                  <label class="form-label">Limit</label>
                  <input type="text" name="usage_limit" class="form-control" 
                         value="<?= htmlspecialchars($coupon['usage_limit'] ?? '') ?>" required>
              </div>

              <div class="mb-3">
                  <label class="form-label">Son Kullanma Tarihi</label>
                  <input type="datetime-local" name="expire_date" class="form-control" 
                         value="<?= isset($coupon['expire_date']) ? date('Y-m-d\TH:i', strtotime($coupon['expire_date'])) : '' ?>" required>
              </div>
              <button type="submit" class="btn btn-primary w-100">Güncelle</button>
          </form>
      </div>
    </div>
</div>