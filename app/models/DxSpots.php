<?php

namespace app\models;

use lithium\util\collection\Filters;
use app\models\DxSpotsStats;

class DxSpots extends \lithium\data\Model {

	protected $_schema = array(
		'_id' => array('type' => 'id'),
		'callsign' => array('type' => 'string', 'null' => false),    
    'frequency' => array('type' => 'float'),
    'comment' => array('type' => 'string'),
    'time' => array('type' => 'date'),
    'by' => array('type' => 'string'),
	);

}

Filters::apply('app\models\DxSpots', 'save', function($self, $params, $chain ) {

		// Determine what band the spot is in and set it
	if ( !$params['entity']->exists() ) {

		$entity = $params['entity'];
		$frequency = floatval($entity->frequency);

		switch (true) {
			case $frequency >= 1800 && $frequency <= 1900:
				$entity->band = '160';
				break;
			case $frequency >= 3500 && $frequency <= 4000:
				$entity->band = '80';
				break;
			case $frequency >= 7000 && $frequency <= 7300:
				$entity->band = '40';
				break;
			case $frequency >= 14000 && $frequency <= 14300:
				$entity->band = '20';
				break;
			case $frequency >= 18068 && $frequency <= 18168:
				$entity->band = '17';
				break;
			case $frequency >= 21000 && $frequency <= 21450:
				$entity->band = '15';
				break;
			case $frequency >= 24890 && $frequency <= 24990:
				$entity->band = '12';
				break;
			case $frequency >= 28000 && $frequency <= 29700:
				$entity->band = '10';
				break;
		}
		
		$entity->frequency = $frequency;
		$params['entity'] = $entity;
			
	}

	$nearestMinute = round(time()/60) * 60;

	$criteria = array('minute' => $nearestMinute);
	$options = array('upsert' => true);					
	$document = array('$inc' => array(
		'stats.total' => 1,
		'stats.' . $entity->band => 1,
	));
	
	DxSpotsStats::update($document, $criteria, $options); 

	$response = $chain->next($self, $params, $chain);

	return $response;
});
