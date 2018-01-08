<?php

//
// Load our environment
//
$dotenv = new Dotenv\Dotenv( dirname( dirname( __FILE__ ) ) );
$dotenv->load();

//
// Set up our paths
//
define( 'ASSET_DIR', getenv( 'ASSET_DIR' ) );
define( 'OVERLAY_DIR', getenv( 'OVERLAY_DIR' ) );
define( 'PHOTO_DIR', getenv( 'PHOTO_DIR' ) ); // S3
define( 'BACKGROUND_AUDIO_DIR', getenv( 'BACKGROUND_AUDIO_DIR' ) );
define( 'VOICES_DIR', getenv( 'VOICES_DIR' ) ); // S3
define( 'MERGED_AUDIO_DIR', getenv( 'MERGED_AUDIO_DIR' ) );
define( 'VIDEO_DIR', getenv( 'VIDEO_DIR' ) ); // S3
define( 'FONT_DIR', getenv( 'FONT_DIR' ) ); // S3

define( 'LOG_DIR', getenv( 'LOG_DIR' ) );

//
// Set up optional logging
//
$log = new Monolog\Logger( 'workers' );
$log->pushHandler( new Monolog\Handler\StreamHandler( LOG_DIR . '/workers.log', getenv( 'LOG_LEVEL' ) ) );
