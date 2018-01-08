<?php

use Havas\Holiday\AudioPool;
use Havas\Holiday\Card;
use Havas\Holiday\Monitor;
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

			if ( $worker->validateCard( false, false ) ) {

				$returnCode = $worker->generateAudio();

				if ( $returnCode == 0 ) {

//					$url = $card->mergedAudio->putFile();

					$finalStatus    = AudioPool::ID + Monitor::JOB_COMPLETED;
					$renderDuration = sprintf( "%.2f", ( microtime( true ) - $startTime ) );

				} else {
					$renderDuration = 0;
					$url            = '';
					if ( $record['retries'] <= Monitor::MAX_RETRIES ) {
						$finalStatus = AudioPool::ID + Monitor::JOB_PENDING;
						$record['retries'] ++;
					} else {
						$finalStatus = AudioPool::ID + Monitor::JOB_FAILED;
					}
					$log->error( "Audio job {$jobCounter} for Card {$cardId} failed to render audio, exited with code {$returnCode}." );
				}


				$result = $conn->prepare( "UPDATE `card` SET `audio_url` = :url, `retries` = :retries, `audio_render_time` = :audio_render_time, `status` = :status, `updated_at` = NOW() WHERE id = :id" )
				               ->execute( [
					               ':url'               => $card->getAudio(),
					               ':audio_render_time' => $renderDuration,
					               ':retries'           => $record['retries'],
					               ':status'            => $finalStatus,
					               ':id'                => $cardId
				               ] );

				$log->info( "Audio job {$jobCounter} for Card {$cardId} took {$renderDuration}s to render." );

			} else {
				$result = $conn->prepare( "UPDATE `card` SET `status` = :status, `updated_at` = NOW() WHERE id = :id" )->execute( [
					':status' => AudioPool::ID + Monitor::JOB_FAILED,
					':id'     => $cardId
				] );
				$log->error( "Audio job {$jobCounter} failed validation of Card {$cardId}." );
				$returnCode = 33;


			}

		}

	} catch ( \Exception $e ) {
		$log->error( "Audio job {$jobCounter} for Card {$cardId} failed to connect to database: " . $e->getMessage() );
		$returnCode = 99;
	}
}

exit( $returnCode );
