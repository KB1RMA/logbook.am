;(function(window, undefined) {
	'use strict'


	var $ = window.jQuery,
	    $window = $(window),
			hams = [],
			map = null;


	function creeperInit() {
		// bring the map into scope
		map = window.logbookMap;		
		
		console.log('creeper initialized');

	}

	$(document).ready(function() {
		
		// Initialize when the map has loaded
		$window.bind('logbookmaploaded', creeperInit);

	});

})(window);
