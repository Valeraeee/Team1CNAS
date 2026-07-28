<?php
include 'db.php';

$id = $_GET['id'];

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

$result = $conn->query("SELECT * FROM users WHERE id=$id");
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html><body>
<h2>Edit Member</h2>
<form method="POST">
    Member Name: <input name="name" value="<?= $user['name'] ?>" required><br><br>
    Email: <input name="email" value="<?= $user['email'] ?>" required><br><br>
    <button type="submit">Update</button>
</form>
<a href="index.php">Back</a>
</body></html>
