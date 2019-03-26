# Latest & Greatest
Get stats and latest posts from various social media, including Facebook, Twitter, Instagram, YouTube and Pinterest.

---
---

## Installation
### Composer
1. Add the project repository to your project composer.json:
```
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/ozpital/latest-and-greatest.git"
    }
]
```

2. Require the **latest-and-greatest** project
```
"require": {
    "ozpital/latest-and-greatest": "*"
}
```

3. Run composer update in the terminal
```
composer update
```

This should fetch the project ready for use.

---
---

## Usage
### Quick Start
```
<?php

// Autoload
include 'vendor/autoload.php';

// Define used classes
use Dotenv\Dotenv;
use LatestAndGreatest\Networks\YouTube;
use LatestAndGreatest\Networks\Twitter;
use LatestAndGreatest\Networks\Facebook;
use LatestAndGreatest\Networks\Instagram;
use LatestAndGreatest\Networks\Pinterest;

// Initialise Dotenv
$dotenv = new Dotenv(__DIR__);
$dotenv->load();

// Default LAG Parameters
$args = [
    'cacheDir' => __DIR__, // cache location (ensure it exists with correct permissions)
    'max' => 2 // How many posts do you want to return with the 'getLatest()' method?
];

// YouTube
$yt = new YouTube($args);
print_r($yt->getStats());
print_r($yt->getLatest());

// Twitter
$tw = new Twitter($args);
print_r($tw->getStats());
print_r($tw->getLatest());

// Facebook
$fb = new Facebook($args);
print_r($fb->getStats());
print_r($fb->getLatest());

// Instagram
$in = new Instagram($args);
print_r($in->getStats());
print_r($in->getLatest());

// Pinterest
$pin = new Pinterest($args);
print_r($pin->getStats());
print_r($pin->getLatest());
```

---
### Slow Start
#### Global
1. As always, be sure to include the `composer autoload` script by including the following in your script as normal.
```
include 'vendor/autoload.php';
```

2. **Latest and Greatest** takes advantage of the [Dotenv](https://github.com/vlucas/phpdotenv) package.  
We must initialise Dotenv with the following:
```
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();
```
The above will load the variables defined in the `.env` file located in the root of your project.

---

#### Facebook
Facebook currently requires an **APP ID**, **APP SECRET** and the **PAGE NAME** you would like to retrieve stats and posts from.  
These variables are defined in the `.env` file as described under *Usage > Global*.
```
FACEBOOK_APP_ID=XXXXX
FACEBOOK_APP_SECRET=XXXXX
FACEBOOK_PAGE_NAME=XXXXX
```
You can obtain an `APP_ID` and `APP_SECRET` by creating an app at https://developers.facebook.com.   
Simply having an `APP_ID` and `APP_SECRET` should be enough for this to work.

The `PAGE_NAME` is the Facebook page identifier - Just as you find in the url of your desired Facebook page.  
eg: https://www.facebook.com/`pepsi` or https://www.facebook.com/`DJFurney`.

##### Initialisation
```
<?php

use LatestAndGreatest\Networks\Facebook;

$fb = new Facebook();
$fb->getStats();
$fb->getLatest();
```

---

#### Instagram
Luckily, there's a public endpoint that squirts out JSON for Instagram to get the data we're after.  

The only variable we need to define in the `.env` is a username.   
```
INSTAGRAM_USERNAME=XXXXX
INSTAGRAM_ACCESS_TOKEN=XXXXX
```

##### Initialisation
```
<?php

use LatestAndGreatest\Networks\Instagram;

$insta = new Instagram();
$insta->getStats();
$insta->getLatest();
```

---

#### Pinterest
##### Requirements
```
php-dom
php-xml
```

I've done some poking around Pinterest to find a public access point for the data we're after, there's no public JSON feed unfortunately.  
The API is also bit of a nightmare for our use case so I've worked around that for now.  

Interestingly, I'm actually pulling meta tag data from the HTML of the user page for the statistics.   
I'm also using the XML RSS feed for the latest posts. *Check out the source code to see what's going on behind the scenes on this one.*

The only variable we need to define in the `.env` is a username.   
```
PINTEREST_USERNAME=XXXXX
```

##### Initialisation
```
<?php

use LatestAndGreatest\Networks\Pinterest;

$pin = new Pinterest();
$pin->getStats();
$pin->getLatest();
```

---

#### Twitter
We're using the [TwitterOAuth](https://twitteroauth.com/) package here.  
It's very good and full of functionality beyond the remit of this package.

To get Twitter working we need to create an app at https://apps.twitter.com/ and copy the API Key, API Secret, Access Token and Access Token Secret into our `.env` file.
```
TWITTER_API_KEY=XXXXX
TWITTER_API_SECRET=XXXXX
TWITTER_ACCESS_TOKEN=XXXXX
TWITTER_ACCESS_TOKEN_SECRET=XXXXX
```

##### Initialisation
```
<?php

use LatestAndGreatest\Networks\Twitter;

$tw = new Twitter();
$tw->getStats();
$tw->getLatest();
```

---

### YouTube
For YouTube stats and latest videos we need to define a Google API Key *(...with YouTube enabled in the Google developer console)*.

We also require a YouTube channel ID to target.
```
GOOGLE_API_KEY=XXXXX
YOUTUBE_CHANNELID=XXXXX
```

##### Initialisation
```
<?php

use LatestAndGreatest\Networks\YouTube;

$yt = new YouTube();
$yt->getStats();
$yt->getLatest();
```
