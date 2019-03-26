<?php

// Autoload
include '../vendor/autoload.php';

// Define used classes
use Dotenv\Dotenv;
use LatestAndGreatest\Networks\YouTube;
use LatestAndGreatest\Networks\Twitter;
use LatestAndGreatest\Networks\Facebook;
use LatestAndGreatest\Networks\Instagram;
use LatestAndGreatest\Networks\Pinterest;

// Initialise Dotenv
$dotenv = Dotenv::create(dirname(__DIR__));
$dotenv->load();

// Default LAG Parameters
$args = [
    'cacheDir' => dirname(__DIR__) . '/cache/', // cache location (ensure it exists with correct permissions)
    'max' => 1 // How many posts do you want to return with the 'getLatest()' method?
];

// Facebook
$fb = new Facebook($args);
print_r('<pre>');
print_r($fb->getProfile());
print_r($fb->getStats());
print_r($fb->getLatest());
print_r('</pre>');

// Instagram
$in = new Instagram($args);
print_r('<pre>');
print_r($in->getProfile());
print_r($in->getStats());
print_r($in->getLatest());
print_r('</pre>');

// YouTube
$yt = new YouTube($args);
print_r('<pre>');
print_r($yt->getProfile());
print_r($yt->getStats());
print_r($yt->getLatest());
print_r('</pre>');

// Twitter
$tw = new Twitter($args);
print_r('<pre>');
print_r($tw->getProfile());
print_r($tw->getStats());
print_r($tw->getLatest());
print_r('</pre>');

// // Pinterest
$pin = new Pinterest($args);
print_r('<pre>');
print_r($pin->getProfile());
print_r($pin->getStats());
print_r($pin->getLatest());
print_r('</pre>');
