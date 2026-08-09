<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../functions.php';

final class UpdateTest extends TestCase
{
    private mysqli $conn;
    private int $userId;

    protected function setUp(): void
    {
        //NOSONAR
        require __DIR__ . '/../db.php';
        /** @var mysqli $conn */
        $this->conn = $conn;

        $this->userId = createUser($this->conn, 'Before Update', 'test.update@example.com');
    }

    protected function tearDown(): void
    {
        deleteUser($this->conn, $this->userId);
        $this->conn->close();
    }

    public function testUpdateUserChangesNameAndEmail(): void
    {
        $ok = updateUser($this->conn, $this->userId, 'After Update', 'updated@example.com');

        $this->assertTrue($ok);

        $row = getUserById($this->conn, $this->userId);
        $this->assertSame('After Update', $row['name']);
        $this->assertSame('updated@example.com', $row['email']);
    }

    public function testUpdateUserWithUnknownIdReturnsTrueButChangesNothing(): void
    {
        $ok = updateUser($this->conn, 999999, 'Nobody', 'nobody@example.com');
        $this->assertTrue($ok);

        $affected = updateUserAffectedRows($this->conn, 999999, 'Nobody', 'nobody@example.com');
        $this->assertSame(0, $affected);
    }
}
