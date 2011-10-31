/**
 * @namespace Wikia
 */
var Wikia = {};

(function(ns){
	/**
	 * @private
	 */

	var ua = navigator.userAgent,
	isApp = (typeof Titanium != 'undefined'),
	isIPad = /iPad/i.test(ua),
	isIPhone = /iPhone/i.test(ua),
	isIPod = /iPod/i.test(ua),
	isAndroid = /Android/i.test(ua),
	isDesktop = !isApp && !(isIPad || isIPhone || isIPod || isAndroid);

	/**
	 * @public
	 */

	ns.Platform = {
		is: function(){
			var result = true, result2, x, y;

			for(x = 0, y = arguments.length; x < y; x++){
				switch(arguments[x]){
					case 'app':
						result2 = isApp;
						break;
					case 'web':
						result2 =  !isApp;
						break;
					case 'desktop':
						result2 =  isDesktop;
						break;
					case 'mobile':
						result2 =  !isDesktop;
						break;
					case 'ios':
						result2 =  (isIPad || isIPhone || isIPod);
						break;
					case 'ipad':
						result2 =  isIPad;
						break;
					case 'ipod':
						result2 =  isIPod;
						break;
					case 'iphone':
						result2 =  isIPhone;
						break;
					case 'android':
						result2 = isAndroid;
						break;
					default:
						result2 = false;
						break;
				}

				result = result && result2;
			}

			return result;
		}
	};

	ns.i18n = {
		setup: function(data){
			if(Wikia.Platform.is('app'))
				wgMessages = data;
		},

		Msg: function(messageId){
			return wgMessages[messageId] || ('[' + messageId + ']');
		}
	};

	ns.log = function(msg){
		if(Wikia.Platform.is('app'))
			Titanium.API.debug(msg);
		else if(console && console.log)
			console.log(msg);
	}

	/**
	 * XPlatform/platform-specific fixes and polyfills
	 */

	if(Wikia.Platform.is('app', 'ios')){
		window.requireJsGetUrl = function(url){
			return 'app://Resources/' + url;
		};
	}

	Array.prototype.shuffle = function() {
		var s = [];

		while(this.length){
			s.push(this.splice(Math.floor(Math.random() * this.length), 1)[0]);
		}

		while(s.length){
			this.push(s.pop());
		}
		return this;
	};
})(Wikia);