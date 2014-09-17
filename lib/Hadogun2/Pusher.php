<?php

namespace Hadogun2;

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;
use Uzulla\SLog\SimpleLogger;

class Pusher implements WampServerInterface
{

    protected $subscribedTopics = [];
    protected $count = 0;
    private $log;

    static $単位時間 = 10; // sec
    static $波動砲発射閾値 = 10; // tweet
    static $リデュース = 0;
    private $前回の時刻 = 0; // microtime
    private $現在流量 = 0; // tweet

    public function __construct(){
        $this->前回の時刻 = microtime(true);
        echo 'start time' . $this->前回の時刻 . "\n";

        $this->log = $this->log = new SimpleLogger(SimpleLogger::DEBUG, BASE_PATH.'/push.log');
    }

    private $debug_count = 0;

    public function onSubscribe(ConnectionInterface $conn, $topic)
    {
        $this->subscribedTopics['hadoch'] = $topic;
    }

    public function onTweeter($json)
    {
        $data = json_decode($json, true);
        //var_dump($data);

        if(static::$リデュース>1) {
            // リデュース
            $this->debug_count++;
            if($this->debug_count%static::$リデュース!=0){
                return;
            }else{
                $this->debug_count=0;
            }
        }

        echo ".";

        if (!isset($this->subscribedTopics['hadoch'])) {
            return;
        }

        $今の時刻 = microtime(true);
        $差分 = $今の時刻 - $this->前回の時刻;

        if ($差分 < static::$単位時間) {
            $this->現在流量++;
            $data['type'] = 'tweet';

        } else {
            // 判定
            if (static::$波動砲発射閾値 < $this->現在流量) {
                $this->log->info("波動砲発射:" . $this->現在流量);
                echo "h";
                $data['type'] = 'hadogun_fire';
            } else {
                $this->log->info("現在流量:" . $this->現在流量);
                $data['type'] = 'tweet';
            }
            $this->現在流量 = 0;
            $this->前回の時刻 = $今の時刻;
        }

        if ($data['type'] == 'tweet') {
            // nop
        } else if ($data['type'] == 'hadogun_fire') {
            echo "波動砲発射！！";
            // var_dump($data['tweet']);
        }

        $topic = $this->subscribedTopics['hadoch'];
        $topic->broadcast($data);
        return;
    }

    public function onUnSubscribe(ConnectionInterface $conn, $topic)
    {
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->count++;
        $this->log->debug("Conn num:{$this->count}");
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->count--;
        $this->log->debug("Conn num:{$this->count}");
    }

    public function onCall(ConnectionInterface $conn, $id, $topic, array $params)
    {
        // In this application if clients send data it's because the user hacked around in console
        $conn->callError($id, $topic, 'You are not allowed to make calls')->close();
    }

    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible)
    {
//        var_dump($conn,  $topic, $event, $exclude, $eligible);

        // connection.conn.publish('change単位時間', '1234') sample js
        if($topic->getId() == PASS.'change単位時間') {
            static::$単位時間 = (int)$event;
        }elseif($topic->getId() == PASS.'changeリデュース'){
            static::$リデュース = (int)$event;
        }elseif($topic->getId() == PASS.'change波動砲発射閾値'){
            static::$波動砲発射閾値 = (int)$event;
        }
        //$topic->broadcast($event);
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
    }
}