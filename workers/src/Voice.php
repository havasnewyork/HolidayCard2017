<?php namespace Havas\Holiday;

class Voice extends Asset {

	public $assetDirectory = VOICES_DIR;

	public $offset = 0;

	public $word;

	public $useS3 = false;

	protected $contentType = 'audio/wav';

	/**
	 * @var int ffpmeg input number
	 */
	public $input = 0;

	public function getWord() {
		if (empty($this->word)) {
				preg_match("/^.+\/(.+?)_.+$/",$this->key,$match);
				return isset($match[1]) ? $match[1] : '**WORD MISSING**';
		} else {
			return $this->word;
		}
	}

}