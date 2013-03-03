<?php

namespace app\models;

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
