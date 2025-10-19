<?php
session_start();

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/helper.php';

requireLogin();

$error = '';
$success = '';
$trip = [];

if (!isCompany()) {
    header("Location: dashboard.php");
    exit;
}

$company_id = $_SESSION['company_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['trip_id'])) {
    $trip_id = $_GET['trip_id'];
    $trip = get_company_trip($trip_id);
    if (!$trip) {
        $error = 'Trip not found.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trip_id = $_POST['trip_id'] ?? '';

    $destination_city = $_POST['destination_city'] ?? '';
    $departure_city = $_POST['departure_city'] ?? '';
    $departure_time = $_POST['departure_time'] ?? '';
    $arrival_time = $_POST['arrival_time'] ?? '';
    $price = $_POST['price'] ?? '';
    $capacity = $_POST['capacity'] ?? '';

    $result = edit_trip(
        $trip_id,
        $destination_city,
        $departure_city,
        $departure_time,
        $arrival_time,
        $price,
        $capacity,
        $company_id
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
    $trip = get_company_trip($trip_id);
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
              <input type="hidden" name="trip_id" value="<?= htmlspecialchars($trip['id'] ?? '') ?>">

              <div class="mb-3">
                  <label class="form-label">Başlangıç</label>
                  <input type="text" name="departure_city" class="form-control" 
                         value="<?= htmlspecialchars($trip['departure_city'] ?? '') ?>" required>
              </div>

              <div class="mb-3">
                  <label class="form-label">Bitiş</label>
                  <input type="text" name="destination_city" class="form-control" 
                         value="<?= htmlspecialchars($trip['destination_city'] ?? '') ?>" required>
              </div>

              <div class="mb-3">
                  <label class="form-label">Çıkış Tarihi ve Saati</label>
                  <input type="datetime-local" name="departure_time" class="form-control" 
                         value="<?= isset($trip['departure_time']) ? date('Y-m-d\TH:i', strtotime($trip['departure_time'])) : '' ?>" required>
              </div>

              <div class="mb-3">
                  <label class="form-label">Varış Tarihi ve Saati</label>
                  <input type="datetime-local" name="arrival_time" class="form-control" 
                         value="<?= isset($trip['arrival_time']) ? date('Y-m-d\TH:i', strtotime($trip['arrival_time'])) : '' ?>" required>
              </div>

              <div class="mb-3">
                  <label class="form-label">Fiyat</label>
                  <input type="number" name="price" class="form-control" 
                         value="<?= htmlspecialchars($trip['price'] ?? '') ?>" required>
              </div>

              <div class="mb-3">
                  <label class="form-label">Kapasite</label>
                  <input type="number" name="capacity" class="form-control" 
                         value="<?= htmlspecialchars($trip['capacity'] ?? '') ?>" required>
              </div>

              <button type="submit" class="btn btn-primary w-100">Güncelle</button>
          </form>
      </div>
    </div>
</div>