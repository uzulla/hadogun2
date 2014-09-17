<?php
namespace Hadogun2;

use \Uzulla\SLog\SimpleLogger;

class Collector extends \OauthPhirehose
{

    private $zmq;
    private $log;

    public function __construct($username, $password, $method = \Phirehose::METHOD_SAMPLE, $format = self::FORMAT_JSON, $lang = FALSE)
    {

        $context = new \ZMQContext();
        $socket = $context->getSocket(\ZMQ::SOCKET_PUSH, 'hadoch');
        $socket->connect("tcp://localhost:5555");
        $this->zmq = $socket;

        $this->log = new SimpleLogger(SimpleLogger::DEBUG, BASE_PATH.'/track.log');

        parent::__construct($username, $password, $method, $format, $lang);
    }

    public function enqueueStatus($status)
    {
        $data = json_decode($status, true);
        if (!is_array($data) || !isset($data['user']['screen_name'])) {
            return; // invalid data?
        }

        $data = ['tweet' => [
            'screen_name' => $data['user']['screen_name'],
            'profile_image_url' => $data['user']['profile_image_url'],
            'text' => urldecode($data['text']),
            'created_at' => $data['created_at'],
            'timestamp_ms' => $data['timestamp_ms'],
            'id_str' => $data['id_str'],
        ]];

        $this->zmq->send(json_encode($data));

        // logging tweet
        $this->log->debug("tweet: {$data['tweet']['screen_name']}: {$data['tweet']['id_str']} => ".
            preg_replace("/[\r\n]/u", '', urldecode($data['tweet']['text'])));
        echo ".";
    }
}
