<?php

namespace app\models;

class DxSpotsStats extends \lithium\data\Model {

	protected $_schema = array(
		'_id' => array('type' => 'id'),
		'minute' => array('type' => 'date'),
		'stats' => array('type' => 'object'),
			'stats.total' => array('type' => 'int'),
			'stats.160'=> array('type' => 'int', 'default' => 0),
			'stats.80' => array('type' => 'int', 'default' => 0),
			'stats.60' => array('type' => 'int', 'default' => 0),
			'stats.40' => array('type' => 'int', 'default' => 0),
			'stats.30' => array('type' => 'int', 'default' => 0),
			'stats.20' => array('type' => 'int', 'default' => 0),
			'stats.17' => array('type' => 'int', 'default' => 0),
			'stats.15' => array('type' => 'int', 'default' => 0),
			'stats.12' => array('type' => 'int', 'default' => 0),
			'stats.10' => array('type' => 'int', 'default' => 0),
			'stats.6'  => array('type' => 'int', 'default' => 0),
	);

}
