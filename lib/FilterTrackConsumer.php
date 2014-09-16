<?php

class FilterTrackConsumer extends OauthPhirehose
{
    static $単位時間 = 3; // sec
    static $波動砲発射閾値 = 10; // tweet
    private $前回の時刻 = 0; // microtime
    private $現在流量 = 0; // tweet
    private $zmq;

    public function __construct($username, $password, $method = Phirehose::METHOD_SAMPLE, $format = self::FORMAT_JSON, $lang = FALSE)
    {
        $this->前回の時刻 = microtime(true);
        echo 'start time' . $this->前回の時刻 . "\n";

        $context = new \ZMQContext();
        $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'hadoch');
        $socket->connect("tcp://localhost:5555");
        $this->zmq = $socket;

        parent::__construct($username, $password, $method, $format, $lang);
    }

    public function enqueueStatus($status)
    {
        $data = json_decode($status, true);
        if (!is_array($data) || !isset($data['user']['screen_name'])) {
            return; // invalid data?
        }

        $今の時刻 = microtime(true);
        $差分 = $今の時刻 - $this->前回の時刻;

        $tweet = [
            'screen_name' => $data['user']['screen_name'],
            'profile_image_url' => $data['user']['profile_image_url'],
            'text' => urldecode($data['text']),
            'created_at' => $data['created_at'],
            'timestamp_ms' => $data['timestamp_ms'],
            'id_str' => $data['id_str'],
        ];

        $data = ['tweet' => $tweet];

        if ($差分 < static::$単位時間) {
            $this->現在流量++;
            $data['type'] = 'tweet';

        } else {
            // 判定
            if (static::$波動砲発射閾値 < $this->現在流量) {
                echo "波動砲発射!:" . $this->現在流量 . "\n";
                $data['type'] = 'hadogun_fire';
            } else {
                echo "現在流量:" . $this->現在流量 . "\n";
                $data['type'] = 'tweet';
            }
            $this->現在流量 = 0;
            $this->前回の時刻 = $今の時刻;
        }

        $this->zmq->send(json_encode($data));
        echo ".";
    }
}

//var_dump($data);
/*
 * array(25) {
  'created_at' =>
  string(30) "Wed Sep 10 04:57:08 +0000 2014"
  'id' =>
  int(509566219473915905)
  'id_str' =>
  string(18) "509566219473915905"
  'text' =>
  string(38) "@_SLIM___ where da blue Benjamin's bby"
  'source' =>
  string(82) "<a href="http://twitter.com/download/iphone" rel="nofollow">Twitter for iPhone</a>"
  'truncated' =>
  bool(false)
  'in_reply_to_status_id' =>
  int(509344259385077760)
  'in_reply_to_status_id_str' =>
  string(18) "509344259385077760"
  'in_reply_to_user_id' =>
  int(237983594)
  'in_reply_to_user_id_str' =>
  string(9) "237983594"
  'in_reply_to_screen_name' =>
  string(8) "_SLIM___"
  'user' =>
  array(38) {
    'id' =>
    int(314256118)
    'id_str' =>
    string(9) "314256118"
    'name' =>
    string(17) "Ms. PERFECT‼️"
    'screen_name' =>
    string(9) "CheifMela"
    'location' =>
    string(8) "Westside"
    'url' =>
    NULL
    'description' =>
    string(41) "ITS ME HOE‼️

TIP Redd 8.23.06❤❤"
    'protected' =>
    bool(false)
    'verified' =>
    bool(false)
    'followers_count' =>
    int(1495)
    'friends_count' =>
    int(616)
    'listed_count' =>
    int(5)
    'favourites_count' =>
    int(1170)
    'statuses_count' =>
    int(70744)
    'created_at' =>
    string(30) "Thu Jun 09 23:40:27 +0000 2011"
    'utc_offset' =>
    int(-18000)
    'time_zone' =>
    string(26) "Central Time (US & Canada)"
    'geo_enabled' =>
    bool(true)
    'lang' =>
    string(2) "en"
    'contributors_enabled' =>
    bool(false)
    'is_translator' =>
    bool(false)
    'profile_background_color' =>
    string(6) "000000"
    'profile_background_image_url' =>
    string(91) "http://pbs.twimg.com/profile_background_images/439188237/tumblr_lhr9irXKNJ1qf145uo1_500.jpg"
    'profile_background_image_url_https' =>
    string(92) "https://pbs.twimg.com/profile_background_images/439188237/tumblr_lhr9irXKNJ1qf145uo1_500.jpg"
    'profile_background_tile' =>
    bool(true)
    'profile_link_color' =>
    string(6) "0084B4"
    'profile_sidebar_border_color' =>
    string(6) "F56D05"
    'profile_sidebar_fill_color' =>
    string(6) "00070A"
    'profile_text_color' =>
    string(6) "F04A0E"
    'profile_use_background_image' =>
    bool(true)
    'profile_image_url' =>
    string(75) "http://pbs.twimg.com/profile_images/509035655939117057/iY8ODcln_normal.jpeg"
    'profile_image_url_https' =>
    string(76) "https://pbs.twimg.com/profile_images/509035655939117057/iY8ODcln_normal.jpeg"
    'profile_banner_url' =>
    string(58) "https://pbs.twimg.com/profile_banners/314256118/1408419714"
    'default_profile' =>
    bool(false)
    'default_profile_image' =>
    bool(false)
    'following' =>
    NULL
    'follow_request_sent' =>
    NULL
    'notifications' =>
    NULL
  }
  'geo' =>
  NULL
  'coordinates' =>
  NULL
  'place' =>
  NULL
  'contributors' =>
  NULL
  'retweet_count' =>
  int(0)
  'favorite_count' =>
  int(0)
  'entities' =>
  array(5) {
    'hashtags' =>
    array(0) {
    }
    'trends' =>
    array(0) {
    }
    'urls' =>
    array(0) {
    }
    'user_mentions' =>
    array(1) {
      [0] =>
      array(5) {
        ...
      }
    }
    'symbols' =>
    array(0) {
    }
  }
  'favorited' =>
  bool(false)
  'retweeted' =>
  bool(false)
  'possibly_sensitive' =>
  bool(false)
  'filter_level' =>
  string(6) "medium"
  'lang' =>
  string(2) "en"
  'timestamp_ms' =>
  string(13) "1410325028668"
}
 */
