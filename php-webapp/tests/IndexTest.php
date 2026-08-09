<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../functions.php';

final class IndexTest extends TestCase
{
    private mysqli $conn;

    protected function setUp(): void
    {
        // NOSONAR
        require __DIR__ . '/../db.php';
        /** @var mysqli $conn */
        $this->conn = $conn;
    }

    protected function tearDown(): void
    {
        $this->conn->query("DELETE FROM users WHERE email = 'test.index@example.com'");
        $this->conn->close();
    }

    public function testGetAllUsersReturnsArrayIncludingNewRow(): void
    {
        $id = createUser($this->conn, 'Test Index', 'test.index@example.com');

        $this->assertNotNull(getUserById($this->conn, $id));

        $all = getAllUsers($this->conn);
        $this->assertIsArray($all);

        $emails = array_column($all, 'email');
        $this->assertContains('test.index@example.com', $emails);
    }
}
