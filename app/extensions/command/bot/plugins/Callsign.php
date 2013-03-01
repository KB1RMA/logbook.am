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

		if ($callsigns[0] != 'c2') 
			return;

		if (!isset($callsigns[1])) 
			return;

		$requestedCall = strtoupper($callsigns[1]);

		$callsign = Callsigns::first(array( 
			'conditions' => array( 
				'callsign' => $requestedCall,
			)
		));
		
		if (!count($callsign))
			return String::insert($responses['notfound'], compact('requestedCall'));

		$response .= $callsign->callsign . ' - ';
		$response .= $callsign->person->givenName . ' ' . $callsign->person->additionalName . ' ' . $callsign->person->familyName . ' - ';
		if (isset($callsign->address))
			$response .= $callsign->address->streetAddress . ' ' . $callsign->address->locality . ', ' . $callsign->address->region . ' ' . $callsign->address->postalCode . ' - ';
		else
			$response .= 'No Address Information - ';

		if (isset($callsign->uls))
			$response .= 'Class: ' . $callsign->uls->licenseClass . ' - ';

		$response .= 'http://lookup.logbook.am/call/' . $requestedCall;

		return $response;
	
	}
}

?>
