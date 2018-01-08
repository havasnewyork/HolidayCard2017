<?php namespace Havas\Holiday;

use Psr\Log\LoggerInterface;
use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;

class Monitor {

	const JOB_FAILED      = 0;
	const JOB_PENDING     = 10;
	const JOB_IN_PROGRESS = 20;
	const JOB_COMPLETED   = 50;

	const MAX_WORKERS = 5;
	const MAX_RETRIES = 3;

	const MAX_CHILD_EXECUTION_TIME = 30;

	public $jobCounter = 0;

	/**
	 * @var \PDO
	 */
	protected $conn;

	/**
	 * @var LoopInterface
	 */
	protected $loop;

	/**
	 * @var LoggerInterface
	 */
	protected $log;

	/**
	 * Array of child process pools
	 *
	 * @var array
	 */
	public $pools = [];

	/**
	 * Monitor constructor.
	 *
	 * @param LoopInterface $loop
	 * @param LoggerInterface $log
	 */
	public function __construct( LoopInterface $loop, LoggerInterface $log ) {

		$this->loop = $loop;
		$this->log  = $log;

		try {
			$this->conn = new \PDO( sprintf( 'mysql:host=%s;dbname=%s', getenv( 'DB_HOST' ), getenv( 'DB_NAME' ) ), getenv( 'DB_USER' ),
				getenv( 'DB_PASSWORD' ) );

		} catch ( \Exception $e ) {
			$this->log->error( "Monitor failed to connect to database: " . $e->getMessage() );
			exit( 99 );

		}

	}

	public function registerWorkerPool( Pool $pool ) {
		$this->pools[ $pool->name ] = $pool;
		$this->log->notice( "Registered new pool {$pool->name} with ID {$pool->id}." );
	}

	/**
	 * Returns the number of currently active workers
	 * for the specified pool
	 *
	 * @param string $poolName optionally constrain to a specific pool
	 *
	 * @return int
	 *
	 */
	public function getActiveWorkers( $poolName = null ) {
		$activeWorkers = 0;
		if ( is_null( $poolName ) ) {
			foreach ( $this->pools as $poolName => $pool ) {
				foreach ( $pool->workers as $worker ) {
					if ( is_array( $worker ) && is_a( $worker['process'], Process::class ) ) {
						$activeWorkers ++;
					}
				}
			}
		} else {
			foreach ( $this->pools[ $poolName ]->workers as $worker ) {
				if ( is_array( $worker ) && is_a( $worker['process'], Process::class ) ) {
					$activeWorkers ++;
				}
			}
		}

		return $activeWorkers;
	}

	/**
	 * Checks for stalled processes across all pools
	 * and kills them
	 *
	 */
	public function cleanup() {

		foreach ( $this->pools as $poolName => $pool ) {

			foreach ( $pool->workers as $jobCounter => $worker ) {

				if ( is_array( $worker ) ) {

					$cardId = intval( $worker['card'] );

					$childRuntimeDuration = sprintf( "%.3f", microtime( true ) - $worker['start'] );

					if ( $childRuntimeDuration > self::MAX_CHILD_EXECUTION_TIME ) {

						$this->log->warning( "Monitor is killing unresponsive child process in pool {$poolName} after {$childRuntimeDuration}s for job {$jobCounter} and Card {$cardId}." );

						// if it still exists, clean up the process
						$pool->removeWorker( $jobCounter );

						$retries = $this->conn->query( "SELECT `retries` FROM `card` WHERE id = {$cardId}" )->fetchColumn( 0 );

						if ( $retries <= Monitor::MAX_RETRIES ) {
							$finalStatus = $pool->id + Monitor::JOB_PENDING;
							$retries ++;
						} else {
							$finalStatus = $pool->id + Monitor::JOB_FAILED;
						}

						$result = $this->conn->prepare( "UPDATE `card` SET `status` = :status, `retries` = :retries, `updated_at` = NOW() WHERE id = :id" )
						                     ->execute( [
							                     ':id'      => $cardId,
							                     ':retries' => $retries,
							                     ':status'  => $finalStatus
						                     ] );

					}

				}
			}

		}

//		$this->log->debug( sprintf( " %d running processes, %sMB peak memory.", $this->getActiveWorkers(),
//			number_format( memory_get_peak_usage( true ) / 1000000, 1 ) ) );


	}

	/**
	 * Scan for pending jobs in a given pool
	 *
	 * @param $poolName
	 * @param $tickTime
	 */
	public function scanForPendingJobs( $poolName, $tickTime ) {

		$this->conn->beginTransaction();

		$pendingStatus = $this->pools[ $poolName ]->id + Monitor::JOB_PENDING;

		$jobToStart = $this->conn->query( sprintf( "SELECT id FROM `card` WHERE status = %d ORDER BY `updated_at` ASC LIMIT 1 FOR UPDATE",
			$pendingStatus ), \PDO::FETCH_OBJ )->fetch();

		if ( is_object( $jobToStart ) && ( $this->getActiveWorkers( $poolName ) < Monitor::MAX_WORKERS ) && ( $jobToStart->id > 0 ) ) {

			$inProgressStatus = $this->pools[ $poolName ]->id + Monitor::JOB_IN_PROGRESS;

			$result = $this->conn->prepare( "UPDATE card SET status= :status, `updated_at` = NOW() WHERE id = :id" )->execute( [
				':status' => $inProgressStatus,
				':id'     => $jobToStart->id
			] );

			$this->pools[ $poolName ]->addWorker( $this->jobCounter,
				[
					'card'    => $jobToStart->id,
					'counter' => $this->jobCounter,
					'start'   => $tickTime
				]
			);

		}

		$this->conn->commit();

	}

	/**
	 * @return \PDO
	 */
	public function getConn() {
		return $this->conn;
	}

	/**
	 * @return LoopInterface
	 */
	public function getLoop() {
		return $this->loop;
	}


}