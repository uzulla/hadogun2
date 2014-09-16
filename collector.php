<?php
require_once(__DIR__.'/vendor/autoload.php');
require_once(__DIR__.'/config.php');

// Start streaming
$sc = new FilterTrackConsumer(OAUTH_TOKEN, OAUTH_SECRET, Phirehose::METHOD_FILTER);
$sc->setTrack(['iphone']);
$sc->consume();
