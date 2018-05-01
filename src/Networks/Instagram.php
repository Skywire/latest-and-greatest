<?php

namespace LatestAndGreatest\Networks;

use LatestAndGreatest\LatestAndGreatest;
use Exception;

/**
 * Extend Latest And Greatest To Support Instagram
 */
class Instagram extends LatestAndGreatest {
    /**
     * Define the cache filename
     * @var String
     */
    protected $cacheFileName = 'lag--instagram.json';

    /**
     * Initalise the API secret variable
     * @var String
     */
    protected $userName;

    /**
     * Define the API end point
     * @var String
     */
    protected $endpoint = 'https://api.instagram.com/v1/';

    /**
     * Prepare endpoint response variable
     * @var Object
     */
    protected $endpointResponseUserData;

    /**
     * Prepare endpoint response variable
     * @var Object
     */
    protected $endpointResponseUserMedia;

    /**
     * This fires when instance created
     */
    public function __construct($options = []) {
        try {
            parent::__construct($options);
            $this->setUserName();
            $this->setAccessToken();
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
     * Set the page name
     */
    public function setUserName() {
        if (!getenv('INSTAGRAM_USERNAME')) {
            throw new Exception('No INSTAGRAM_USERNAME defined in your .env');
        }

        $this->userName = getenv('INSTAGRAM_USERNAME');
    }

    /**
     * Get the page name
     */
    public function getUserName() {
        return $this->userName;
    }

    /**
     * Set the access token
     */
    public function setAccessToken() {
        if (!getenv('INSTAGRAM_ACCESS_TOKEN')) {
            throw new Exception('No INSTAGRAM_ACCESS_TOKEN defined in your .env');
        }

        $this->accessToken = getenv('INSTAGRAM_ACCESS_TOKEN');
    }

    /**
     * Get the access token
     */
    public function getAccessToken() {
        return $this->accessToken;
    }

    /**
     * Get the Instagram API User endpoint
     * @return String
     */
    public function getUserDataApiEndpoint() {
        $url = 'users/self';

        $query = [
            'access_token' => $this->getAccessToken()
        ];

        return $this->endpoint . $url . '/?' . urldecode(http_build_query($query));
    }

    /**
     * Get endpoint response
     * @return Object
     */
    public function getUserDataEndpointResponse() {
        if (!$this->endpointResponseUserData) {
            // Get data from API
            $endpointResult = @file_get_contents($this->getUserDataApiEndpoint());
            if (!$endpointResult) {
                throw new Exception('No data returned from endpoint');
            }

            // Convert to object
            $this->endpointResponseUserData = json_decode($endpointResult);
        }

        return $this->endpointResponseUserData;
    }

    /**
     * Get the Instagram API User endpoint
     * @return String
     */
    public function getUserMediaApiEndpoint() {
        $url = 'users/self/media/recent';

        $query = [
            'access_token' => $this->getAccessToken()
        ];

        return $this->endpoint . $url . '/?' . urldecode(http_build_query($query));
    }

    /**
     * Get endpoint response
     * @return Object
     */
    public function getUserMediaEndpointResponse() {
        if (!$this->endpointResponseUserMedia) {
            // Get data from API
            $endpointResult = @file_get_contents($this->getUserMediaApiEndpoint());
            if (!$endpointResult) {
                throw new Exception('No data returned from endpoint');
            }

            // Convert to object
            $this->endpointResponseUserMedia = json_decode($endpointResult);
        }

        return $this->endpointResponseUserMedia;
    }

    /**
     * Get page profile array
     * @return Array
     */
    public function getProfileArray() {
        $array = [
            'username' => $this->getUserName()
        ];

        // Get profile image data from API
        $endpointResult = $this->getUserDataEndpointResponse();

        if ($endpointResult) {
            // Get image as data string
            $imageDataString = @file_get_contents($endpointResult->data->profile_picture);

            // Get image dimensions and mime type
            $imageData = getimagesizefromstring($imageDataString);

            // Build relevant array
            $array['picture'] = [
                'width' => $imageData[0],
                'height' => $imageData[0],
                'src' => 'data:'. $imageData['mime'] .';base64,'. base64_encode($imageDataString)
            ];
        }

        return $array;
    }

    /**
     * Get releavant statistics
     * @return Array
     */
    public function getStatisticsArray() {
        // Get api data
        $endpointResult = $this->getUserDataEndpointResponse();

        // Create statistics array
        $statistics = [
            'followers' => $endpointResult->data->counts->followed_by,
            'following' => $endpointResult->data->counts->follows
        ];

        return $statistics;
    }

    /**
     * Get an array of posts
     * @return Array
     */
    public function getPostsArray() {
        // Get api data
        $endpointResult = $this->getUserMediaEndpointResponse();

        // Create posts array
        $posts = [];
        $index = 0;
        foreach ($endpointResult->data as $post) {
            $posts[] = [
                'id' => $post->id,
                'link' => $post->link,
                'text' => isset($post->caption->text) ? $post->caption->text : '',
                'date' => $post->created_time,
                'likes' => $post->likes->count,
                'comments' => $post->comments->count,
                'media' => [
                    'thumbnail' => $post->images->standard_resolution->url,
                    'width' => $post->images->standard_resolution->width,
                    'height' => $post->images->standard_resolution->height
                ]
            ];

            $index ++;
            if ($index === $this->maxResults) {
                break;
            }
        }

        return $posts;
    }
}
