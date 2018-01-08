<?php

use Havas\Holiday\Card;
use Havas\Holiday\Monitor;
use Havas\Holiday\VideoPool;
use Havas\Holiday\Worker;

require 'vendor/autoload.php';
require 'lib/boot.php';

$cardId     = intval( $argv[1] );
$jobCounter = $argv[2];
$timeCode   = $argv[3];

$returnCode = 1;

if ( $cardId > 0 ) {

	try {
		$conn = new \PDO( sprintf( 'mysql:host=%s;dbname=%s', getenv( 'DB_HOST' ), getenv( 'DB_NAME' ) ), getenv( 'DB_USER' ),
			getenv( 'DB_PASSWORD' ) );

		$record = $conn->query( sprintf( "SELECT * FROM `card` WHERE `id` = %d", $cardId ), PDO::FETCH_ASSOC )
		               ->fetch();

		if ( $record['id'] > 0 ) {

			$card = new Card( $record );

			$worker = new Worker( $card, $log );

			$startTime = microtime( true );

			// sanity check that we have all our files
			if ( $worker->validateCard( true, true ) ) {

				$returnCode = $worker->generateVideo();

				if ( $returnCode == 0 ) {

					$url = $card->video->putFile(); // upload to S3

					$finalStatus    = VideoPool::ID + Monitor::JOB_COMPLETED;
					$renderDuration = sprintf( "%.2f", ( microtime( true ) - $startTime ) );

				} else {
					$renderDuration = 0;
					$url            = '';
					if ( $record['retries'] <= Monitor::MAX_RETRIES ) {
						$finalStatus = VideoPool::ID + Monitor::JOB_PENDING;
						$record['retries'] ++;
					} else {
						$finalStatus = VideoPool::ID + Monitor::JOB_FAILED;
					}
					$log->error( "Video job {$jobCounter} for Card {$cardId} failed to render video, exited with code {$returnCode}." );
				}
				
				$result = $conn->prepare( "UPDATE `card` SET `video_url` = :url, `retries` = :retries, `status` = :status, `video_render_time` = :video_render_time, `updated_at` = NOW() WHERE id = :id" )
				               ->execute( [
					               ':url'               => $card->getVideo(),
					               ':video_render_time' => $renderDuration,
					               ':retries'           => $record['retries'],
					               ':status'            => $finalStatus,
					               ':id'                => $cardId
				               ] );
				if ( $finalStatus == ( VideoPool::ID + Monitor::JOB_COMPLETED ) ) {
					$log->info( "Video job {$jobCounter} for Card {$cardId} took {$renderDuration}s to render and upload to S3." );
				}

			} else {
				$result = $conn->prepare( "UPDATE `card` SET `status` = :status, `updated_at` = NOW() WHERE id = :id" )->execute( [
					':status' => VideoPool::ID + Monitor::JOB_FAILED,
					':id'     => $cardId
				] );
				$log->error( "Video job {$jobCounter} failed validation of Card {$cardId}." );
				$returnCode = 33;

			}

		}

	} catch ( \Exception $e ) {
		$log->error( "Video job {$jobCounter} for Card {$cardId} failed to connect to database: " . $e->getMessage() );
		$returnCode = 99;
	}


}

exit( $returnCode );
