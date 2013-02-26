// Delegate .transition() calls to .animate()
// if the browser can't do CSS transitions.
if (!$.support.transition)
		$.fn.transition = $.fn.animate;

(function( window, undefined) {

	/**
	 * Prepare variables for use within scope
	 */
	
	var $ = window.jQuery, 
			$body = $('body'),
			$resultsTable = null,
			$resultsContainer = null,
			$callsignInput = null,
			$callsignFind = null,
			$searchContainer = null,
			$callSearchContainer = null,
			$userSettingsDropdown = null,
			$useMyLocation = null,
			map_canvas = null,
			elevation_profile = null,
			polyline = null,
			callsignInformation = {}, 
			bounds = null,
			chart = null,
			elevation = null;

	/**
	 * Object to save and retrieve various user preferences
	 */

	var userPreferences = {
		$form : null,
		latLng : null,
		prefix : 'logbook_',
		settings : {},

		init : function() {
			this.bindToForm();
			// Retrieve default settings to populate settings object 
			this.retrieveSettings();
			// load settings from localStorage and populate form
			this.load();
			this.updateSettings();
		},

		bindToForm : function() {
			this.$form = $('#settings-list');
			this.$form.find('input').change(function() { 
				userPreferences.retrieveSettings();	
				userPreferences.save();	
			});
		},

		load : function() {
			for ( var setting in this.settings ) {
				userPreferences.settings[setting] = window.localStorage.getItem( this.prefix + setting );
			}
			return this.settings;
		},

		save : function() {
			for ( var setting in this.settings ) {
				window.localStorage.setItem( userPreferences.prefix + setting, userPreferences.settings[setting] );	
			}
			console.log(this.settings);
		},

		retrieveSettings : function() {
			var settings = new Object;
			this.$form.find(':input').each( function() {
				$element = $(this);
				if ( $element.is(':checked') )
					settings[$element.attr('name')] = $element.val();
				else 
					settings[$element.attr('name')] = 0;
			});
			this.settings = settings;
		},

		updateSettings : function() {
			for ( var setting in this.settings ) {
				$element = userPreferences.$form.find('[name="' + setting + '"]');
				if ( userPreferences.settings[setting] !== "0"  )
					$element.attr('checked', true);
				else 
					$element.attr('checked', false);
			}
		}

	}

	/**
	 * Grab results from the Callsigns::autocomplete controller
	 */

	function autoComplete( partialCall ) {

		var requestData = { 'callsign' : partialCall };
		
		$.ajax( {
				type : 'POST',
				url : '/callsigns/autocomplete', 
				data : requestData,
				dataType : 'json'
			})
			.done( function( data, textStatus, jqXHR ) { populateAutoCompleteResults( data ); });
	}


	/**
	 * Populate the autocomplete box with results 
	 */

	function populateAutoCompleteResults ( results ) {
		var i = 0,
				resultRows = '',
				anchorTag	= '',
				yearInPast = new Date();
				
		yearInPast.setYear(yearInPast.getFullYear() - 1);
		yearInPast = Math.round(yearInPast.getTime() / 1000); // Time in epoch

		// Loop through results		
		$.each(results.callsigns, function(i, result) {
			if ( result.person === undefined ) { result.person = {}; }
			if ( result.address === undefined ) { result.address = {}; }
			if ( result.qslInfo === undefined ) { result.qslInfo = {}; }

			var callsign = result.callsign || '',
			    givenName = result.person.givenName || '',
			    familyName = result.person.familyName || '',
			    locality = result.address.locality || '',
			    region = result.address.region || '',
          lotw = result.qslInfo.lotwLastActive || '';

			// An 'active' LOTW user is considered to be someone who has uploaded in the past year
			if ( lotw > yearInPast ) { lotw = 'LOTW'; } else { lotw = ''; }

			anchorTag = '<a href="/call/' + callsign + '/">';		
			resultRows += '<tr><td>' + anchorTag + callsign + '</a></td>';
			resultRows += '<td>' + anchorTag + givenName + ' ' + familyName + '</a></td>';
			resultRows += '<td>' + anchorTag + locality + ', ' + region + '</a></td>';
			resultRows += '<td>' + lotw + '</a></td></tr>';
		});
		
		// Fade results container out
		$resultsTable
			.transition( { opacity : 0 }, 50, function() { 
				
				// Reach out to the big bad DOM to populate the results
				$(this).html(resultRows); 
				
				resizeCallSearch(function() { 
					$resultsContainer.fadeIn(300);
					$resultsTable.transition( { 'opacity' : 1 }, 50 );	
				});			
		});

	}

	function resizeCallSearch( callback ) {
		var containerHeight = $searchContainer.outerHeight() + $resultsTable.outerHeight();
		
		$callSearchContainer.transition( { 'height' : containerHeight }, 100, callback );

	}

	function closeResultsContainer() {
		$resultsTable.empty();

		if ( ! $body.hasClass('home') )
			$resultsContainer.fadeOut(300);

		resizeCallSearch();
	}
	
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
		$useMyLocation.addClass('active');
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
	 * Open and close settings dropdown
	 */

	function toggleSettingsDropdown() {
		var dropdown_height = $userSettingsDropdown.outerHeight();

		if ( !$userSettingsDropdown.hasClass('opened') ) {
			$userSettingsDropdown
				.css( { top : dropdown_height * -1 })
				.transition( { top : '50px' }, 300, function() { 
						$(this).bind('clickoutside', function() { toggleSettingsDropdown() }); 
				})
				.addClass('opened');
		} else {
			$userSettingsDropdown
				.transition( { top : dropdown_height * -1 })
				.removeClass('opened')
				.unbind('clickoutside');
		}
		
	}


	/**
	 * Initialize various pieces on page load
	 */

	function init() {

		$body = $('body');
		$resultsTable = $('#callsign-results table');
		$resultsContainer = $('#callsign-results');
		$callsignInput = $('#callsign-input');
		$callsignFind = $('#callsign-find');
		$searchContainer = $('#callsign-entry');
		$callSearchContainer = $('#call-search');
		$userSettingsDropdown = $('#user-settings-dropdown');
		$userSettings = $('#user-settings');
		map_canvas = document.getElementById("map_canvas");
		elevation_profile = document.getElementById("elevation_profile");
		$useMyLocation = $('#use-my-location');

		// Bind settings dropdown and settings object
		$userSettings.click( function() { toggleSettingsDropdown() } );
		userPreferences.init();
		
		// Every time a key is released on the callsign input, autocomplete the results
		$body.on('keyup', '#callsign-input', function() { 
			var val = this.value;

			// Only if it isn't empty
			if ( val !== '' )
				autoComplete( val ); 
		});

		// On pageload, if there's something in the callsign input box, populate the results
		if ( $callsignInput.val() )
			autoComplete( this.value );

		// Hide find button if JS is enabled (screw you, poka)
		$callsignFind.hide();

		// When callsign input looses focus, hide the container
		$callsignInput.blur(function() { 
			setTimeout( function() { closeResultsContainer();	resizeCallSearch(); }, 200 );
		});

		// When callsign input gains focus, populate results if it's not empty
		$callsignInput.focus(function() { autoComplete( this.value ); });

		// Enable location button if the BROWSER-EXPERIENCE is adequate (looking at you, Scotty)
		if(navigator.geolocation) {
			$useMyLocation.addClass('enabled');

			if ( ! userPreferences.settings['use-location'] )
				$useMyLocation.click( function (event) { placeUserOnMap(); event.preventDefault(); 	});
	
		}

		$('#show-elevation-profile').click(function() { createElevationProfile(); return false; });
		
	}


	/**
	 * Initialize Google Maps after the API has been loaded
	 */
	 
	 function mapInit() {
			
			// Find lat and lng on the page so we know where to center the map
			var lat = document.getElementById("mapLat").innerHTML,
					lng = document.getElementById("mapLng").innerHTML;

			callsignInformation.latLng = new google.maps.LatLng(lat, lng);

			var	mapOptions = {
						zoom: 10,
						center: callsignInformation.latLng,
						mapTypeId: google.maps.MapTypeId.ROADMAP
					};

			// Initialize map
			map = new google.maps.Map(map_canvas, mapOptions);
			
			// Set marker
			var marker = new google.maps.Marker({ map : map, position : callsignInformation.latLng });	
			
			// add marker to Maps bounds
			bounds = new google.maps.LatLngBounds();
			bounds.extend(callsignInformation.latLng);

			if ( userPreferences.settings['use-location'] !== "0" ) 
				placeUserOnMap();
			
		}

	/**
	 * Document on ready
	 */

	$(function() {

		init();	

		// Initialize Google Maps and visualization API if there is a map_canvas element on the page
		if ( typeof(map_canvas) != 'undefined' && map_canvas != null ) {
			google.load("maps", "3", {
				callback : mapInit,
				"other_params" : "sensor=true"
			});
		}

	});

})(window); // End anonymous function to scope code
