<?php

// Autoload
include 'vendor/autoload.php';

// Define used classes
use PHPUnit\Framework\TestCase;
use Dotenv\Dotenv;
use LatestAndGreatest\Networks\Facebook;

// Initialise Dotenv
$dotenv = new Dotenv(dirname(__DIR__));
$dotenv->load();

class TestFacebook extends TestCase {
    private $lag;

    protected function setUp() {
        $this->lag = new Facebook();
    }

    public function testStats() {
        $result = $this->lag->getStats();

        $this->assertObjectHasAttribute('likes', $result);
    }

    public function testLatest() {
        $result = $this->lag->getLatest();

        $this->assertInternalType('array', $result);
        $this->assertNotEmpty($result);
    }
}
