<?php

// Autoload
include 'vendor/autoload.php';

// Define used classes
use PHPUnit\Framework\TestCase;
use Dotenv\Dotenv;
use LatestAndGreatest\Networks\Twitter;

// Initialise Dotenv
$dotenv = Dotenv::create(dirname(__DIR__));
$dotenv->load();

class TestTwitter extends TestCase {
    private $lag;

    protected function setUp() {
        $this->lag = new Twitter();
    }

    public function testStats() {
        $result = $this->lag->getStats();

        $this->assertObjectHasAttribute('followers', $result);
        $this->assertObjectHasAttribute('friends', $result);
        $this->assertObjectHasAttribute('favourites', $result);
    }

    public function testLatest() {
        $result = $this->lag->getLatest();

        $this->assertInternalType('array', $result);
        $this->assertNotEmpty($result);
    }
}
