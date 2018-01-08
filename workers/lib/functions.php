<?php

use Havas\Holiday\Monitor;

/**
 * @param React\EventLoop\LoopInterface $loop
 */
function register_signal_handlers( React\EventLoop\LoopInterface $loop, $log ) {

	$pcntl = new MKraemer\ReactPCNTL\PCNTL( $loop );
	$pcntl->on( SIGTERM, function () use ( $log ) {

		try {
			$conn = new \PDO( sprintf( 'mysql:host=%s;dbname=%s', getenv( 'DB_HOST' ), getenv( 'DB_NAME' ) ), getenv( 'DB_USER' ),
				getenv( 'DB_PASSWORD' ) );

			$result = $conn->prepare( "UPDATE `card` SET status = :pending WHERE status = :inprogress" )->execute( [
				':pending'    => Monitor::JOB_PENDING,
				':inprogress' => Monitor::JOB_IN_PROGRESS
			] );

		} catch ( \Exception $e ) {
		}

		$log->notice( 'Main workers monitor ended by SIGTERM (kill PID)' );
		die();
	} );
	$pcntl->on( SIGINT, function () use ( $log ) {

		try {
			$conn = new \PDO( sprintf( 'mysql:host=%s;dbname=%s', getenv( 'DB_HOST' ), getenv( 'DB_NAME' ) ), getenv( 'DB_USER' ),
				getenv( 'DB_PASSWORD' ) );

			$result = $conn->prepare( "UPDATE `card` SET status = :pending WHERE status = :inprogress" )->execute( [
				':pending'    => Monitor::JOB_PENDING,
				':inprogress' => Monitor::JOB_IN_PROGRESS
			] );

		} catch ( \Exception $e ) {
		}

		$log->notice( 'Main workers monitor ended by SIGINT (CTRL-C)' );
		die();
	} );
}
