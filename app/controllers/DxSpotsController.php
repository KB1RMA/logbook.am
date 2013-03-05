<?php

namespace app\controllers;

use app\models\DxSpots;
use app\models\Callsigns;

class DxSpotsController extends \lithium\action\Controller {

	public function index() {

		$spots = DxSpots::find('all', array(
			'limit' => 50,
			'order' => array('$natural' => -1 ),
			));

		return compact('spots');

	}

	public function call( $requestedCall = null ) {
		$requestedCall = strtoupper($requestedCall);

		if ( ! $requestedCall )
			$requestedCall = strtoupper($this->request->data['callsign']);

		$limit = array_key_exists('limit', $this->request->data) ? $this->request->data['limit'] : null;

		$spots = DxSpots::find('all', array(
			'conditions' => array(
				'callsign' => strtoupper($requestedCall),
			),
			'order' => array('time' => 'DESC'),
			'limit' => $limit,
		));
	
		$callsign = Callsigns::first(array(
			'conditions' => array('callsign' => $requestedCall ),
		));

		if ( ! count($callsign ) )
			$callsign = (Object) array('callsign' => $requestedCall);

		if ( $this->request->is('ajax') ) {
			$this->render(array(
				'type' => 'json',
				'data' => compact('spots')
			));
		}

		return compact('spots', 'callsign');

	}

}

