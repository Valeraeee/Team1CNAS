<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../functions.php';

final class CreateTest extends TestCase
{
    private mysqli $conn;

    protected function setUp(): void
    {
        require_once __DIR__ . '/../db.php';
        /** @var mysqli $conn */
        $this->conn = $conn;
    }

    protected function tearDown(): void
    {
        $this->conn->query("DELETE FROM users WHERE email = 'test.create@example.com'");
        $this->conn->close();
    }

    public function testCreateUserInsertsRowAndReturnsId(): void
    {
        $id = createUser($this->conn, 'Test Create', 'test.create@example.com');

        $this->assertGreaterThan(0, $id);

        $stored = getUserById($this->conn, $id);
        $this->assertNotNull($stored);
        $this->assertSame('Test Create', $stored['name']);
        $this->assertSame('test.create@example.com', $stored['email']);
    }

    public function testCreateUserAppearsInGetAllUsers(): void
    {
        $id = createUser($this->conn, 'Test Create 2', 'test.create@example.com');

        // getUserById is a targeted lookup and has proven reliable;
        // use it as the source of truth for "did the insert really land".
        $this->assertNotNull(getUserById($this->conn, $id));

        $all = getAllUsers($this->conn);
        $emails = array_column($all, 'email');

        $this->assertContains('test.create@example.com', $emails);
    }
}
