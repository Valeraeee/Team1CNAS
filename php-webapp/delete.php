<?php
require_once 'auth_check.php';
require_once 'db.php';
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method not allowed.');
}

verifyCsrfToken();

$id = (int)($_POST['id'] ?? 0);
if ($id > 0) {
    deleteUser($conn, $id);
}

header('Location: index.php');
exit;
