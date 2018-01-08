<?php namespace Havas\Holiday;

use Aws\S3\S3Client;
use wapmorgan\Mp3Info\Mp3Info;

class Card {

	public $id;

	public $photo;
	public $audio;

	public $client;

	/**
	 * @var array
	 */
	public $overlays;

	public $mergedAudio;

	public $video;
	public $caption;

	public $status;

	public $creationDate;
	public $lastUpdated;

	public $videoDuration = 15;

	public function __construct( $data = [] ) {

		$this->client = new S3Client( [
			'region'  => getenv( 'AWS_REGION' ),
			'version' => 'latest'
		] );

		$this->id = $data['id'];

		$photoUrl = str_replace( 'https://s3.amazonaws.com/havas-holiday-2017/', '', $data['photo_url'] );

		$this->photo = new Photo( $photoUrl, $this->client );

		$this->audio = new Audio( ( is_array( $data['audio_parts'] ) ? $data['audio_parts'] : json_decode( $data['audio_parts'], true ) ), $this->client );

		$overlayData = is_array( $data['overlay'] ) ? $data['overlay'] : json_decode( $data['overlay'], true );

		$this->overlays[] = new Overlay( $overlayData['id'], $overlayData['type'] );

		$this->mergedAudio = new MergedAudio( $data['audio_url'], $this->client );

		$this->video   = new Video( $data['video_url'], $this->client );
		$this->caption = $data['caption'];

		$this->status = $data['status'];

		$this->creationDate = $data['created_at'];
		$this->lastUpdated  = $data['updated_at'];

	}

	/**
	 * Returns duration of the background audio
	 *
	 * @return float
	 */
	public function getAudioLength() {
		try {
			$backgroundAudioFile = new Mp3Info( $this->audio->background_audio );

			return sprintf( "%.1f", $backgroundAudioFile->duration );
		} catch ( \Exception $e ) {
		}

		return $this->videoDuration;

	}

	public function __get( $name ) {
		return $this->{$name};
	}

	public function getBaseKey() {
		return $this->photo->getBaseKey();
	}

	public function getPhoto() {
		return $this->photo->key;
	}

	public function getVideo() {
		return $this->video->key;
	}

	public function getAudio() {
		return $this->mergedAudio->key;
	}

}