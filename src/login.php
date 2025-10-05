<?php
session_start();
include __DIR__ . '/../config.php'; // correct path to config.php

$error = '';

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Prepare statement to fetch user by email
    $stmt = $conn->prepare("SELECT * FROM User WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Verify password
        if (password_verify($password, $user['password'])) {
            $_SESSION['email'] = $user['email'];
            $_SESSION['user_id'] = $user['id'];
            header("Location: ../index.php"); // Redirect to index or dashboard
            exit;
        } else {
            $error = "Incorrect password!";
        }
    } else {
        $error = "User not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="../public/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card p-4">
        <h2>Login</h2>
        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="mb-3">
                <label>Email</label>
                <input type="text" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary">Login</button>
        </form>
        <p class="mt-3">Don't have an account? <a href="register.php">Register here</a>.</p>
    </div>
</div>
<script src="../public/js/bootstrap.bundle.min.js"></script>
</body>
</html>
