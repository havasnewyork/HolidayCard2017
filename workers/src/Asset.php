<?php namespace Havas\Holiday;

use Aws\S3\S3Client;

class Asset {

	public $key;

	public $client;

	public $assetDirectory;

	public $isLocal = false;
	public $isRemote = false;

	public $baseKey;

	public $useS3 = true;

	protected $contentType = 'text/plain';

	public function __construct( $key, S3Client $client = null ) {
		$this->key    = ltrim( $key, '/' );
		$this->client = $client;
	}

	public function __get( $name ) {
		if ( $name == 'url' ) {
			return $this->getUrl();
		} elseif ( $name == 'file' ) {
			return $this->getFile();
		} else {
			return $this->{$name};
		}
	}

	/**
	 * Strips any folder info and/or suffix
	 */
	public function getBaseKey() {
		if ( empty( $this->baseKey ) ) {
			$info          = pathinfo( $this->key );
			$this->baseKey = basename( $this->key, '.' . $info['extension'] );
		}

		return $this->baseKey;
	}

	public function getUrl() {
		return ( ! empty( $this->key ) && $this->useS3 ) ? sprintf( "https://s3.amazonaws.com/%s/%s", getenv( 'AWS_BUCKET' ), $this->key ) : null;
	}

	public function getFile() {
		if ( ! empty( $this->key ) ) {

			$saveAs = $this->assetDirectory . DIRECTORY_SEPARATOR . $this->key;

			if ( ! file_exists( $saveAs ) ) {

				if ( $this->useS3 && $this->client ) {
					@mkdir( dirname( $saveAs ), 0755, true );

					try {
						$result         = $this->client->getObject( [
							'Bucket' => getenv( 'AWS_BUCKET' ),
							'Key'    => $this->key,
							'SaveAs' => $saveAs
						] );
						$this->isLocal  = true;
						$this->isRemote = true;

					} catch ( \Exception $e ) {
						$this->isLocal = false;
						$saveAs        = null;
					}

				} else {
					$saveAs = null;
				}

			} else {
				$this->isLocal = true;
			}

			return $saveAs;

		}

		return null;

	}

	public function putFile() {
		if ( $this->client && $this->useS3 ) {

			$localFile = $this->assetDirectory . DIRECTORY_SEPARATOR . $this->key;

			if ( file_exists( $localFile ) ) {
				try {
					$result = $this->client->putObject( [
						'Bucket'      => getenv( 'AWS_BUCKET' ),
						'Key'         => $this->key,
						'SourceFile'  => $localFile,
						'ACL'         => 'public-read',
						'ContentType' => $this->contentType
					] );

					return $result['ObjectURL'];

				} catch ( \Exception $e ) {
					print_r( $e->getMessage() );
				}
			}

		} else {
			return $this->key;
		}

		return null;
	}

}