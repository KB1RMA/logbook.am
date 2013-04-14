<?php

namespace app\controllers;

use app\models\Callsigns;
use li3_geo\data\Geocoder;

class HamCreeperController extends \lithium\action\Controller {

	public function index() {

		return;
	}

	public function find() {
		
		$lat    = $this->request->query['lat'];
		$lng    = $this->request->query['lng'];
		$radius = intval($this->request->query['radius']);

		// Find zip code of current map center and find all callsigns matching that zip
		$location = Geocoder::find('google', array('latitude' => $lat, 'longitude' => $lng));

		if ( $location ) {
			$location = $location->address();
			$zip = $location['postalCode'];
		} else {
			$zip = null;
		}
		
		if ( $zip ) {
			$forGeocoding = Callsigns::find('all', array(
				'conditions' => array(
					'Address.postalCode' => $zip,
				),
			));

			foreach ( $forGeocoding as $callsign )
				$callsign->getLatitude(); // Force geocoding
		}

		$callsigns = Callsigns::find('all', array( 
			'conditions' => array( 
				'Location' => array(
					'$near' =>  array($lng, $lat ),
					'$maxDistance' => $radius,
				),
			),
		));

			
		$this->render(array(
			'type' => 'json',
			'data' => compact('zip', 'callsigns')
		));

	}

}
