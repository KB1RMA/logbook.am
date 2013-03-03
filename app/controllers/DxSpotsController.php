<?php

namespace app\controllers;

use app\models\DxSpots;

class DxSpotsController extends \lithium\action\Controller {

	public function index() {

		$spots = DxSpots::find('all', array(
			'limit' => 50,
			'order' => array('time' => 'DESC'),
			));

		return compact('spots');

	}

}

