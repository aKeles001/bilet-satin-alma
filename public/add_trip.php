<?php
session_start();

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/helper.php';

$error = '';
$success = '';

requireLogin();
if (isCompany()) {
    $company_id = $_SESSION['company_id'];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $destination_city = $_POST['destination_city'] ?? '';
        $departure_city = $_POST['departure_city'] ?? '';
        $departure_time = $_POST['departure_time'] ?? '';
        $arrival_time = $_POST['arrival_time'] ?? '';
        $price = $_POST['price'] ?? '';
        $capacity = $_POST['capacity'] ?? '';
        $result = registerTrip($destination_city, $departure_city, $departure_time, $arrival_time, $price, $capacity, $company_id);
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}
else {
    header("Location: dashboard.php");
    exit;
}
include 'header.php';
?>
<div class="main-content">
  <div class="d-flex justify-content-center align-items-center" style="min-height: 80vh;">
      <div class="card shadow-sm p-4" style="width: 400px;">
          <h3 class="text-center mb-3">Sefer Ekle</h3>
          <?php if ($error): ?>
              <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>

          <?php if ($success): ?>
              <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
              <script>
                  setTimeout(function() {
                      window.location.href = "company_panel.php";
                  }, 2000);
              </script>
          <?php endif; ?>
          <form method="POST" action="">
              <div class="mb-3">
                  <label class="form-label">Başlangıç</label>
                  <input type="text" name="departure_city" class="form-control" required>
              </div>
              <div class="mb-3">
                  <label class="form-label">Bitiş</label>
                  <input type="text" name="destination_city" class="form-control" required>
              </div>
              <div class="mb-3">
                  <label class="form-label">Çıkış Tarihi ve Saati</label>
                  <input type="datetime-local" name="departure_time" class="form-control" required>
              </div>
              <div class="mb-3">
              <label class="form-label">Varış Tarihi ve Saati</label>
              <input type="datetime-local" name="arrival_time" class="form-control" required>
              </div>
              <div class="mb-3">
                  <label class="form-label">Fiyat</label>
                  <input type="number" name="price" class="form-control" required>
              </div>
              <div class="mb-3">
                  <label class="form-label">Kapasite</label>
                  <input type="number" name="capacity" class="form-control" required>
              </div>
              <button type="submit" class="btn btn-primary w-100">Kayıt Et</button>
          </form>
      </div>
  </div>
</div>
<?php include 'footer.php'; ?>