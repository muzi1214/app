require([
	'jquery',
	'wikia.tracker',
], function ($, tracker) {
	'use strict';

	var track = tracker.buildTrackingFunction({
		action: tracker.ACTIONS.CLICK,
		category: 'embeddable-discussions',
		trackingMethod: 'analytics'
	});

	function iframeEventListener(event) {
		var data = event.data;

		if (data.hasOwnProperty('height')) {
			$('#embeddable-discussions').css('height', data.height);
		}
	}

	$(function() {
		console.log('ext.embeddableDiscussions.js');

		if (window.addEventListener) {
			window.addEventListener("message", iframeEventListener, false)
		} else {
			window.attachEvent("onmessage", iframeEventListener)
		}

		// Track impression
		track({
			action: tracker.ACTIONS.IMPRESSION,
			label: 'embeddable-discussions-loaded',
		});
	});
});
