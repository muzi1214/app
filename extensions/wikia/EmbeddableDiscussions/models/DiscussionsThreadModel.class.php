<?php

class DiscussionsThreadModel {
	const DISCUSSIONS_API_BASE = 'https://services.wikia.com/discussion/';
	const DISCUSSIONS_API_BASE_DEV = 'https://services.wikia-dev.com/discussion/';
	const DISCUSSIONS_API_SORT_KEY = 'trending';
	const DISCUSSIONS_API_SORT_DIRECTION = 'descending';
	const THREAD_CACHE_KEY = "embeddable_discussions_thread";

	const MCACHE_VER = '1.0';

	private $cityId;

	public function __construct( $cityId ) {
		$this->cityId = $cityId;
	}

	// GET https://services.wikia.com/discussion/3035/threads?sortKey=trending&limit=3&viewableOnly=false
	private function getRequestUrl($sortKey, $limit) {
		global $wgDevelEnvironment;

		if ( empty( $wgDevelEnvironment ) ) {
			return self::DISCUSSIONS_API_BASE . "$this->cityId/threads?sortKey=$sortKey&limit=$limit&viewableOnly=false";
		}

		return self::DISCUSSIONS_API_BASE_DEV . "$this->cityId/threads?sortKey=$sortKey&limit=$limit&viewableOnly=false";
	}

	private function apiRequest( $url ) {
		$data = Http::get( $url );
		$obj = json_decode( $data, true );
		return $obj;
	}

	private function buildPost( $rawPost, $index ) {
		global $wgContLang;

		return [
			'author' => $rawPost['createdBy']['name'],
			'authorAvatar' => $rawPost['createdBy']['avatarUrl'],
			'content' => $wgContLang->truncate($rawPost['rawContent'], 120),
			'upvoteCount' => $rawPost['upvoteCount'],
			'commentCount' => $rawPost['postCount'],
			'createdAt' => wfTimestamp( TS_ISO_8601, $rawPost['creationDate']['epochSecond'] ),
			'link' => '/d/p/' . $rawPost['id'],
			'id' => $rawPost['id'],
			'index' => $index,
		];

		return $post;
	}

	private function formatData( $rawData, $sortKey ) {
		$rawThreads = $rawData['_embedded']['threads'];

		$data = [
			'siteId' => $this->cityId,
			'discussionsUrl' => "/d/f?sort=$sortKey",
			//'rawTreads' => $rawThreads,
		];

		if ( is_array( $rawThreads ) && count( $rawThreads ) > 0 ) {
			foreach ( $rawThreads as $key => $value ) {
				$data['threads'][] = $this->buildPost( $value, $key );
			}
		}

		return $data;
	}

	public function getData( $sortKey, $limit ) {
		$memcKey = wfMemcKey( __METHOD__, self::MCACHE_VER );

		myvardump( $memcKey );

		$rawData = WikiaDataAccess::cache(
			$memcKey,
			WikiaResponse::CACHE_VERY_SHORT,
			function() use ($sortKey, $limit) {
				return $this->apiRequest( $this->getRequestUrl( $sortKey, $limit ) );
			}
		);

		$data = $this->formatData( $rawData, $sortKey );

		return $data;
	}
}
