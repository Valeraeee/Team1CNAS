<?php
include_once 'db.php';

$id = $_GET['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = $_POST['name'];
    $email = $_POST['email'];
    $stmt = $conn->prepare("UPDATE users SET name=?, email=? WHERE id=?");
    $stmt->bind_param("ssi", $name, $email, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: index.php");
    exit();
}

// Prepared statement to prevent SQL Injection
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Team Member</title>
</head>
<body>
<h2>Edit Member</h2>
<form method="POST">
    <label for="name">Member Name:</label>
    <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required><br><br>

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required><br><br>

    <button type="submit">Update</button>
</form>
<a href="index.php">Back</a>
</body>
</html>
