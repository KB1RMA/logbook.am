<?php

namespace app\controllers;

use app\models\DxSpots;

class DxSpotsController extends \lithium\action\Controller {

	public function index() {

		$spots = DxSpots::find('all', array(
			'limit' => 50,
			'order' => array('$natural' => -1 ),
			));

		return compact('spots');

	}

	public function call( $requestedCall = null ) {
		if ( ! $requestedCall )
			$requestedCall = $this->request->data['callsign'];

		$spots = DxSpots::find('all', array(
			'conditions' => array(
				'callsign' => strtoupper($requestedCall),
			),
			'order' => array('time' => 'DESC'),
		));
		
		$this->render(array(
			'type' => 'json',
			'data' => compact('spots')
		));

	}

}

