<?php
session_start();
require_once __DIR__ . '/../src/auth.php';
requireLogin();
require_once __DIR__ . '/../src/helper.php';
include 'header.php';

$message = '';
if (!isset($_GET['trip_id'])) {
		$message = 'No trip selected.';
} else {
		$trip_id = $_GET['trip_id'];
		$user_id = $_SESSION['user_id'];
		$result = book_ticket($trip_id, $user_id);
		$message = $result['message'];
		if (isset($result['seat_number'])) {
			$message .= '<br><strong>Koltuk No:</strong> ' . htmlspecialchars($result['seat_number']);
		}
}
?>
<div class="main-content d-flex justify-content-center align-items-center" style="min-height: 60vh;">
	<div class="card shadow-sm p-4" style="width: 400px;">
		<h3 class="text-center mb-3">Bilet Satın Al</h3>
		<div class="alert <?= (isset($result) && $result['success']) ? 'alert-success' : 'alert-danger' ?> text-center">
			<?= htmlspecialchars($message) ?>
		</div>
		<div class="text-center mt-3">
			<a href="dashboard.php" class="btn btn-primary">Dashboard'a Dön</a>
		</div>
	</div>
</div>
<?php include 'footer.php'; ?>