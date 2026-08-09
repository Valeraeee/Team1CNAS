<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $validUser = hash_equals(getenv('ADMIN_USERNAME') ?: '', $username);
    $validPass = password_verify($password, getenv('ADMIN_PASSWORD_HASH') ?: '');

    if ($validUser && $validPass) {
        session_regenerate_id(true);
        $_SESSION['authenticated'] = true;
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head><title>Login</title></head>
<body>
    <h2>Admin Login</h2>
    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post" action="login.php">
        <label>Username: <input type="text" name="username" required></label><br>
        <label>Password: <input type="password" name="password" required></label><br>
        <button type="submit">Log In</button>
    </form>
</body>
</html>
