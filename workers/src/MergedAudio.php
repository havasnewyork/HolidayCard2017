<?php namespace Havas\Holiday;


class MergedAudio extends Asset {

	public $assetDirectory = MERGED_AUDIO_DIR;

	public $useS3 = false;

	protected $contentType = 'audio/m4a';

	public function setAudio( $value ) {
		$this->key = $value;
	}

}