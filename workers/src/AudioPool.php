<?php namespace Havas\Holiday;

use Psr\Log\LoggerInterface;

class AudioPool extends Pool {

	const ID = 100;

	public function __construct( Monitor $monitor, LoggerInterface $log, $options = [] ) {
		parent::__construct( 'Audio', self::ID, $monitor, $log, $options );
	}

}