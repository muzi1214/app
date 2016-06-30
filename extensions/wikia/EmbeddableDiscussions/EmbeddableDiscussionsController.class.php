<?php

class EmbeddableDiscussionsController extends WikiaApiController {
	const TAG_NAME = 'discussions';
	const SORT_TRENDING = 'trending';
	const SORT_LATEST = 'latest';
	const ITEMS = '&items=';
	const CATEGORY = '&category=';
	const EMBED = '&embed=1';

	public function __construct() {
		parent::__construct();
	}

	public static function onParserFirstCallInit( Parser $parser ) {
		global $wgEnableDiscussions;

		if ( $wgEnableDiscussions ) {
			$parser->setHook( self::TAG_NAME, [ 'EmbeddableDiscussionsController', 'render' ] );
		}

		return true;
	}

	public static function onBeforePageDisplay( \OutputPage $out, \Skin $skin ) {
		\Wikia::addAssetsToOutput( 'embeddable_discussions_js' );
		\Wikia::addAssetsToOutput( 'embeddable_discussions_scss' );
		return true;
	}

	public static function render( $input, array $args, Parser $parser, PPFrame $frame ) {
		global $wgServer, $wgCityId;
		myvardump('wgCityId = ' . $wgCityId);

		$showMostRecent = filter_var( $args['latest'], FILTER_VALIDATE_BOOLEAN );
		$itemCount = empty( $args['size'] ) ? 5 : intval( $args['size'] );
		//$category = $args['category'];
		$styleOverrides = $args['style'];

		if ($showMostRecent) {
			$urlArgs = '/f?sort=' . self::SORT_LATEST;
		} else {
			$urlArgs = '/f?sort=' . self::SORT_TRENDING;
		}

		$urlArgs .= self::ITEMS . $itemCount;
		$urlArgs .= self::EMBED;

		$modelData = (new DiscussionsThreadModel( $wgCityId ))->getData(
			$showMostRecent ? self::SORT_LATEST : self::SORT_TRENDING,
			$itemCount
		);

		myvardump($modelData);

		//$discussionsDataService = new DiscussionsDataService( $wgCityId );
		//myvardump($discussionsDataService->getData());


		// Fixme: calculate additional parameters to pass to iframe

		$data = [
			'url' => $wgServer . '/d' . $urlArgs,
		];

		$templateEngine = ( new Wikia\Template\MustacheEngine )->setPrefix( __DIR__ . '/templates' );

		/*
		$html = $templateEngine->clearData()
			->setData( $data )
			->render( 'EmbeddableDiscussions.mustache' );
		*/

		$html = $templateEngine->clearData()
			->setData( $modelData )
			->render( 'DiscussionThread2.mustache' );

		//myvardump($html);

		return $html;
	}
}
