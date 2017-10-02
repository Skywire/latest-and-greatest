<?php

namespace LatestAndGreatest\Networks;

use LatestAndGreatest\LatestAndGreatest;
use Abraham\TwitterOAuth\TwitterOAuth;
use Exception;

/**
 * Extend Latest And Greatest To Support Twitter
 */
class Twitter extends LatestAndGreatest {
    /**
     * Define the cache filename
     * @var String
     */
    protected $cacheFileName = 'lag--twitter.json';

    /**
     * Initalise the Twitter API key variable
     * @var String
     */
    protected $apiKey;

    /**
     * Initalise the Twitter API secret variable
     * @var String
     */
    protected $apiSecret;

    /**
     * Initalise the Twitter access token variable
     * @var String
     */
    protected $accessToken;

    /**
     * Initalise the Twitter access token secret variable
     * @var String
     */
    protected $accessTokenSecret;

    /**
     * Initalise the Twitter oAuth connection variable
     * @var Object
     */
    protected $connection;

    /**
     * This fires when instance created
     */
    public function __construct($options = []) {
        try {
            parent::__construct($options);
            $this->setApiKey();
            $this->setApiSecret();
            $this->setAccessToken();
            $this->setAccessTokenSecret();
            $this->createConnection();
            parent::init();
        } catch (Exception $e) {
            echo '<pre>';
            echo 'Message: ' . $e->getMessage(). PHP_EOL;
            echo 'File: ' . $e->getFile(). PHP_EOL;
            echo 'Line: ' . $e->getLine(). PHP_EOL;
            echo 'Trace:' . PHP_EOL . $e->getTraceAsString(). PHP_EOL;
            echo '</pre>';
        }
    }

    /**
     * Set the Twitter API key with the `TWITTER_API_KEY` defined in the .env file
     */
    public function setApiKey() {
        if (!getenv('TWITTER_API_KEY')) {
            throw new Exception('No TWITTER_API_KEY defined in your .env');
        }

        $this->apiKey = getenv('TWITTER_API_KEY');
    }

    /**
     * Set the Twitter API secret with the `TWITTER_API_SECRET` defined in the .env file
     */
    public function setApiSecret() {
        if (!getenv('TWITTER_API_SECRET')) {
            throw new Exception('No TWITTER_API_SECRET defined in your .env');
        }

        $this->apiSecret = getenv('TWITTER_API_SECRET');
    }

    /**
     * Set the Twitter access token with the `TWITTER_ACCESS_TOKEN` defined in the .env file
     */
    public function setAccessToken() {
        if (!getenv('TWITTER_ACCESS_TOKEN')) {
            throw new Exception('No TWITTER_ACCESS_TOKEN defined in your .env');
        }

        $this->accessToken = getenv('TWITTER_ACCESS_TOKEN');
    }

    /**
     * Set the Twitter access token secret with the `TWITTER_ACCESS_TOKEN_SECRET` defined in the .env file
     */
    public function setAccessTokenSecret() {
        if (!getenv('TWITTER_ACCESS_TOKEN_SECRET')) {
            throw new Exception('No TWITTER_ACCESS_TOKEN_SECRET defined in your .env');
        }

        $this->accessTokenSecret = getenv('TWITTER_ACCESS_TOKEN_SECRET');
    }

    /**
     * Create a connection to the api
     */
    public function createConnection() {
        $this->connection = new TwitterOAuth(
            $this->apiKey,
            $this->apiSecret,
            $this->accessToken,
            $this->accessTokenSecret
        );
    }

    /**
     * Get the statistics
     * @return Array
     */
    public function getStatisticsArray() {
        $userData = $this->connection->get('account/verify_credentials', [
            'include_rts' => false,
            'exclude_replies' => true,
            'include_entities' => false,
            'skip_status' => true,
            'include_email' => false
        ]);

        // Create usable data array
        $array = [
            'followers' => $userData->followers_count,
            'friends' => $userData->friends_count,
            'favourites' => $userData->favourites_count
        ];

        return $array;
    }

    /**
     * Get the users latest tweets
     * @return Array
     */
    public function getPostsArray() {
        // Fetch a bigger list of tweets than we actually need.
        //
        // We do this as if we were to fetch 2 ('count' => 2), it doesn't
        // guarentee to always return 2 results.
        // It would appear the api fetches the desired amount first then
        // filters the result with the defined parameters, thus removing some
        // results from the desired amount.
        $tweets = $this->connection->get('statuses/user_timeline', [
            'include_rts' => false,
            'exclude_replies' => true,
            'include_entities' => false,
            'tweet_mode' => 'extended',
            'count' => 40 // We deliberatly get more than required here
        ]);

        // Shrink array
        $latestTweets = array_slice($tweets, 0, $this->maxResults);

        // Create usable data array
        $array = [];
        foreach ($latestTweets as $tweet) {

            $array[$tweet->id] = [
                'id' => $tweet->id,
                'text' => $tweet->full_text,
                'date' => strtotime($tweet->created_at),
                'url' => 'http://twitter.com/statuses/' . $tweet->id
            ];

            // Is there media attached to the post?
            if (isset($tweet->extended_entities->media[0])) {
                $array[$tweet->id]['media'] = [
                    'thumbnail' => $tweet->extended_entities->media[0]->media_url_https . ':large',
                    'width' => $tweet->extended_entities->media[0]->sizes->large->w,
                    'height' => $tweet->extended_entities->media[0]->sizes->large->h
                ];
            }
        }

        // Remove named keys
        $array = array_values($array);

        return $array;
    }
}
