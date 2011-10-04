var GamingCalendar = {

	data: {},

    init: function() {
		$.nirvana.getJson('GamingCalendar', 'getEntries', {weeks:3, offset: 0}, $.proxy(function (data) {
			// get the current cookieVal
			var cookieVal = this.getCookieVal();

			// 0 if not set
			if ( cookieVal == null ) {
				cookieVal = 1;
			// +1 othewise
			} else {
				cookieVal++;
			}

			// reset, if no such item
			if ( 'undefined' == typeof data.entries[0][cookieVal] ) {
				cookieVal = 1;
			}

			// store the cookieVal for future requests
			this.setCookieVal( cookieVal );

			// tell modal, which item to expand
			data.entries[0][cookieVal].expanded = true;

			// store for future use
			this.data = data;
			this.data.thisWeek = 0;

			// grab the first item
			var item = data.entries[0][cookieVal];

			// generate HTML from template
			var itemHTML = this.renderItem(item, false);

			// insert into module (after the h1)
			$('.GamingCalendarModule h1').after(itemHTML);
        }, this));

		$('.GamingCalendarModule').click(this.showCalendar);
	},

	getCookieVal: function() {
		var cookieStart = document.cookie.indexOf( 'wikiagc=' );
		if ( cookieStart == -1 ) {
			return null;
		}
		var valueStart = document.cookie.indexOf( '=', cookieStart ) + 1;
		var valueEnd   = document.cookie.indexOf( ';', valueStart );
		if ( valueEnd == -1 ) {
			valueEnd = document.cookie.length;
		}
		var val = document.cookie.substring( valueStart, valueEnd );
		if ( val != null ) {
			return unescape( val );
		}
		return null;
    },

	setCookieVal: function( value ) {
		var expiration = new Date( new Date().getTime() + 3600000 ); // 1 hour
		var str = 'wikiagc=' + escape( value ) + '; path=/ ; expires=' + expiration.toUTCString();
		document.cookie = str;
	},

	renderItem: function(item, expanded) {
		var template = $('#GamingCalendarItemTemplate').html();

		if ( item.gameSubtitle ) {
			template = template.replace('##gameSubTitle##', '<span class="game-subtitle">' + item.gameSubtitle + '</span>');
		} else {
			template = template.replace('##gameSubTitle##', '');
		}

		if ( expanded ) {
			template = template.replace('##expanded##', 'selected');
		} else {
			template = template.replace('##expanded##', 'unselected');
		}

		template = template.replace('##gameTitle##', item.gameTitle);
		template = template.replace('##description##', item.description);
		template = template.replace('##imageSrc##', item.image.src);
		template = template.replace('##moreInfoUrl##', item.moreInfoUrl);
		template = template.replace('##preorderLink##', item.preorderUrl ? '<a href="'+item.preorderUrl+'" class="game-pre-order" target="_blank">Pre-order now</a>' : '');
		template = template.replace('##systems##', item.systems.join(', '));

		var months = new Array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
		var date = new Date(item.releaseDate * 1000); // miliseconds!
		template = template.replace('##month##', months[date.getUTCMonth()]);
		template = template.replace('##day##', date.getUTCDate());

		return template;
	},

	showCalendar: function(e) {
		e.preventDefault();

		$.tracker.byStr( 'gamingCalendar/calendar/open' );

		// Check, whether the GamingCalendarModal has already been triggered once.
		// We don't want the resources to be retrieved more than once.
		if ( 'object' == typeof GamingCalendarModal && GamingCalendarModal.initialized ) {
			GamingCalendarModal.modal.showModal();
			return;
		}

		// Load CSS and JS
		$.getResources([
			$.getSassCommonURL('/extensions/wikia/GamingCalendar/css/GamingCalendarModal.scss'),
			wgExtensionsPath + '/wikia/GamingCalendar/js/GamingCalendarModal.js'
		], function() {
			// Get markup
			$.get('/wikia.php?controller=GamingCalendar&method=getModalLayout&format=html', function(html) {
				GamingCalendarModal.modal = $(html).makeModal({width: 610, persistent: true, blackoutOpacity: 0.80});
				GamingCalendarModal.init();
			});
		});
    }
}

$(function() {
	GamingCalendar.init();
});