<?php

use Havas\Holiday\Monitor;
use React\EventLoop\Factory;

require 'vendor/autoload.php';
require 'lib/boot.php';

$status = $argv[1];

$loop    = Factory::create();
$monitor = new Monitor( $loop, $log );

$cards = $monitor->getConn()
                 ->query( sprintf( "SELECT * FROM `card` WHERE `video_url` = '' AND `status` = %d ORDER BY `created_at` ASC",
	                 $status ), PDO::FETCH_OBJ )->fetchAll();

print_r( $cards );
