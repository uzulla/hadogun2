<?php
namespace Publisher;

use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;
use Uzulla\SLog\SimpleLogger;

class Pusher implements WampServerInterface
{

    protected $subscribedTopics = [];
    protected $count = 0;
    private $log;

    public function __construct(){
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

        // リデュース
//        $this->debug_count++;
//        if($this->debug_count%10!=0){
//            return;
//        }else{
//            $this->debug_count=0;
//        }

        echo ".";

        if (!isset($this->subscribedTopics['hadoch'])) {
            return;
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
        $topic->broadcast($event);
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
    }
}