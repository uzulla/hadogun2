<?php
require_once(__DIR__.'/vendor/autoload.php');
require_once(__DIR__.'/config.php');

// Start streaming
$sc = new \Hadogun2\Collector(OAUTH_TOKEN, OAUTH_SECRET, \Phirehose::METHOD_FILTER);
$sc->setTrack($keywords);
$sc->consume();
