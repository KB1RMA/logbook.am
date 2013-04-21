<?php

namespace app\controllers;

use app\models\Callsigns;
use li3_geo\data\Geocoder;

class HamCreeperController extends \lithium\action\Controller {

	public function index() {

		return;
	}

	public function find() {

		$lat    = (double)$this->request->query['lat'];
		$lng    = (double)$this->request->query['lng'];
		$radius = intval($this->request->query['radius']);

		$callsigns = Callsigns::find('all', array(
			'fields' => array( 'Callsign', 'Location' ),
			'limit' => '2000',
			'conditions' => array(
				'Location' => array(
					'$geoWithin' => array(
						'$center' => array( array($lng, $lat), $radius )
					),
				),
			),
		));

		$this->render(array(
			'type' => 'json',
			'data' => compact('callsigns')
		));

	}

}