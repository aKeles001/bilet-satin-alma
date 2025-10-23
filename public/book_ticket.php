<?php
session_start();
require_once __DIR__ . '/../src/auth.php';
requireLogin();
require_once __DIR__ . '/../src/helper.php';
include 'header.php';

$message = '';
if (!isset($_POST['trip_id'])) {
		$message = 'No trip selected.';
} 
else {
		$trip_id = $_POST['trip_id'];
		$booked_seat = $_POST['selected_seat'];
		$code = $_POST['code'];
		$user_id = $_SESSION['user_id'];
		if (!empty($code)){
			$result = book_ticket($trip_id, $user_id, $booked_seat, $code);
		}
		else {
			$result = book_ticket($trip_id, $user_id, $booked_seat);
		}
		$message = $result['message'];
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