<?php
session_start();

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/helper.php';

$error = '';
$success = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $validation = validate_registration($full_name, $email, $password);
    if (!$validation['success']) {
        $error = $validation['message'];
    } else {
        $result = registerUser($full_name, $email, $password);
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}

include 'header.php';
?>
<div class="main-content">
  <div class="d-flex justify-content-center align-items-center" style="min-height: 80vh;">
      <div class="card shadow-sm p-4" style="width: 400px;">
          <h3 class="text-center mb-3">Kayıt Ol</h3>

          <?php if ($error): ?>
              <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>

          <?php if ($success): ?>
              <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
              <script>
                  setTimeout(function() {
                      window.location.href = "login.php";
                  }, 2000);
              </script>
          <?php endif; ?>

          <form method="POST" action="">
              <div class="mb-3">
                  <label class="form-label">İsim</label>
                  <input type="text" name="full_name" class="form-control" required>
              </div>
              <div class="mb-3">
                  <label class="form-label">Email</label>
                  <input type="email" name="email" class="form-control" required>
              </div>
              <div class="mb-3">
                  <label class="form-label">Şifre</label>
                  <input type="password" name="password" class="form-control" required>
              </div>
              <button type="submit" class="btn btn-primary w-100">Kayıt Ol</button>
          </form>
      </div>
  </div>
</div>
<?php include 'footer.php'; ?>
