;(function () {
	'use strict'

	var $ = window.jQuery,
	    data = [],
	    graph = null,
	    options = {
				lines : {
					lineWidth : 0,
					color: '#000',
					fill: true,
					fillColor: 'rgba(59,162,169,.4)',
				}
			};

	function plotData ( receivedData ) {
		data = receivedData.data;

		graph = $.plot('#all-band-stats', [data], options );

	}

	function retrieveData () {

		$.ajax({
				url: '/dx_spots/stats',
				type: 'POST',
				dataType: 'json',
				success: plotData
		});

	}

	$(document).ready(function() {

		retrieveData();

		setInterval(function() {
			retrieveData();
		}, 5000);

	});

})();
