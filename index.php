<?php
session_start();

// If user clicks logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Check if user is logged in
$loggedIn = isset($_SESSION['username']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home - PHP Auth Demo</title>
    <link href="public/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card p-4">
            <h2>Welcome to the Demo App</h2>
            
            <?php if ($loggedIn): ?>
                <p>Hello, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!</p>
                <a href="?action=logout" class="btn btn-danger">Logout</a>
                <a href="src/dashboard.php" class="btn btn-primary">Dashboard</a>
            <?php else: ?>
                <p>Please login or register to continue:</p>
                <a href="src/login.php" class="btn btn-primary">Login</a>
                <a href="src/register.php" class="btn btn-success">Register</a>
            <?php endif; ?>
        </div>
    </div>

    <script src="public/js/bootstrap.bundle.min.js"></script>
</body>
</html>
