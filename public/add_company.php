<?php
session_start();

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/helper.php';

$error = '';
$success = '';
requireLogin();
if (isAdmin()) {
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'] ?? '';
        $logo = $_FILES['logo'] ?? null;
        $full_name = $_POST['full_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $validation = validate_registration($full_name, $email, $password);
        if (!$validation['success']) {
        $error = $validation['message'];
        } else {
            if ($logo && $logo['error'] === UPLOAD_ERR_OK) {
                $san_name = preg_replace("/[^a-zA-Z0-9]/", "_", basename($logo['name']));
                $extention = pathinfo($logo['name'], PATHINFO_EXTENSION);
                $new_name = $san_name . '.' . $extention;
                $target_dir = __DIR__ . '/../images/company_logos/';
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0755, true);
                }
                $target_path = $target_dir . $new_name;

                if (move_uploaded_file($logo['tmp_name'], $target_path)) {
                    $success = "Şirket Logosu başarıyla yüklendi";
                    $result = add_company($name, $target_path, $full_name, $email, $password);
                    if ($result['success']) {
                        $success = $result['message'];
                    } else {
                        $error = $result['message'];
                    }
                }
            } else {
                $error = 'Logo yüklenirken bir hata meydana geldi.';
        }
    }
}
}
include 'header.php';
?>
<div class="main-content">
  <div class="d-flex justify-content-center align-items-center" style="min-height: 80vh;">
      <div class="card shadow-sm p-4" style="width: 400px;">
          <h3 class="text-center mb-3">Firma Ekle</h3>
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
          <form method="POST" action="" enctype="multipart/form-data">
              <div class="mb-3">
                <label class="form-label">Firma İsim</label>
                <input type="text" name="name" class="form-control" required>
              </div>
              <div>
                <label class="form-label">Logo</label>
                <input type="file" name="logo" class="form-control" accept="image/*" required>
              </div>
              <div>
                <label class="form-label">Admin İsim</label>
                <input type="text" name="full_name" class="form-control" accept="image/*" required>
              </div>
              <div>
                <label class="form-label">Admin E-mail</label>
                <input type="text" name="email" class="form-control" accept="image/*" required>
              </div>
              <div>
                <label class="form-label">Admin Şifre</label>
                <input type="password" name="password" class="form-control" accept="image/*" required>
              </div>
              <button type="submit" class="btn btn-primary w-100">Tanımla</button>
          </form>
      </div>
  </div>
</div>
<?php include 'footer.php'; ?>

