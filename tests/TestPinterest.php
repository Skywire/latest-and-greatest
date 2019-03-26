<?php

// Autoload
include 'vendor/autoload.php';

// Define used classes
use PHPUnit\Framework\TestCase;
use Dotenv\Dotenv;
use LatestAndGreatest\Networks\Pinterest;

// Initialise Dotenv
$dotenv = Dotenv::create(dirname(__DIR__));
$dotenv->load();

class TestPinterest extends TestCase {
    private $lag;

    protected function setUp() {
        $this->lag = new Pinterest();
    }

    public function testStats() {
        $result = $this->lag->getStats();

        $this->assertObjectHasAttribute('followers', $result);
        $this->assertObjectHasAttribute('following', $result);
        $this->assertObjectHasAttribute('pins', $result);
    }

    public function testLatest() {
        $result = $this->lag->getLatest();

        $this->assertInternalType('array', $result);
        $this->assertNotEmpty($result);
    }
}
