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
			map_canvas = document.getElementById("map_canvas");

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
		var $resultsContainer = $('#callsign-results'),
				$resultsTable = $('#callsign-results table'),
				$searchContainer = $('#callsign-entry'),
				$callSearchContainer = $('#call-search'),
				i = 0,
				resultRows = '',
				anchorTag	= '';

		// Loop through results		
		$.each(results.callsigns, function(i, result) {
			anchorTag = '<a href="/call/' + result.callsign + '/">';		
			resultRows += '<tr><td>' + anchorTag + result.callsign + '</a></td>';
			resultRows += '<td>' + anchorTag + result.first_name + ' ' + result.last_name + '</a></td>';
			resultRows += '<td>' + anchorTag + result.city + ', ' + result.state + '</a></td></tr>';
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
		var $callSearchContainer = $('#call-search'),
				$resultsContainer = $('#callsign-results'),
				$resultsTable = $('#callsign-results table'),
				$searchContainer = $('#callsign-entry'),
				containerHeight = $searchContainer.outerHeight() + $resultsTable.outerHeight();
		
		$callSearchContainer.transition( { 'height' : containerHeight }, 100, callback );

	}

	function closeResultsContainer() {
		var	$resultsTable = $('#callsign-results table'),
				$resultsContainer = $('#callsign-results');

		$resultsTable.empty();

		if ( ! $body.hasClass('home') )
			$resultsContainer.fadeOut(300);

		resizeCallSearch();
	}
	
	/**
	 * Initialize various pieces on page load
	 */

	function init() {

		var $body = $('body'),
				$callsignInput = $('#callsign-input'),
				$callsignFind = $('#callsign-find'),
				$resultsContainer = $('#callsign-results');
				$callSearch = $('#call-search');

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

	}


	/**
	 * Initialize Google Maps after the API has been loaded
	 */
	 
	 function mapInit() {
			
			// Find lat and lng on the page so we know where to center the map
			var lat = document.getElementById("mapLat").innerHTML,
					lng = document.getElementById("mapLng").innerHTML,
					latLng = new google.maps.LatLng(lat, lng),
					mapOptions = {
						zoom: 10,
						center: latLng,
						mapTypeId: google.maps.MapTypeId.ROADMAP
					};

			// Initialize map
			map = new google.maps.Map(map_canvas, mapOptions);
			
			// Set marker
			var marker = new google.maps.Marker({ map : map, position : latLng });	

	 }

	/**
	 * Document on ready
	 */

	$(function() {

		init();	

		// Initialize Google Maps API if there is a map_canvas element on the page
		if ( typeof(map_canvas) != 'undefined' && map_canvas != null ) {
			google.load("maps", "3", {
				callback : mapInit,
				"other_params" : "sensor=true"
			});
		}

	});

})(window); // End anonymous function to scope code
