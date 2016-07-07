<?php
$wgExtensionCredits['parserhook'][] = [
	'name' => 'Embeddable Discussions',
	'author' => [
		'pgroland',
	],
	'version' => '1',
	'url' => 'https://github.com/Wikia/app/tree/dev/extensions/wikia/EmbeddableDiscussions',
];

// models
$wgAutoloadClasses['DiscussionsDataService'] = $IP . '/extensions/wikia/Recirculation/services/DiscussionsDataService.class.php';
$wgAutoloadClasses['DiscussionsThreadModel'] = __DIR__ . '/models/DiscussionsThreadModel.class.php';


// controller
$wgAutoloadClasses['EmbeddableDiscussionsController'] =  __DIR__ . '/EmbeddableDiscussionsController.class.php';

// hooks
$wgHooks['ParserFirstCallInit'][] = 'EmbeddableDiscussionsController::onParserFirstCallInit';
$wgHooks['BeforePageDisplay'][] = 'EmbeddableDiscussionsController::onBeforePageDisplay';

// i18n
$wgExtensionMessagesFiles['EmbeddableDiscussions'] = __DIR__ . '/EmbeddableDiscussions.i18n.php';
