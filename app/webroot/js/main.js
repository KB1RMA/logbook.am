// Delegate .transition() calls to .animate()
// if the browser can't do CSS transitions.
if (!$.support.transition) {
    $.fn.transition = $.fn.animate;
}

(function (window, document) {
    'use strict'

    var
        $ = window.jQuery,
        $window = $(window),
        $doc = $(document),
        $body = $('body'),
        $userSettingsDropdown = null,
        $userSettings = null,
        map_canvas = null,
        map = null,
        autoComplete = {},
        Spinner = window.Spinner,
        currentSpinner = null,
        userPreferences = window.userPreferences;

    /* to call $.startLoading() on $doc for loading icon */
    $.fn.startLoading = function () {
        var
            opts = {
                lines: 13, // The number of lines to draw
                length: 4, // The length of each line
                width: 2, // The line thickness
                radius: 6, // The radius of the inner circle
                corners: 1, // Corner roundness (0..1)
                rotate: 0, // The rotation offset
                direction: 1, // 1: clockwise, -1: counterclockwise
                color: '#fff', // #rgb or #rrggbb
                speed: 1.2, // Rounds per second
                trail: 60, // Afterglow percentage
                shadow: false, // Whether to render a shadow
                hwaccel: true, // Whether to use hardware acceleration
                className: 'spinner', // The CSS class to assign to the spinner
                zIndex: 2e9, // The z-index (defaults to 2000000000)
                top: 'auto', // Top position relative to parent in px
                left: 'auto' // Left position relative to parent in px
            },
            $this = $(this),
            target = $this.find('.loading')[0];

        // Start spinning
        if (currentSpinner === null ) {
            currentSpinner = new Spinner(opts).spin(target);
        }
    }

    /* to call $.stopLoading() on $doc to remove icon */
    $.fn.stopLoading = function () {
        if (currentSpinner !== null ) {
            currentSpinner.stop();
        }
        currentSpinner = null;
    }


    /**
     * Object to save and retrieve various user preferences
     */
    userPreferences = {
        $form : null,
        latLng : null,
        prefix : 'logbook_',
        settings : {},

        init : function () {
            this.bindToForm();
            // Retrieve default settings to populate settings object
            this.retrieveSettings();
            // load settings from localStorage and populate form
            this.load();
            this.updateSettings();
            window.userPreferences = this;
        },

        bindToForm : function () {
            this.$form = $('#settings-list');
            this.$form.find('input').change(function () {
                userPreferences.retrieveSettings();
                userPreferences.save();
            });
        },

        load : function () {
            var setting;
            for (setting in this.settings) {
                userPreferences.settings[setting] = window.localStorage.getItem(this.prefix + setting);
            }
            return this.settings;
        },

        save : function () {
            var setting;
            for (setting in this.settings) {
                window.localStorage.setItem(userPreferences.prefix + setting, userPreferences.settings[setting]);
            }
        },

        retrieveSettings : function () {
            var settings = {};
            this.$form.find(':input').each(function () {
                var $element = $(this);
                if ($element.is(':checked')) {
                    settings[$element.attr('name')] = $element.val();
                } else {
                    settings[$element.attr('name')] = 0;
                }
            });
            this.settings = settings;
        },

        updateSettings : function () {
            var
                setting,
                $element;

            for (setting in this.settings) {
                $element = userPreferences.$form.find('[name="' + setting + '"]');
                if (userPreferences.settings[setting] !== "0") {
                  $element.attr('checked', true);
                } else {
                  $element.attr('checked', false);
                }
            }
        }

    }


    /**
     * Object to interact with the auto complete results
     */
    autoComplete = {

        endPoint : '/callsigns/autocomplete',
        results : null,
        resultsTable : '',
        $resultsTable : null,
        $resultsContainer : null,
        $callsignInput : null,
        $callsignFind : null,
        $searchContainer : null,
        $callSearchContainer : null,

        init : function () {
            // Populate all DOM elements we need to interact with
            this.$resultsTable = $('#callsign-results table');
            this.$resultsContainer = $('#callsign-results');
            this.$callsignInput = $('#callsign-input');
            this.$callsignFind = $('#callsign-find');
            this.$searchContainer = $('#callsign-entry');
            this.$callSearchContainer = $('#call-search');

            // Every time a key is released on the callsign input, autocomplete the results
            $body.on('keyup', '#callsign-input', function () {
                var val = this.value;

                // Only if it isn't empty
                if (val !== '') {
                    autoComplete.find(val);
                }
            });

            // On pageload, if there's something in the callsign input box, populate the results
            if (this.$callsignInput.val()) {
                autoComplete.find(this.value);
            }

            // Hide find button if JS is enabled (screw you, poka)
            autoComplete.$callsignFind.hide();

            // When callsign input looses focus, hide the container
            autoComplete.$callsignInput.blur(function () {
                setTimeout(function () {
                    autoComplete
                        .closeResults()
                        .resizeCallSearch()
                    ;
                }, 200);
            });

            // When callsign input gains focus, populate results if it's not empty
            autoComplete.$callsignInput.focus(function () {
                autoComplete.find(this.value);
            });

            return this;
        },

        find : function (partialCall) {
            var
                ajaxOptions = {
                    type     : 'POST',
                    url      : this.endPoint,
                    data     : { 'callsign' : partialCall },
                    dataType : 'json'
                };

            $.ajax(ajaxOptions)
              .done(function (data, textStatus, jqXHR) {
                  autoComplete.results = data;
                  autoComplete
                    .processResults()
                    .buildResultsTable()
                    .populateResultsTable()
                  ;
              });
        },

        processResults : function () {
            var processed = [];

            // Loop through JSON containing results
            $.each(this.results.callsigns, function (i, result) {
                if (result.Person  === undefined) { result.Person  = {}; }
                if (result.Address === undefined) { result.Address = {}; }
                if (result.qslInfo === undefined) { result.qslInfo = {}; }

                var address = '';
                if (result.Address.locality === undefined)
                    address = result.Address.country;
                else
                    address = result.Address.locality + ', ' + result.Address.region;

                // Build results object
                processed.push({
                    callsign   : result.Callsign || '',
                    givenName  : result.Person.givenName || '',
                    familyName : result.Person.familyName || '',
                    address    : address || '',
                    lotw       : result.qslInfo.lotwLastActive || ''
                });
            });

            this.processedResults = processed;

            return this;
        },

        buildResultsTable : function () {
            var
                i = 0,
                result,
                resultsRows = '',
                lotw = '',
                anchorTag  = '',
                yearInPast = new Date(),
                resultsLength = this.processedResults.length;

            yearInPast.setYear(yearInPast.getFullYear() - 1);
            yearInPast = Math.round(yearInPast.getTime() / 1000); // Time in epoch

            for (i = 0; i < resultsLength; i = i + 1) {
                result = this.processedResults[i];
                lotw = '';

                // An 'active' LOTW user is considered to be someone who has uploaded in the past year
                if (result.lotw > yearInPast) { lotw = 'LOTW'; } else { lotw = ''; }

                anchorTag   = '<a href="/call/' + result.callsign + '/">';
                resultsRows += '<tr><td>' + anchorTag + result.callsign + '</a></td>';
                resultsRows += '<td>' + anchorTag + result.givenName + ' ' + result.familyName + '</a></td>';
                resultsRows += '<td>' + anchorTag + result.address + '</a></td>';
                resultsRows += '<td>' + lotw + '</a></td></tr>';
            }

            this.resultsTable = resultsRows;

            return this;
        },

        populateResultsTable : function () {

            // Fade results container out
            this.$resultsTable
              .transition({ opacity : 0 }, 50, function () {
                  // Reach out to the big bad DOM to populate the results
                  $(this).html(autoComplete.resultsTable);

                  autoComplete.resizeCallSearch(function () {
                      autoComplete.$resultsContainer.fadeIn(300);
                      autoComplete.$resultsTable.transition({ 'opacity' : 1 }, 50);
                  });
              })
            ;

            return this;
        },

        resizeCallSearch : function (callback) {
            var containerHeight = this.$searchContainer.outerHeight() + this.$resultsTable.outerHeight();
            this.$callSearchContainer.transition({ 'height' : containerHeight }, 100, callback);
        },

        closeResults : function () {
            this.$resultsTable.empty();

            if (! $body.hasClass('home'))
                this.$resultsContainer.fadeOut(300);

            this.resizeCallSearch();

            return this;
        }

    } // End of autoComplete object


    /**
     * Object to interact with the DX Spots
     */

    var dxSpots = {
        endPoint : '/dx_spots/call',
        results : null,
        callsign : null,
        limit : 10,
        $spotsContainer : null,

        init : function () {
            this.$spotsContainer = $('#dx-cluster-spots');

            // jump out if there's no container
            if (!this.$spotsContainer.length)
                return false;

            this.callsign = this.$spotsContainer.attr("data-callsign");
            this.limit = this.$spotsContainer.attr("data-limit");
            this.find();
        },

        find : function () {
            var
                ajaxOptions = {
                    type     : 'POST',
                    url      : this.endPoint,
                    data     : { 'callsign' : this.callsign, 'limit' : this.limit },
                    dataType : 'json'
                };

            $.ajax(ajaxOptions)
              .done(function (data, textStatus, jqXHR) {
                  dxSpots.results = data;
                  dxSpots.populateResults();
              });
        },

        populateResults : function () {
            var
                html = '<table><thead><th>band</th><th>freq</th><th>comment</th><th>time</th><th>by</th></thead><tbody>',
                seconds = new Date().getTime() / 1000;

            $.each(this.results.spots, function (i, result) {
                var
                    secondsAgo = seconds - result.time,
                    hrs = ~~ (secondsAgo / 3600),
                    mins = ~~ ((secondsAgo % 3600) / 60),
                    secs = parseInt(secondsAgo % 60);

              html += '<tr><td>' + result.band + '</td><td>' + result.frequency + '</td><td>' + result.comment + '</td><td>'+ hrs + 'h ' + mins + 'm ' + secs + 's ago</td><td>' + result.by + '</td></tr>';
            });

            html += '</tbody></table>';
            this.$spotsContainer.html(html);
        }

    }


    /**
     * Open and close settings dropdown
     */

    function toggleSettingsDropdown () {
        var dropdown_height = $userSettingsDropdown.outerHeight();

        if (!$userSettingsDropdown.hasClass('opened')) {
            $userSettingsDropdown
              .css({ top : dropdown_height * -1 })
              .transition({ top : '50px' }, 300, function () {
                  $(this).bind('clickoutside', function () { toggleSettingsDropdown(); });
              })
              .addClass('opened')
            ;
        } else {
            $userSettingsDropdown
                .transition({ top : dropdown_height * -1 })
                .removeClass('opened')
                .unbind('clickoutside')
            ;
        }
    }


    /**
     * Initialize various pieces on page load
     */

    function init () {
        $doc.foundation();
        $body = $('body');
        $userSettingsDropdown = $('#user-settings-dropdown');
        $userSettings = $('#user-settings');
        map_canvas = document.getElementById("map_canvas");

        // Bind settings dropdown and settings object
        $userSettings.click(function () { toggleSettingsDropdown(); } );
        userPreferences.init();

        // Initialize Auto call completion
        autoComplete.init();

        // DX Spot container initialization
        dxSpots.init();
    }


    /**
     * Initialize Google Maps after the API has been loaded
     */

     function mapInit () {
          // Center of US first
          var
              mapCenter = new google.maps.LatLng(40.0, -98.0),
              mapOptions = {
                  zoom: 4,
                  center: mapCenter,
                  mapTypeId: google.maps.MapTypeId.ROADMAP
              };

          // Initialize map
          window.logbookMap = new google.maps.Map(map_canvas, mapOptions);
          map = window.logbookMap;

          // Trigger load event for everything else to use
          $window.trigger('logbookmaploaded');

    }

    /**
     * Document on ready
     */

    $(function () {

        init();

        // Initialize Google Maps and visualization API if there is a map_canvas element on the page
        if (typeof(map_canvas) != 'undefined' && map_canvas != null) {
            google.load("maps", "3", {
                callback : mapInit,
                "other_params" : "sensor=true&libraries=geometry"
            });
        }

    });

  })(window, document); // End anonymous function to scope code
