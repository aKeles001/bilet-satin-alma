<?php
session_start();

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/helper.php';

$error = '';
$success = '';
$user_id = $_SESSION['user_id'];

requireLogin();
$user_tickets = get_user_tickets( $user_id );

if (isCompany() && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_id = $_POST['ticket_id'] ?? '';
    $result = cancel_ticket($ticket_id);

    if ($result['success']) {
        $success = $result['message'];
    } else {
        $error = $result['message'];
    }
}
elseif (isUser() && $_SERVER['REQUEST_METHOD'] === 'POST'){
    $ticket_id = $_POST['ticket_id'];
    foreach ($user_tickets as $user_ticket){
      if ($user_ticket['id'] == $ticket_id) {
        $result = cancel_ticket($ticket_id);
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
      }
    }
}
else{
  $result['message'] = 'asdasd';
  $error = $result['message'];
}
?>

<?php include 'header.php'; ?>

<body>

<div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="resultModalLabel">Result</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<?php if ($success || $error): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = new bootstrap.Modal(document.getElementById('resultModal'));
    const modalBody = document.querySelector('#resultModal .modal-body');
    const modalTitle = document.querySelector('#resultModal .modal-title');

    <?php if ($success): ?>
        modalTitle.textContent = "Success";
        modalBody.innerHTML = "<?= htmlspecialchars($success) ?>";
    <?php elseif ($error): ?>
        modalTitle.textContent = "Error";
        modalBody.innerHTML = "<?= htmlspecialchars($error) ?>";
    <?php endif; ?>

    <?php if (isCompany()): ?>
    modal.show();
    const modalElement = document.getElementById('resultModal');
    modalElement.addEventListener('hidden.bs.modal', () => {
        window.location.href = "company_panel.php";
    });
    <?php elseif (isUser()): ?>
    modal.show();
    const modalElement = document.getElementById('resultModal');
    modalElement.addEventListener('hidden.bs.modal', () => {
        window.location.href = "dashboard.php";
    });
    <?php endif; ?>
});
</script>
<?php endif; ?>

<?php include 'footer.php'; ?>

</body>