<?php

use PHPUnit\Framework\TestCase;
use App\Infrastructure\Database\Database;

class DatabaseTest extends TestCase
{
    protected function setUp(): void
    {
        $_ENV['APP_ENV'] = 'test';
    }

    public function testGetInstanceReturnsPDO()
    {
        $db = Database::getInstance();
        $this->assertInstanceOf(PDO::class, $db);
    }

    public function testGetInstanceIsSingleton()
    {
        $db1 = Database::getInstance();
        $db2 = Database::getInstance();
        $this->assertSame($db1, $db2);
    }
}
