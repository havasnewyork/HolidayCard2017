<?php namespace Havas\Holiday;

class Photo extends Asset {

	public $assetDirectory = PHOTO_DIR;

	/**
	 * @var int ffpmeg input number
	 */
	public $input = 0;

	public function __get( $name ) {
		if ( $name == 'width' ) {
			return $this->getImageData( 'width' );
		} elseif ( $name == 'height' ) {
			return $this->getImageData( 'height' );
		} elseif ( $name == 'contentType' ) {
			return $this->getImageData( 'mime' );
		} else {
			return parent::__get( $name );
		}
	}

	public function getImageData( $prop ) {
		$filePath = $this->getFile();
		if ( ! empty( $filePath ) ) {
			$props = getimagesize( $filePath );
			switch ( $prop ) {
				case 'width':
					return $props[0];
					break;
				case 'height':
					return $props[1];
					break;
				case 'mime':
					return $props['mime'];
					break;
			}
		}

		return null;
	}

}