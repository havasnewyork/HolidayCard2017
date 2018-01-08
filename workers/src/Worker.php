<?php namespace Havas\Holiday;

use Psr\Log\LoggerInterface;

class Worker {

	/**
	 * @var string
	 */
	private $baseCommand = "exec ffmpeg -hide_banner ";

	private $underlyingImageInput = "-loop 1 -i %s ";

	private $overlayInput = "-ignore_loop 0 -i %s ";

	private $videoCodec = "-c:v libx264 -preset fast -crf 25 "; // a bit crunchier than the default, still looks good.
	private $audioEncodingCodec = "-c:a mp3 -ac 1 -b:a 64k "; // mono 64k
	private $audioVideoCodec = "-c:a aac -ac 1 -b:a 64k "; // mono 64k

	private $audioBackgroundInput = '-i %s ';

	private $voiceInput = '-itsoffset 00:00:%2$02f -i %1$s ';

	private $audioFilter = '-filter_complex "amix=inputs=%d:duration=longest" -async 1 ';

	private $audioFilterGraphs = [
		'boost' => '[%1$s]volume=2:precision=fixed[%2$s] '
	];

	private $videoFilter = '-filter_complex "%s" ';

	private $videoFilterGraphs = [
		'covered'        => '[%1$s]scale=%2$s:%3$s:flags=neighbor[%4$s],[%5$s][%4$s]overlay=main_w/2-overlay_w/2:main_h/2-overlay_h/2[%8$s] ',
		'covered_scaled' => '[%1$s]scale=%2$s:%3$s:flags=neighbor[%4$s],[%5$s][%4$s]overlay=0:0[%8$s] ',
		'covered_offset' => '[%1$s]scale=%2$s:%3$s:flags=neighbor[%4$s],[%5$s][%4$s]overlay=%6$s:%7$s[%8$s] ',
		'centered'       => '[%1$s]scale=%2$s:%3$s:flags=neighbor[%4$s],[%5$s][%4$s]overlay=main_w/2-overlay_w/2:main_h/2-overlay_h/2[%8$s] ',
	];

	private $textFilterGraph = '[%1$s]drawtext=fontfile=%2$s:text=\'%3$s\':fontcolor=white:fontsize=%4$d:box=1:boxcolor=red:boxborderw=15:x=(w-text_w)/2:y=(h-text_h)/2:enable=\'between(t,%5$f,%6$f)\'[%7$s] ';

	private $outputMap = '-map "%s" -map "%s" ';

	private $videoDuration = "-t %d ";

	private $card;

	private $fontFile = 'OpenSans-Bold.ttf';
	private $wordFontSize = 64;
	private $wordDuration = 1;

	private $log;

	private $mergedAudio;

	public function __construct( Card $card, LoggerInterface $log = null ) {
		$this->card = $card;
		$this->log  = $log;

		$this->fontFile = getenv( 'FONT_FILE' ) ?: $this->fontFile;

	}

	/**
	 * Validate that all card elements are present before we call ffmpeg
	 *
	 * @param bool $includeMergedAudio confirm that our merged audio also exists
	 *
	 * @return bool
	 */
	public function validateCard( $includeMergedAudio = false, $includeOverlay = true ) {

		$passed = true;

		// validate background photo
		$photoFile = $this->card->photo->getFile();
		if ( ( ! $this->card->photo->isLocal ) || ! file_exists( $photoFile ) ) {
			$this->log->debug( "Card validation failed for photo {$photoFile}" );

			$passed = false;
		}

		// validate audio background
		$backgroundAudioFile = $this->card->audio->background_audio;
		if ( ! file_exists( $backgroundAudioFile ) ) {
			$this->log->debug( "Card validation failed for background audio {$backgroundAudioFile}" );

			$passed = false;
		}

		// validate voices
		foreach ( $this->card->audio->voices as $voice ) {
			$voiceFile = $voice->getFile();
			if ( ( ! $voice->isLocal ) || ! file_exists( $voiceFile ) ) {
				$this->log->debug( "Card validation failed for voice file {$voiceFile}" );

				$passed = false;
			}
		}

		// validate overlay
		if ( $includeOverlay ) {
			foreach ( $this->card->overlays as $overlay ) {
				if ( ! file_exists( $overlay->file ) ) {
					$this->log->debug( "Card validation failed for overlay {$overlay->file}" );

					$passed = false;
				}
			}
		}

		if ( $includeMergedAudio ) {
			$mergedAudioFile = $this->card->mergedAudio->getFile();
			if ( ( ! $this->card->mergedAudio->isLocal ) || ! file_exists( $mergedAudioFile ) ) {
				$this->log->debug( "Card validation failed for merged audio file {$mergedAudioFile}" );

				$passed = false;
			}
		}

		return $passed;

	}

	/**
	 * @return mixed
	 */
	public function generateAudio() {
		$input        = 0;
		$filter_graph = '';

		$command = $this->baseCommand;

		if ( is_a( $this->card->audio, Audio::class ) ) {

			$command .= sprintf( $this->audioBackgroundInput, $this->card->audio->background_audio );
			$input ++;

			if ( is_array( $this->card->audio->voices ) ) {

				for ( $i = 0; $i < count( $this->card->audio->voices ); $i ++ ) {
					$command .= sprintf( $this->voiceInput, $this->card->audio->voices[ $i ]->file, $this->card->audio->voices[ $i ]->offset );

					$this->card->audio->voices[ $i ]->input = $input;
					$input ++;
				}

				$command .= sprintf( $this->audioFilter, $input );
			}

			$command .= $this->audioEncodingCodec;

			$audioUrl = "/tmp/merged_audio" . DIRECTORY_SEPARATOR . $this->card->getBaseKey() . ".mp3";

			$audioOutput = sprintf( "%s%s", MERGED_AUDIO_DIR, $audioUrl );

			$command .= "-y {$audioOutput}";

			$this->log->debug( "Running {$command}" );

			exec( $command, $output, $returnCode );

			if ( $returnCode == 0 ) {
				$this->card->mergedAudio->setAudio( $audioUrl );
			}

			return $returnCode;
		}

		return 0;

	}

	/**
	 * @return mixed
	 */
	public function generateVideo() {

		$input            = 0;
		$filter_graph     = '';
		$map_video_output = '';

		$command = $this->baseCommand;

		$command .= sprintf( $this->underlyingImageInput, $this->card->photo->file );

		$this->card->photo->input = $input;
		$input ++;

		// filters
		$video_filter_graphs = [];

		// master video scaling
		if ( $this->card->photo->width < $this->card->photo->height ) { // PORTRAIT
			$videoHeight = ( $this->card->photo->height > 1080 ) ? 1080 : $this->card->photo->height;
			$videoWidth  = round( ( $this->card->photo->width / $this->card->photo->height ) * 1080 );
		} else { // LANDSCAPE
			$videoWidth  = ( $this->card->photo->width > 1080 ) ? 1080 : $this->card->photo->width;
			$videoHeight = round( ( $this->card->photo->height / $this->card->photo->width ) * 1080 );
		}
		$video_filter_graphs[] = sprintf( "[0:v]scale=%d:%d[photo_out]", $videoWidth, $videoHeight );

		$base_video_input = "photo_out";
//		$base_video_input    = sprintf( "%d:v", $this->card->photo->input );
		$video_graph_output = "{$base_video_input}";


		// Add any video overlays as inputs
		if ( count( $this->card->overlays ) > 0 ) {

			for ( $i = 0; $i < count( $this->card->overlays ); $i ++ ) {

				$command .= sprintf( $this->overlayInput, $this->card->overlays[ $i ]->file );

				$this->card->overlays[ $i ]->input = $input;

				$video_graph_output = "video_out{$input}";

				if ( $this->card->photo->width < $this->card->photo->height ) { // PORTRAIT

					$overlayHeight         = $videoHeight;
					$resultingOverlayWidth = ( $this->card->overlays[ $i ]->width * ( $videoHeight / $this->card->overlays[ $i ]->height ) );

					switch ( $this->card->overlays[ $i ]->type ) {
						case 'centered':
							$overlayWidth  = $videoWidth;
							$overlayHeight = - 1;
							break;
						case 'covered_scaled':
							$overlayHeight = $videoHeight;
							$overlayWidth  = $videoWidth;
							break;
						default:
							$overlayWidth = - 1;
					}

				} else { // LANDSCAPE
					$overlayWidth           = $videoWidth;
					$resultingOverlayHeight = ( $this->card->overlays[ $i ]->height * ( $videoWidth / $this->card->overlays[ $i ]->width ) );

					switch ( $this->card->overlays[ $i ]->type ) {
						case 'centered':
							$overlayHeight = $videoHeight;
							$overlayWidth  = - 1;
							break;
						case 'covered_scaled':
							$overlayHeight = $videoHeight;
							$overlayWidth  = $videoWidth;
							break;
						default:
							$overlayHeight = - 1;
					}

				}

				// 'covered_scaled' => '[%1$s]scale=%2$s:%3$s:flags=neighbor[%4$s],[%5$s][%4$s]overlay=0:0[%8$s] ',


				$video_filter_graphs[] = sprintf( $this->videoFilterGraphs[ $this->card->overlays[ $i ]->type ],
					$input,
					$overlayWidth,
					$overlayHeight,
					"video_tmp{$input}",
					$base_video_input,
					$this->card->overlays[ $i ]->offset_x,
					$this->card->overlays[ $i ]->offset_y,
					$video_graph_output
				);

				$input ++;
				$base_video_input = $video_graph_output;
			}

		}

// [%1$s]drawtext=fontfile=%2$s:text=\'%3$s\':fontcolor=white:fontsize=%4$d:box=1:boxcolor=red:boxborderw=5:x=(w-text_w)/2:y=(h-text_h)/2:enable=\'between(t,%5$f,%6$f)\'[%7$s]

		// Overlay text
		if ( is_array( $this->card->audio->voices ) ) {

			$base_video_input = $video_graph_output;

			for ( $i = 0; $i < count( $this->card->audio->voices ); $i ++ ) {

				$video_graph_output = "word_out{$i}";

				$video_filter_graphs[] = sprintf( $this->textFilterGraph,
					$base_video_input,
					FONT_DIR . DIRECTORY_SEPARATOR . $this->fontFile,
					$this->card->audio->voices[ $i ]->getWord(),
					$this->wordFontSize,
					$this->card->audio->voices[ $i ]->offset,
					$this->card->audio->voices[ $i ]->offset + $this->wordDuration,
					$video_graph_output
				);

				$base_video_input = $video_graph_output;

			}

		}

		$map_video_output = "[{$video_graph_output}]";

		// Add audio
		if ( is_a( $this->card->mergedAudio, MergedAudio::class ) && ( $this->card->mergedAudio->file != '' ) ) {
			$command .= sprintf( $this->audioBackgroundInput, $this->card->mergedAudio->file );
		}

		$base_audio_output = sprintf( "%d:a", $input );

		$input ++;


		// Add the video filters to include the overlays
		if ( count( $video_filter_graphs ) > 0 ) {
			$command .= sprintf( $this->videoFilter, join( "; ", $video_filter_graphs ) );
		}

		$command .= $this->videoCodec;
		$command .= $this->audioVideoCodec;

		$command .= sprintf( $this->outputMap, $map_video_output, $base_audio_output );

		$command .= sprintf( $this->videoDuration, $this->card->getAudioLength() );

		$videoOutput = sprintf( "%s%svideos%s%s.mp4", VIDEO_DIR, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $this->card->getBaseKey() );

		$command .= "-y {$videoOutput}";

		$this->log->debug( "Running {$command}" );

		exec( $command, $output, $returnCode );

		if ( $returnCode == 0 ) {
			$this->card->video->setVideo( "videos/" . $this->card->getBaseKey() . ".mp4" );
		}

		return $returnCode;
	}

}