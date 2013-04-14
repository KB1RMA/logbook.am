;(function(window, undefined) {
	'use strict'


	var $ = window.jQuery,
	    $window = $(window),
			google = window.google,
			hams = [],
			map = null,
			mapCenter = null;

	function doTheCreep() {
		console.log('doing the creep');
		// Find map center and displayed radius in miles
		var bounds = map.getBounds(),
				sw = bounds.getSouthWest(),
				ne = bounds.getNorthEast(),
				proximitymeters = google.maps.geometry.spherical.computeDistanceBetween (sw, ne),
				center = bounds.getCenter(),
				centerLat = center.hb,
				centerLng = center.ib;

		console.log(proximitymeters);

		var ajaxOptions = { 
					type     : 'GET',
					url      : '/ham-creeper/find',
					data     : { 'lat' : centerLat, 'lng' : centerLng, 'radius' : proximitymeters },
					dataType : 'json' 
				};

		$.ajax( ajaxOptions )
			.done( function( data, textStatus, jqXHR ) { 
				var element_count = 0;
				$.each(data.callsigns, function(call, data) {
					element_count++;
					var location = new google.maps.LatLng( parseFloat(data.Location.lat), parseFloat(data.Location.lng) );
					hams.push( new google.maps.Marker({ map: map, position: location }) );
				});
				console.log(element_count);
			});
	}		

	function centerMapOnUser() {
		if ( navigator.geolocation ) {
			navigator.geolocation.getCurrentPosition( function(position) {
				map.setCenter(new google.maps.LatLng(position.coords.latitude, position.coords.longitude));
				map.setZoom(10);
			});
		}
	}


	function creeperInit() {
		// bring the map into scope
		map = window.logbookMap;		
		
		centerMapOnUser();

		google.maps.event.addListener(map, 'idle', doTheCreep);
		
	}

	$(document).ready(function() {
		
		// Initialize when the map has loaded
		$window.bind('logbookmaploaded', creeperInit);

	});

})(window);