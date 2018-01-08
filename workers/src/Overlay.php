<?php namespace Havas\Holiday;

class Overlay {

	public $id;

	public $type;

	public $file;

	public $width;
	public $height;

	public $offset_x = 0;
	public $offset_y = 0;

	public $mime;

	/**
	 * @var int ffpmeg input number
	 */
	public $input;

	public function __construct( $id, $type = 'covered' ) {

		$this->id   = $id;
		$this->type = $type;

		$this->file = sprintf( '%s%soverlay%3$d.gif', OVERLAY_DIR, DIRECTORY_SEPARATOR, $this->id );

		$props = getimagesize( $this->file );

		$this->width  = $props[0];
		$this->height = $props[1];

		$this->mime = $props['mime'];

	}

	public function __get( $name ) {
		return $this->{$name};
	}


}