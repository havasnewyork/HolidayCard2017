<?php

use Havas\Holiday\AudioPool;
use Havas\Holiday\Monitor;
use Havas\Holiday\VideoPool;

require 'vendor/autoload.php';
require 'lib/boot.php';

$cardId = intval( $argv[1] );

sleep( rand( 5, 25 ) );

try {
	$conn = new \PDO( sprintf( 'mysql:host=%s;dbname=%s', getenv( 'DB_HOST' ), getenv( 'DB_NAME' ) ), getenv( 'DB_USER' ),
		getenv( 'DB_PASSWORD' ) );

	$statuses = [ ( VideoPool::ID + Monitor::JOB_PENDING ), ( AudioPool::ID + Monitor::JOB_PENDING ) ];

	$result = $conn->query( sprintf( "UPDATE `card` SET `status` = %d WHERE `id` = %d", $statuses[ rand( 0, 1 ) ], $cardId ) );

} catch ( Exception $e ) {
	print_r( $e->getMessage() );
}

exit( 0 );