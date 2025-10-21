<?php
session_start();

require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/helper.php';

$error = '';
$success = '';
requireLogin();

// Only admins can access this page
if (!isAdmin()) {
    die('Unauthorized access.');
}

// Fetch companies to populate dropdown
$companies = get_companies();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $company_id = $_POST['company_id'] ?? null; // dropdown or hidden input
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validate user input
    $validation = validate_registration($full_name, $email, $password);
    if (!$validation['success']) {
        $error = $validation['message'];
    } else {
        // Register the user
        $result = registerUser($full_name, $email, $password, $company_id, 'company');
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
            <h3 class="text-center mb-3">Register Company User</h3>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <script>
                    setTimeout(function() {
                        window.location.href = "admin_panel.php";
                    }, 2000);
                </script>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Assign Company (optional)</label>
                    <select name="company_id" class="form-select">
                        <option value="">-- No Company Assigned --</option>
                        <?php foreach ($companies as $company): ?>
                            <option value="<?= htmlspecialchars($company['id']) ?>">
                                <?= htmlspecialchars($company['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
