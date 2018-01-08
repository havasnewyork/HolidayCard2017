<?php

use Aws\S3\Exception\S3Exception;
use Havas\Holiday\Photo;

require 'vendor/autoload.php';
require 'lib/boot.php';

$client = new Aws\S3\S3Client( [
	'region'  => getenv( 'AWS_REGION' ),
	'version' => 'latest'
] );
$client->registerStreamWrapper();

$photo = new Photo( 'testing/cz15xaqs2jakihm1s.png', $client );

echo $photo->width;

//print_r( $client->listBuckets() );

//try {
//	$objects = $client->getIterator( 'ListObjects', array(
//		'Bucket' => getenv( 'AWS_BUCKET' )
//	) );
//
//	echo "Keys retrieved!\n";
//	foreach ( $objects as $object ) {
//		echo $object['Key'] . "\n";
//	}
//} catch ( S3Exception $e ) {
//	echo $e->getMessage() . "\n";
//}

