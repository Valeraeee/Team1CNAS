<?php

require_once 'auth_check.php';

include_once 'db.php';

?>

<!DOCTYPE html>

<html lang="en">

<head>
    <title>CNAS Assignment - Team Members List</title>
</head>

<body>
<h2>Team Members in Class -T01/T02 Team - 01/02/03/04 </h2>
<p style="color:gray;font-size:12px;">Served by pod: <?php echo htmlspecialchars(gethostname()); ?></p>
<p><a href="create.php">Add New Team Member</a> | <a href="logout.php">Logout</a></p>

<table style="border: 1px solid black; border-collapse: collapse;">

<tr>
    <th style="border: 1px solid black; padding: 8px;">ID</th>
    <th style="border: 1px solid black; padding: 8px;">Student Name</th>
    <th style="border: 1px solid black; padding: 8px;">Email</th>
    <th style="border: 1px solid black; padding: 8px;">Actions</th>
</tr>

<?php
$csrfToken = htmlspecialchars($_SESSION['csrf_token']);
$result = $conn->query("SELECT * FROM users");
while ($row = $result->fetch_assoc()) {
    $id = htmlspecialchars($row['id']);
    $name = htmlspecialchars($row['name']);
    $email = htmlspecialchars($row['email']);

    echo "<tr>
            <td style='border: 1px solid black; padding: 8px;'>{$id}</td>
            <td style='border: 1px solid black; padding: 8px;'>{$name}</td>
            <td style='border: 1px solid black; padding: 8px;'>{$email}</td>
            <td style='border: 1px solid black; padding: 8px;'>
                <a href='update.php?id={$id}'>Edit</a> |
                <form method='post' action='delete.php' style='display:inline;' onsubmit='return confirm(\"Delete this record?\");'>
                    <input type='hidden' name='id' value='{$id}'>
                    <input type='hidden' name='csrf_token' value='{$csrfToken}'>
                    <button type='submit' style='display:inline;background:none;border:none;color:blue;text-decoration:underline;cursor:pointer;padding:0;font-size:inherit;'>Delete</button>
                </form>
            </td>
        </tr>";
}
$conn->close();
?>
</table>
</body>
</html>
