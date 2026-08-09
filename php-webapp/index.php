<?php include_once 'db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>CNAS Assignment - Team Members List</title>
</head>
<body>
<h2>Team Members in Class -T01/T02 Team – 01/02/03/04 </h2>
<p style="color:gray;font-size:12px;">Served by pod: <?php echo htmlspecialchars(gethostname()); ?></p>
<a href="create.php">Add New Team Member</a>

<table style="border: 1px solid black; border-collapse: collapse;">
<tr>
    <th style="border: 1px solid black; padding: 8px;">ID</th>
    <th style="border: 1px solid black; padding: 8px;">Student Name</th>
    <th style="border: 1px solid black; padding: 8px;">Email</th>
    <th style="border: 1px solid black; padding: 8px;">Actions</th>
</tr>
<?php
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
                <a href='delete.php?id={$id}'>Delete</a>
            </td>
          </tr>";
}
$conn->close();
?>
</table>
</body>
</html>
