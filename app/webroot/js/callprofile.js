;(function(window, undefined) {
	'use strict'


	var $ = window.jQuery,
	    $window = $(window),
			elevationService = null,
			elevation_profile = null,
			polyline = null,
			bounds = null,
			chart = null,
			elevations = null,
	    callsignInformation = {}, 
			map = null;

	function placeUserOnMap( callback ) {
			if ( navigator.geolocation ) {
				navigator.geolocation.getCurrentPosition( function(position) {
					userPreferences.latLng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
					var marker = new google.maps.Marker({ map : map, position : userPreferences.latLng });	

					// Put a line on the map between user and callsign
					if (polyline) 
						polyline.setMap(null);
					
					polyline = new google.maps.Polyline({
						path: [ userPreferences.latLng, callsignInformation.latLng ],
						strokeColor: "#000000",
						map: map});

					// Zoom the map out to show user and callsign
					bounds.extend(userPreferences.latLng);
					map.fitBounds(bounds);
					setTimeout(callback, 200 );
				});
			}
	}


	/**
	 * Load the Visualization API and the piechart package.
	 */
	function loadVisualizations( callback ) {
		  google.load("visualization", "1", {
				callback : callback,
				packages: ["columnchart"]
			});
	}

	/**
	 * Elevation profile between user location and callsign
	 */

	function createElevationProfile() {

		if ( google.visualization === undefined ) {
			loadVisualizations( createElevationProfile );
			return; 
		}

		chart = new google.visualization.ColumnChart(elevation_profile);
		elevationService = new google.maps.ElevationService();

		// combine user and callsign latLng objects
		
		if ( userPreferences.latLng === undefined || userPreferences.latLng === null ) {
			if ( confirm('We need your location. Would you like to find it now?') )
				placeUserOnMap(createElevationProfile);

			return;
		}
		
		var latLngs = [ userPreferences.latLng, callsignInformation.latLng ];

		elevationService.getElevationAlongPath({
			path: latLngs,
			samples: 256
		}, plotElevation);
	}
	

	/**
	 * Takes an array of ElevationResult objects, draws the path on the map
	 * and plots the elevation profile on a GViz ColumnChart
	 */

	function plotElevation(results) {
		elevations = results;
		 
		var data = new google.visualization.DataTable();
		data.addColumn('string', 'Sample');
		data.addColumn('number', 'Elevation');
		for (var i = 0; i < results.length; i++) {
			data.addRow(['', elevations[i].elevation]);
		}

		elevation_profile.style.display = 'block';
		chart.draw(data, {
			height: 200,
			legend: 'none',
			titleY: 'Elevation (m)',
			focusBorderColor: '#00ff00'
		});
	}


	/**
	 * General page initialization on document ready
	 */

	function init() {
		elevation_profile = document.getElementById("elevation_profile");

		// Find lat and lng on the page so we know where to center the map
		callsignInformation.lat = $('#mapLat').html(),
		callsignInformation.lng = $('#mapLng').html();

		$('#show-elevation-profile').click(function() { createElevationProfile(); return false; });

		// Initialize when the map has loaded
		$window.bind('logbookmaploaded', mapInit);
	}


	/**
	 * Anything on the callsign page that requires the map to be initialized
	 * this is bound to the map initialization event
	 */
	
	function mapInit() {

		// bring the map into scope
		map = window.logbookMap;		

		callsignInformation.latLng = new google.maps.LatLng(callsignInformation.lat, callsignInformation.lng);

		// Set marker
		var marker = new google.maps.Marker({ map : map, position : callsignInformation.latLng });	
		
		// add marker to Maps bounds
		bounds = new google.maps.LatLngBounds();
		bounds.extend(callsignInformation.latLng);

		if ( userPreferences.settings['use-location'] !== "0" ) 
			placeUserOnMap();

	}

	$(document).ready(function() { init(); });

})(window);
