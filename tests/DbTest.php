<?php declare(strict_types=1);

namespace Autoescape\Test;

use Autoescape\Db;
use Autoescape\UntrustedString;
use PDO;
use PHPUnit\Framework\TestCase;

class DbTest extends TestCase
{
    public function test_escape_parameters(): void
    {
        $escaped = new UntrustedString("O'Reilly");

        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('CREATE TABLE test (name TEXT)');
        $pdo->exec('INSERT INTO test (name) VALUES (\'O\'\'Reilly\')');
        $db = new Db($pdo);

        $results = $db->fetchAll("SELECT * FROM test WHERE name = $escaped");

        $this->assertIsArray($results);
        $this->assertCount(1, $results);
        $this->assertEquals("O'Reilly", $results[0]['name']);
    }
}
