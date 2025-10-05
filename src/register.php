<?php
session_start();
include __DIR__ . '/../config.php';

function uuid()
{
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}


if (isset($_POST['register'])) {
    $full_name = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    if (!$full_name || !$email || !$password) {
        $error = "All fields are required!";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM User WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            $error = "Email already exists!";
        } else {
            // Insert new user with hashed password
            $id = uuid();
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO User (id, full_name, email, password) VALUES (:id, :full_name, :email, :password)");
            $result = $stmt->execute([
                'id' => $id,
                'full_name' => $full_name,
                'email' => $email,
                'password' => $hashedPassword
            ]);

            if ($result) {
                $success = "Registration successful! You can now login.";
                header("Location: ../index.php"); // Redirect to login page
                exit;
            } else {
                $error = "Something went wrong!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Register</h2>
    <?php if(isset($error)) echo '<div class="alert alert-danger">'.$error.'</div>'; ?>
    <form method="POST">
        <div class="mb-3">
            <label>Full Name</label>
            <input type="text" name="full_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" name="register" class="btn btn-primary">Register</button>
        <p class="mt-2">Already have an account? <a href="index.php">Login here</a></p>
    </form>
</div>
</body>
</html>
