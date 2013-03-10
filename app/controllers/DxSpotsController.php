<?php

namespace app\controllers;

use app\models\DxSpots;
use app\models\DxSpotsStats;
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

		$totalSpots = count($spots);
	
		$callsign = Callsigns::first(array(
			'conditions' => array('callsign' => $requestedCall ),
		));

		if ( ! count($callsign ) )
			$callsign = (Object) array('callsign' => $requestedCall);

		if ( $this->request->is('ajax') ) {
			$this->render(array(
				'type' => 'json',
				'data' => compact('spots', 'callsign', 'totalSpots')
			));
		}

		return compact('spots', 'callsign', 'totalSpots');

	}

	public function stats( $limit = 60 ) {

		$stats = DxSpotsStats::find('all', array(
			'limit' => $limit,
			'order' => array('$natural' => -1 ),
		));

		$data = array();

		foreach ( $stats as $key=>$stat ) {
			$data[] = array($stat->minute->sec, $stat->stats->total);
		}

		$this->render(array(
			'type' => 'json',
			'data' => compact('data'),
		));

	}

}

