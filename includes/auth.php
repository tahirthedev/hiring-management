<?php
session_start();

function isLoggedIn(): bool {
    return isset($_SESSION['admin_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: /admin/login.php');
        exit;
    }
}

function loginAdmin(PDO $pdo, string $username, string $password): bool {
    $stmt = $pdo->prepare("SELECT id, password FROM admin_users WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_user'] = $username;
        return true;
    }
    return false;
}

function logoutAdmin(): void {
    session_destroy();
    header('Location: /admin/login.php');
    exit;
}
