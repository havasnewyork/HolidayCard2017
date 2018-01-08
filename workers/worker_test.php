<?php

use Havas\Holiday\Card;
use Havas\Holiday\Worker;

require 'vendor/autoload.php';
require 'lib/boot.php';

$cardId = empty( $argv[1] ) ? 1 : $argv[1];

try {
	$conn = new \PDO( sprintf( 'mysql:host=%s;dbname=%s', getenv( 'DB_HOST' ), getenv( 'DB_NAME' ) ), getenv( 'DB_USER' ),
		getenv( 'DB_PASSWORD' ) );

	$record = $conn->query( sprintf( "SELECT * FROM `card` WHERE `id` = %d", $cardId ), PDO::FETCH_ASSOC )
	               ->fetch();

	if ( $record['id'] > 0 ) {

		$card = new Card( $record );

		$worker = new Worker( $card, $log );

		$startTime = microtime( true );

		$worker->generateAudio();

		printf( "-----------------------------------------------------------------------------\n" );
		printf( "-----------------------------------------------------------------------------\n" );
		printf( "-----------------------------------------------------------------------------\n" );

		$worker->generateVideo();

		$endTime = microtime( true );

		printf( "-----------------------------------------------------------------------------\n" );
		printf( "Test took %.2f seconds", ( $endTime - $startTime ) ) . PHP_EOL;

	}

} catch ( Exception $e ) {
	print_r( $e->getMessage() );
}