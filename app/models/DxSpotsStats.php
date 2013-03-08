<?php

namespace app\models;

class DxSpotsStats extends \lithium\data\Model {

	protected $_schema = array(
		'_id' => array('type' => 'id'),
		'minute' => array('type' => 'date'),
		'stats' => array('type' => 'object'),
			'stats.total' => array('type' => 'int'),
	);

}
