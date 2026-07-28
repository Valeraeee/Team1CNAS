<?php
$host = getenv("DB_HOST") ?: "mysql";
$user = getenv("DB_USER") ?: "root";
$pass = getenv("DB_PASSWORD") ?: "rootpass";
$db   = getenv("DB_NAME") ?: "mydb";
$port = getenv("DB_PORT") ?: 3306;

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
