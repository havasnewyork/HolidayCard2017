<?php namespace Havas\Holiday;

use Aws\S3\S3Client;

class Audio {

	public $background_audio;

	/**
	 * @var array
	 */
	public $voices = [];

	public function __construct( $data, S3Client $client ) {

		$this->background_audio = sprintf( "%s%sbackground%s.mp3", BACKGROUND_AUDIO_DIR, DIRECTORY_SEPARATOR, $data['id'] );

		if ( is_array( $data['voices'] ) && ( count( $data['voices'] ) > 0 ) ) {
			foreach ( $data['voices'] as $voice ) {
				$v         = new Voice( $voice['url'], $client );
				$v->offset = $voice['offset'];
				$v->word   = $voice['word'];

				$this->voices[] = $v;
			}
		}
	}

	public function __get( $name ) {
		return $this->{$name};
	}


}

