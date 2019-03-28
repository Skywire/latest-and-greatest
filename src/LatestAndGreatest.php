<?php

namespace LatestAndGreatest;

use Exception;

/**
 * Latest And Greatest
 *
 * Connect to your social media and grab information to use in your site.
 * Including:
 * - Latest Post(s)
 * - Follower/Subscriber count
 */
class LatestAndGreatest {
    /**
     * Default cache state
     * @var String
     */
    protected $cacheEnabled = true;

    /**
     * Default cache directory
     * @var String
     */
    protected $cacheDirectory = './cache/';

    /**
     * Initalise Cache Filename
     * @var String
     */
    protected $cacheFilename;

    /**
     * Default cache duration in seconds
     * @var Integer
     */
    protected $cacheDuration = (60 * 60);

    /**
     * Default max results amount
     * @var Integer
     */
    protected $maxResults = 1;

    /**
     * Initalise the data variable
     * @var Array
     */
    protected $data;

    public function __construct($options = []) {
        // If max results defined
        if (isset($options['max'])) {
            $this->maxResults = $options['max'];
        }

        // If cache state is defined
        if (isset($options['cache'])) {
            $this->cacheEnabled = $options['cache'];
        }

        // If cache directory defined
        if (isset($options['cacheDir'])) {
            $this->cacheDirectory = $options['cacheDir'];
        }

        // Populate data variable
        $this->data = $this->loadData();
    }

    /**
     * Initalise the specific data set
     * @deprecated class initialised in construct.
     */
    public function init() {
    }

    /**
     * Check is a cache update is required
     * @return boolean
     */
    protected function isUpdateRequired() {
        // Does the file exist?
        if (!file_exists($this->getCacheDirectory() . $this->cacheFileName)) {
            // Make file
            touch($this->getCacheDirectory() . $this->cacheFileName);
            return true;
        }

        // Is it empty?
        if (!filesize($this->getCacheDirectory() . $this->cacheFileName)) {
           return true;
        }

        // Is the file age over the threshold?
        if ((time() - $this->getCacheDuration()) > filemtime($this->getCacheDirectory() . $this->cacheFileName)) {
            return true;
        }

        return false;
    }

    /**
     * Get cached data
     *
     * @return object
     */
    protected function getCachedData(){
        return json_decode(file_get_contents($this->getCacheDirectory() . $this->cacheFileName), true);
    }

    /**
     * Update the cache file
     */
    protected function updateCache($data = []) {
        // Convert to json and save to cache file
        file_put_contents($this->getCacheDirectory() . $this->cacheFileName, json_encode($data, JSON_FORCE_OBJECT));
    }

    /**
     * Delete the desired cache
     */
    protected function deleteCache() {
        // Does the file exist?
        if (file_exists($this->getCacheDirectory() . $this->cacheFileName)) {
            // Delete the file
            unlink($this->getCacheDirectory() . $this->cacheFileName);
        }
        // Empty data variable
        if ($this->data) {
            unset($this->data);
        }
    }

    protected function loadData() {
        $data = [
            'profile' => [],
            'statistics' => [],
            'latest' => []
        ];

        if ($this->cacheEnabled && !$this->isUpdateRequired()) {
            $data = $this->getCachedData();
        } else {
            // Get current stats
            $data['profile'] = $this->getProfileArray();
            // Get current stats
            $data['statistics'] = $this->getStatisticsArray();
            // Get latest data
            $data['latest'] = $this->getPostsArray();
            if ($this->cacheEnabled) {
                $this->updateCache($data);
            }
        }

        return $data;
    }

    /**
     * Get the cached data
     * @return Array The stored data
     */
    public function getData() {
        return $this->data;
    }

    /**
     * Get profile
     * @return Array
     */
    public function getProfile() {
        return $this->data['profile'];
    }

    /**
     * Get page/profile stats
     * @return Array
     */
    public function getStats() {
        return $this->data['statistics'];
    }

    /**
     * Get the latest posts
     * @return Array
     */
    public function getLatest() {
        return $this->data['latest'];
    }


    /**
     * Get cache directory
     * @return String The currently defined cache directory
     */
    public function getCacheDirectory() {
        if (!is_dir($this->cacheDirectory)) {
            throw new Exception('Invalid cache directory');
        }

        return $this->cacheDirectory;
    }

    /**
     * Set the cache file name
     * @param String $filename A valid string that can be used as a filename for the cache
     */
    public function setCacheFilename($filename) {
        $this->cacheFilename = $filename;
    }

    /**
     * Get the cache filename
     * @return String The defined filename for the current cache
     */
    public function getCacheFilename() {
        if (!$this->cacheFilename) {
            throw new Exception('No cache filename set');
        }

        return $this->cacheFilename;
    }

    /**
     * Update the default duration period
     * @param Integer $duration A number in seconds
     */
    public function setCacheDuration($duration) {
        if (!is_int($duration)) {
            throw new Exception('Invalid cache duration');
        }

        $this->cacheDuration = $duration;
    }

    /**
     * Set the max results your want to fetch from the desired API
     * @param Number $amount
     */
    public function setMaxResults($amount) {
        if (!is_int($amount)) {
            throw new Exception('Invalid number');
        }

        $this->maxResults = $amount;
    }

    /**
     * Get the cache duration
     * @return Integer The currently defined cache duration in seconds
     */
    public function getCacheDuration() {
        return $this->cacheDuration;
    }

    /**
     * Get page profile array
     * @return Array
     */
    protected function getProfileArray() {
        return [];
    }

    /**
     * Get releavant statistics
     * @return Array
     */
    protected function getStatisticsArray() {
        return [];
    }

    /**
     * Get an array of posts
     * @return Array
     */
    protected function getPostsArray() {
        return [];
    }
}
