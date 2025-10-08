<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db_connect.php';
include __DIR__ . '/../src/helper.php';
function loginUser($email, $password)
{
    global $db;

    $stmt = $db->prepare("SELECT * FROM User WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        return ['success' => false, 'message' => 'User not found!'];
    }

    if (!password_verify($password, $user['password'])) {
        return ['success' => false, 'message' => 'Incorrect password!'];
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['company_id'] = $user['company_id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'] ?? 'user';

    return ['success' => true, 'user' => $user];
}

function requireLogin()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}


function isAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}
function isCompany()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'company';
}
function registerUser($full_name, $email, $password)
{
    global $db;

    $full_name = trim($full_name ?? '');
    $email = trim($email ?? '');
    $password = trim($password ?? '');

    if (!$full_name || !$email || !$password) {
        return ['success' => false, 'message' => 'All fields are required.'];
    }
    // Check if user exists
    $stmt = $db->prepare("SELECT id FROM `User` WHERE email = :email");
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        return ['success' => false, 'message' => 'Email already exists.'];
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $id = uuid();

    $stmt = $db->prepare("INSERT INTO `User` (id, full_name, email, password) VALUES (:id, :full_name, :email, :password)");
    $result = $stmt->execute([
        ':id' => $id,
        ':full_name' => $full_name,
        ':email' => $email,
        ':password' => $hashedPassword
    ]);

    if ($result) {
        return ['success' => true, 'message' => 'Registration successful.'];
    } else {
        return ['success' => false, 'message' => 'Database error. Please try again.'];
    }
}
?>
