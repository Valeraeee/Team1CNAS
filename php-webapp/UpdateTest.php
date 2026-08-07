<?php

use PHPUnit\Framework\TestCase;

/**
 * Tests update.php AS-IS (unmodified) the same way CreateTest does:
 * a real HTTP POST against PHP's built-in server, then checking the
 * database directly for the resulting change.
 */
final class UpdateTest extends TestCase
{
    private static $serverProcess;
    private static string $baseUrl = 'http://127.0.0.1:8099';
    private mysqli $conn;
    private int $userId;

    public static function setUpBeforeClass(): void
    {
        $docRoot = realpath(__DIR__ . '/..');
        $cmd = sprintf('php -S 127.0.0.1:8099 -t %s', escapeshellarg($docRoot));
        self::$serverProcess = proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);

        usleep(500000);
    }

    public static function tearDownAfterClass(): void
    {
        if (is_resource(self::$serverProcess)) {
            proc_terminate(self::$serverProcess);
            proc_close(self::$serverProcess);
        }
    }

    protected function setUp(): void
    {
        require __DIR__ . '/../db.php';
        /** @var mysqli $conn */
        $this->conn = $conn;

        // Seed a row directly so we have a known id to update.
        $this->conn->query(
            "INSERT INTO users (name, email) VALUES ('Before Update', 'test.update@example.com')"
        );
        $this->userId = $this->conn->insert_id;
    }

    protected function tearDown(): void
    {
        $this->conn->query("DELETE FROM users WHERE id = {$this->userId}");
        $this->conn->close();
    }

    public function testUpdatePhpChangesRow(): void
    {
        $postData = http_build_query([
            'name'  => 'After Update',
            'email' => 'updated@example.com',
        ]);

        $context = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => $postData,
            ],
        ]);

        file_get_contents(self::$baseUrl . '/update.php?id=' . $this->userId, false, $context);

        $result = $this->conn->query("SELECT * FROM users WHERE id = {$this->userId}");
        $row = $result->fetch_assoc();

        $this->assertSame('After Update', $row['name']);
        $this->assertSame('updated@example.com', $row['email']);
    }
}
