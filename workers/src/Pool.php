<?php namespace Havas\Holiday;


use Psr\Log\LoggerInterface;
use React\ChildProcess\Process;

class Pool {

	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var array
	 */
	public $options = [];

	/**
	 * @var array
	 */
	public $workers = [];

	/**
	 * @var Monitor
	 */
	public $monitor;

	/**
	 * @var LoggerInterface
	 */
	public $log;

	/**
	 * Pool constructor.
	 *
	 * @param $name
	 * @param $id
	 * @param Monitor $monitor
	 * @param LoggerInterface $log
	 * @param array $options
	 */
	public function __construct( $name, $id, Monitor $monitor, LoggerInterface $log, $options = [] ) {
		$this->id      = $id;
		$this->name    = $name;
		$this->monitor = $monitor;
		$this->log     = $log;
		$this->options = $options;
	}

	public function __get( $name ) {
		return $this->{$name};
	}

	/**
	 * @param int $jobCounter
	 * @param array $params
	 * @param string $commandString optional custom command to override the pool default - use this carefully
	 */
	public function addWorker( $jobCounter, $params, $commandString = null ) {

		if ( is_null( $commandString ) ) {
			$commandString = $this->options['command'];
		}

		$processCommand = sprintf( "%s %s", $commandString, join( " ", array_values( $params ) ) );

		$this->workers[ $jobCounter ] = [ 'process' => new Process( $processCommand ) ];

		unset( $params['process'] );
		foreach ( $params as $key => $value ) {
			$this->workers[ $jobCounter ][ $key ] = $value;
		}

		$this->workers[ $jobCounter ]['process']->start( $this->monitor->getLoop() );

		$processPID = $this->workers[ $jobCounter ]['process']->getPid();

		$this->workers[ $jobCounter ]['PID'] = $processPID;

		$this->log->debug( "Spawning {$processCommand} with PID " . $processPID );
		$this->log->info( "Starting job {$jobCounter} in {$this->name} pool. {$this->monitor->getActiveWorkers($this->name)} workers now active." );

		$this->workers[ $jobCounter ]['process']->on( 'exit',
			function ( $exitCode ) use ( $jobCounter, $params, $processPID ) {
				if ( $exitCode === 0 ) {
					$this->log->debug( "Child process {$jobCounter} in pool {$this->name} for Card {$params['card']} completed with exit code {$exitCode}" );
				} else {
					if ( empty( $exitCode ) ) {
						$exitCode = 77;
					}

					$this->log->error( "Child process {$jobCounter} in pool {$this->name} for Card {$params['card']} failed with exit code {$exitCode}" );
				}
				unset( $this->workers[ $jobCounter ] );
			}
		);

		$this->monitor->jobCounter ++;

	}

	/**
	 * @param int $jobCounter
	 */
	public function removeWorker( $jobCounter ) {
		if ( is_a( $this->workers[ $jobCounter ]['process'], Process::class ) ) {
			$this->workers[ $jobCounter ]['process']->stdin->close();
			$this->workers[ $jobCounter ]['process']->stdout->close();
			$this->workers[ $jobCounter ]['process']->stderr->close();
			$this->workers[ $jobCounter ]['process']->terminate( SIGKILL );

			$pid    = intval( $this->workers[ $jobCounter ]['process']->getPid() );
			$ff_pid = $pid + 1;
			posix_kill( $ff_pid, SIGKILL );

			$this->log->debug( "Killing worker process {$pid} and FFMPEG process {$ff_pid}." );
		}

		unset( $this->workers[ $jobCounter ] );

	}

}