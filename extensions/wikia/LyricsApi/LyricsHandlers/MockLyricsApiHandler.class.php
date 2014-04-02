<?php
/**
 * Class MockLyricsApiHandler
 *
 * @desc Provides mocked data for LyricsApi
 */
class MockLyricsApiHandler extends AbstractLyricsApiHandler {

	private function generateSongs( $count, $artistName, $albumName ) {
		$songs = [];
		for($i = 0; $i < $count; $i++) {
			$song = new StdClass();
			$song->name =  sprintf('%s song %d', $albumName, $i);

			// Songs without song pages and lyrics
			if ( $i % 3 != 0 ) {
				$song->url = $this->buildUrl([
					'controller' => self::API_CONTROLLER_NAME,
					'method' => 'getSong',
					LyricsApiController::PARAM_ARTIST => $artistName,
					LyricsApiController::PARAM_SONG => $song->name
				]);
			}
			$songs[] = $song;
		}
		return $songs;
	}

	/**
	 * @desc Returns an URL to random placekitten.com image
	 *
	 * @return string
	 */
	private function getImage() {
		return 'http://placekitten.com/' . rand(128, 1024) . '/' . rand(128, 1024) . '/';
	}

	/**
	 * @desc Returns mocked data about an artist
	 *
	 * @param String $artist artist name
	 *
	 * @return stdClass
	 */
	public function getArtist( $artistName ) {
		$result = new stdClass();
		$result->name = $artistName;
		$result->image = $this->getImage( $artistName );

		$album1 = new stdClass();
		$album1->name = 'Album #1';
		$album1->image = $this->getImage( $album1->name );
		$album1->year = 2001;
		$album1->url = $this->buildUrl([
			'controller' => self::API_CONTROLLER_NAME,
			'method' => 'getAlbum',
			LyricsApiController::PARAM_ARTIST => $artistName,
			LyricsApiController::PARAM_ALBUM => $album1->name,
		]);

		$album2 = new stdClass();
		$album2->name = 'Album #2';
		$album2->image = $this->getImage();
		$album2->year = 2011;
		$album2->url = $this->buildUrl([
			'controller' => self::API_CONTROLLER_NAME,
			'method' => 'getAlbum',
			LyricsApiController::PARAM_ARTIST => $artistName,
			LyricsApiController::PARAM_ALBUM => $album2->name,
		]);

		$result->albums = [
			$album2,
			$album1
		];

		// Songs without albums
		$result->songs = $this->generateSongs( 5, $artistName, '' );

		return $result;
	}

	/**
	 * @desc Returns mocked data about an album
	 *
	 * @param String $artistName
	 * @param String $albumName
	 *
	 * @return StdClass
	 */
	public function getAlbum( $artistName, $albumName ) {
		$album = new StdClass();
		$album->name = $albumName;
		$album->image = $this->getImage( $albumName . '.jpg' );

		$album->year = '2000';
		$album->length = '6:66';
		$album->genres = ['Hard', 'Heavy'];
		$album->itunes = 'ALBUMITUNES';

		$artist = new StdClass();
		$artist->name = $artistName;
		$artist->url = $this->buildUrl([
			'controller' => self::API_CONTROLLER_NAME,
			'method' => 'getArtist',
			LyricsApiController::PARAM_ARTIST => $artistName
		]);
		$album->artist = $artist;

		$album->songs = $this->generateSongs( 13, $artistName, $albumName );
		return $album;
	}

	/**
	 * @desc Returns mocked data about a song
	 *
	 * @param String $artistName
	 * @param String $albumName
	 * @param String $songName
	 *
	 * @return StdClass
	 */
	public function getSong( $artistName, $songName ) {
		$song = new StdClass();
		$song->name = $songName;
		$song->lyrics = str_repeat('Lorem ipsum opusum'.PHP_EOL, 10);
		$song->itunes = 'SONGITUNES';

		$artist = new StdClass();
		$artist->name = $artistName;
		$artist->url = $this->buildUrl([
			'controller' => self::API_CONTROLLER_NAME,
			'method' => 'getArtist',
			LyricsApiController::PARAM_ARTIST => $artistName
		]);
		$song->artist = $artist;

		// Songs without album
		if ( ( rand(1, 10) % 2) === 0 ) {
			$albumName = 'Album #' . rand( 1, 10 );

			$album = new StdClass();
			$album->name = $albumName;
			$album->image = $this->getImage( $albumName . '.jpg' );
			$album->url = $this->buildUrl([
				'controller' => self::API_CONTROLLER_NAME,
				'method' => 'getAlbum',
				LyricsApiController::PARAM_ARTIST => $artistName,
				LyricsApiController::PARAM_ALBUM => $albumName
			]);
			$song->album = $album;
		}

		return $song;
	}

	/**
	 * @desc Returns mocked search results for an artist
	 *
	 * @param String $query
	 * @param Integer $limit
	 * @param Integer $offset
	 *
	 * @return array
	 */
	public function searchArtist( $query, $limit, $offset ) {
		$artists = [];
		for ( $i = 0; $i < 5; $i++ ) {
			$artist = new StdClass();
			$artist->name = $query . $i;
			$artist->image = $this->getImage( $artist->name . '.jpg' );
			$artist->url = $this->buildUrl([
				'controller' => self::API_CONTROLLER_NAME,
				'method' => 'getArtist',
				LyricsApiController::PARAM_ARTIST => $artist->name
			]);
			$artists[] = $artist;
		}
		return $artists;
	}

	/**
	 * @desc Returns mocked search results for a song
	 *
	 * @param String $query
	 * @param Integer $limit
	 * @param Integer $offset
	 *
	 * @return array
	 */
	public function searchSong( $query, $limit, $offset ) {
		$songs = [];
		for ( $i = 0; $i < 5; $i++ ) {
			$song = new StdClass();
			$song->name =  sprintf('%s  %d', $query, $i);
			$song->image =  $this->getImage();
			$song->url = $this->buildUrl([
				'controller' => self::API_CONTROLLER_NAME,
				'method' => 'getSong',
				LyricsApiController::PARAM_ARTIST => 'Mocked Artist',
				LyricsApiController::PARAM_SONG => $song->name
			]);
			$songs[] = $song;
		}
		return $songs;
	}

	/**
	 * @desc Returns mocked search results for lyrics
	 *
	 * @param String $query
	 * @param Integer $limit
	 * @param Integer $offset
	 *
	 * @return array
	 */
	public function searchLyrics( $query, $limit, $offset ) {
		$songs = [];
		for ( $i = 0; $i < 5; $i++ ) {
			$song = new StdClass();
			$song->name =  sprintf('%s  %d', $query, $i);
			$song->image =  $this->getImage();
			$song->url = $this->buildUrl([
				'controller' => self::API_CONTROLLER_NAME,
				'method' => 'getSong',
				LyricsApiController::PARAM_ARTIST => 'Mocked Artist',
				LyricsApiController::PARAM_SONG => $song->name
			]);
			$song->highlight = 'i love '.$song->name.' desperately';
			$songs[] = $song;
		}
		return $songs;
	}

}

