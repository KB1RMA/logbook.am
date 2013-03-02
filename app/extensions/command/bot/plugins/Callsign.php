<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright  Copyright 2009, Union of RAD (http://union-of-rad.org)
 * @license	   http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace app\extensions\command\bot\plugins;

use app\models\Callsigns;
use \lithium\util\String;

/**
 * Callsign plugin
 *
 */
class Callsign extends \app\extensions\command\bot\Plugin {

	protected $_classes = array(
		'response' => '\lithium\console\Response'
	);

	/**
	 * possible responses
	 *
	 * @var array
	 */
	protected $_responses = array(
		'notfound' => '{:requestedCall} not found',
	);

	/**
	 * Process incoming messages
	 *
	 * @param string $data
	 * @return string
	 */
	public function process($data) {
		$responses = $this->_responses;
		$callsign = null;
		$response = '';
		extract($data);

		$callsigns = preg_split("/[\s]/", $message, 2);

		if ($callsigns[0] != 'c') 
			return;

		if (!isset($callsigns[1])) 
			return;

		$requestedCall = strtoupper($callsigns[1]);

		$callsign = Callsigns::first(array( 
			'conditions' => array( 
				'callsign' => $requestedCall,
			)
		));
		
		if ( !count($callsign) )
			return String::insert($responses['notfound'], compact('requestedCall'));

		$response .= $callsign->callsign . ' - ';

		if ( isset($callsign->uls) )
			$response .= 'Class: ' . $callsign->uls->licenseClass . ' - ';

		if ( $callsign->getFullName() )
			$response .= $callsign->getFullName() . ' - ';

		if ( $callsign->getFullAddress() )
			$response .= $callsign->getFullAddress() . ' - ';

		$response .= 'Gridsquare: ' . $callsign->getGridSquare() . ' - ';
		$response .= 'ITU Zone: ' . $callsign->getItuZone() . ' - ';
		$response .= 'WAZ Zone: ' . $callsign->getWazZone() . ' - ';
		$response .= 'Country: ' . $callsign->getCountry() . ' - ';
		$response .= 'Continent: ' . $callsign->getContinent() . ' - ';
		$response .= 'Lat: ' . $callsign->getLatitude() . ' - ';
		$response .= 'Lng: ' . $callsign->getLongitude() . ' - ';

		$response .= 'http://lookup.logbook.am/call/' . $requestedCall;

		return $response;
	
	}
}

?>
