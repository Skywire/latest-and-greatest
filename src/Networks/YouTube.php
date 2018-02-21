<?php

namespace LatestAndGreatest\Networks;

use LatestAndGreatest\LatestAndGreatest;
use Exception;

/**
 * Extend Latest And Greatest To Support YouTube
 */
class YouTube extends LatestAndGreatest {
    /**
     * Define the cache filename
     * @var String
     */
    protected $cacheFileName = 'lag--youtube.json';

    /**
     * Initalise the Google Api Key variable
     * @var String
     */
    protected $apiKey;

    /**
     * Initalise the Youtube Channel ID variable
     * @var String
     */
    protected $channelID;

    /**
     * Define the API end point
     * @var String
     */
    protected $endpoint = 'https://www.googleapis.com/youtube/v3/';

    /**
     * This fires when instance created
     */
    public function __construct($options = []) {
        try {
            parent::__construct($options);
            $this->setApiKey();
            $this->setChannelID();
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
     * Get the video API enpoint
     * @return String
     */
    public function getVideoApiEndpoint() {
        $args = [
            'key' => $this->apiKey,
            'channelId' => $this->channelID,
            'part' => 'snippet',
            'maxResults' => $this->maxResults,
            'order' => 'date',
            'type' => 'video'
        ];

        return $this->endpoint . 'search?' . http_build_query($args);
    }

    /**
     * Get the video statistics API enpoint
     * @return String
     */
    public function getVideoStatisticsApiEndpoint($videoId) {
        $args = [
            'key' => $this->apiKey,
            'id' => $videoId,
            'part' => 'statistics'
        ];

        return $this->endpoint . 'videos?' . http_build_query($args);
    }

    /**
     * Get the YouTube channel statistics API enpoint
     * @return String
     */
    public function getChannelStatisticsApiEndpoint() {
        $args = [
            'key' => $this->apiKey,
            'id' => $this->channelID,
            'part' => 'statistics'
        ];

        return $this->endpoint . 'channels?' . http_build_query($args);
    }

    /**
     * Set the API Key
     */
    public function setApiKey() {
        if (!getenv('GOOGLE_API_KEY')) {
            throw new Exception('No GOOGLE_API_KEY key defined in your .env');
        }

        $this->apiKey = getenv('GOOGLE_API_KEY');
    }

    /**
     * Set the Channel ID
     */
    public function setChannelID() {
       if (!getenv('YOUTUBE_CHANNELID')) {
           throw new Exception('No YOUTUBE_CHANNELID defined in your .env');
       }

       $this->channelID = getenv('YOUTUBE_CHANNELID');
    }

    /**
     * Get the defined channels latest videos
     * @return Array
     */
    public function getPostsArray() {
        // Get data from API
        $endpointResult = @file_get_contents($this->getVideoApiEndpoint());

        if (!$endpointResult) {
            throw new Exception('No data returned from endpoint');
        }

        // Convert latest video data to object
        // Dump this if you want to see full response from API
        $object = json_decode($endpointResult);

        // Create usable data array
        $array = [];
        foreach ($object->items as $key => $video) {

            $array[$key] = [
                'videoId' => $video->id->videoId,
                'title' => $video->snippet->title,
                'description' => $video->snippet->description,
                'thumbnail' => [
                    'src' => $video->snippet->thumbnails->high->url,
                    'width' => $video->snippet->thumbnails->high->width,
                    'height' => $video->snippet->thumbnails->high->height
                ]
            ];

            $videoStatisticsResult = @file_get_contents($this->getVideoStatisticsApiEndpoint($video->id->videoId));

            if (!$videoStatisticsResult) {
                continue;
            }

            $videoStatisticsResultObject = json_decode($videoStatisticsResult);

            foreach ($videoStatisticsResultObject->items as $videoItem) {
                $array[$key]['views'] = $videoItem->statistics->viewCount;
                $array[$key]['likes'] = $videoItem->statistics->likeCount;
                $array[$key]['dislikes'] = $videoItem->statistics->dislikeCount;
                $array[$key]['favourites'] = $videoItem->statistics->favoriteCount;
                $array[$key]['comments'] = $videoItem->statistics->commentCount;
            }
        }

        return $array;
    }

    /**
     * Get channel statistics
     * @return Array An array of usable data
     */
    public function getStatisticsArray() {
        // Get data from API
        $endpointResult = @file_get_contents($this->getChannelStatisticsApiEndpoint());
        if (!$endpointResult) {
            throw new Exception('No data returned from endpoint');
        }

        // Convert latest video data to object
        // Note: Print this if you want to see full response from API
        $object = json_decode($endpointResult);

        // Create usable data array
        $array = [
            'videos' => $object->items[0]->statistics->videoCount,
            'views' => $object->items[0]->statistics->viewCount,
            'subscribers' => $object->items[0]->statistics->subscriberCount
        ];

        return $array;
    }
}
