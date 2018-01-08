<?php

use Havas\Holiday\Monitor;
use Havas\Holiday\AudioPool;
use Havas\Holiday\VideoPool;
use React\EventLoop\Factory;
use React\EventLoop\Timer\TimerInterface;

require 'vendor/autoload.php';
require 'lib/boot.php';
require 'lib/functions.php';

$loop = Factory::create();
register_signal_handlers( $loop, $log );

$monitor = new Monitor( $loop, $log );

$audioPool = new AudioPool( $monitor, $log, [ 'command' => 'exec php audio_render.php' ] );
$videoPool = new VideoPool( $monitor, $log, [ 'command' => 'exec php video_render.php' ] );

//$audioPool = new AudioPool( $monitor, $log, [ 'command' => 'exec php child_test.php' ] );
//$videoPool = new VideoPool( $monitor, $log, [ 'command' => 'exec php child_test.php' ] );

$monitor->registerWorkerPool( $audioPool );
$monitor->registerWorkerPool( $videoPool );

//
// MAIN LOOP
//
$log->notice( 'Main monitor process started with ' . Monitor::MAX_WORKERS . " max workers." );
$loop->addPeriodicTimer( 0.5, function ( TimerInterface $timer ) use ( $monitor ) {

	$tickTime = sprintf( "%.3f", microtime( true ) );

	$monitor->cleanup();

	$monitor->scanForPendingJobs( 'Audio', $tickTime );
	$monitor->scanForPendingJobs( 'Video', $tickTime );

} );

$loop->run();