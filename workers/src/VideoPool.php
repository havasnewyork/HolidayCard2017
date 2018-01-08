<?php namespace Havas\Holiday;

use Psr\Log\LoggerInterface;

class VideoPool extends Pool {

	const ID = 200;

	public function __construct( Monitor $monitor, LoggerInterface $log, $options = [] ) {
		parent::__construct( 'Video', self::ID, $monitor, $log, $options );
	}

}