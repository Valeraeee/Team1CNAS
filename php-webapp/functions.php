<?php
/**
 * Reusable database operations. Extracted out of create.php / update.php /
 * delete.php / index.php so that:
 *   1) PHPUnit can call them directly, in-process, and
 *   2) Xdebug can actually see them execute and report coverage.
 */

function getAllUsers(mysqli $conn): array
{
    $result = $conn->query("SELECT * FROM users");
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

function getUserById(mysqli $conn, int $id): ?array
{
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

function createUser(mysqli $conn, string $name, string $email): int
{
    $stmt = $conn->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $email);
    $stmt->execute();
    $id = $stmt->insert_id;
    $stmt->close();
    return $id;
}

function updateUser(mysqli $conn, int $id, string $name, string $email): bool
{
    return updateUserAffectedRows($conn, $id, $name, $email) !== false;
}

/**
 * Same as updateUser(), but returns the number of rows actually changed
 * instead of a plain bool. Captured from the *statement* object right
 * after execute() -- checking $conn->affected_rows afterwards is
 * unreliable across different mysqli driver builds (a well-known quirk),
 * which is why UpdateTest was failing in CI but not locally.
 *
 * Returns false if the query itself failed, otherwise an int (0 if no
 * row matched the given id).
 */
function updateUserAffectedRows(mysqli $conn, int $id, string $name, string $email): int|false
{
    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $email, $id);
    $ok = $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    return $ok ? $affected : false;
}

function deleteUser(mysqli $conn, int $id): bool
{
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}
