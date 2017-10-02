<?php

// Autoload
include 'vendor/autoload.php';

// Define used classes
use PHPUnit\Framework\TestCase;
use Dotenv\Dotenv;
use LatestAndGreatest\Networks\YouTube;

// Initialise Dotenv
$dotenv = new Dotenv(dirname(__DIR__));
$dotenv->load();

class TestYoutube extends TestCase {
    private $lag;

    protected function setUp() {
        $this->lag = new YouTube();
    }

    public function testStats() {
        $result = $this->lag->getStats();

        $this->assertObjectHasAttribute('videos', $result);
        $this->assertObjectHasAttribute('views', $result);
        $this->assertObjectHasAttribute('subscribers', $result);
    }

    public function testLatest() {
        $result = $this->lag->getLatest();

        $this->assertInternalType('array', $result);
        $this->assertNotEmpty($result);
    }
}
