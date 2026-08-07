<?php

use PHPUnit\Framework\TestCase;

/**
 * Tests create.php AS-IS (unmodified) by running it through PHP's
 * built-in web server and making a real POST request to it.
 *
 * This is necessary because create.php calls exit() after a
 * successful POST -- including it directly in a PHPUnit process
 * would kill the test run. Running it as a separate HTTP process
 * sidesteps that entirely.
 */
final class CreateTest extends TestCase
{
    private static $serverProcess;
    private static string $baseUrl = 'http://127.0.0.1:8099';
    private mysqli $conn;

    public static function setUpBeforeClass(): void
    {
        $docRoot = realpath(__DIR__ . '/..');
        $cmd = sprintf('php -S 127.0.0.1:8099 -t %s', escapeshellarg($docRoot));
        self::$serverProcess = proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);

        // Give the built-in server a moment to start listening.
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
    }

    protected function tearDown(): void
    {
        $this->conn->query("DELETE FROM users WHERE email = 'test.create@example.com'");
        $this->conn->close();
    }

    public function testCreatePhpInsertsNewRow(): void
    {
        $postData = http_build_query([
            'name'  => 'Test Create',
            'email' => 'test.create@example.com',
        ]);

        $context = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => $postData,
            ],
        ]);

        // create.php redirects to index.php on success; we don't care
        // about the response body, only the resulting DB state.
        file_get_contents(self::$baseUrl . '/create.php', false, $context);

        $result = $this->conn->query("SELECT * FROM users WHERE email = 'test.create@example.com'");
        $row = $result->fetch_assoc();

        $this->assertNotNull($row, 'Expected create.php to insert a new row');
        $this->assertSame('Test Create', $row['name']);
    }
}
