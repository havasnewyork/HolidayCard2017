<?php namespace Havas\Holiday;


class Video extends Asset {

	public $assetDirectory = VIDEO_DIR;

	protected $contentType = 'video/mp4';

	public $duration = 0;

	public function setVideo( $value ) {
		$this->key = $value;
	}

}