<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright  Copyright 2009, Union of RAD (http://union-of-rad.org)
 * @license	   http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace app\extensions\command\bot\plugins;

use app\models\Callsigns;
use app\models\DxSpots;
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

		if ($callsigns[0] != '.c') 
			return;

		if (!isset($callsigns[1])) 
			return;

		$requestedCall = strtoupper(trim($callsigns[1]));

		// Find the callsign
		$callsign = Callsigns::first(array( 
			'conditions' => array( 
				'Callsign' => $requestedCall,
			)
		));

		// Check if there are any spots for the callsign
		$spot = DxSpots::first(array(
			'conditions' => array(
				'Callsign' => $requestedCall,
			),
			'order' => array('$natural' => -1 ),
		));
		
		// If there are spots, build the message
		$spotString = '';

		if ( count($spot) ) {
			$timeDifference = time() - $spot->time->sec;
			$spotString = 'Spotted on ' . $spot->frequency . ' ';

			if ( $timeDifference > 60 )
				$spotString .= ((integer)($timeDifference / 60)) . ' minutes ago';
			else
				$spotString .= $timeDifference . ' seconds ago';
		}

		// If there aren't any callsigns matching, let's see if we can find one similar
		if ( !count($callsign) ) {

			// Let's only do this if the call contains both a letter and number
			if (! preg_match('/[a-z]/i', $requestedCall) && ! preg_match('/[0-9]/', $requestedCall))
				return 'try a bit more, om';

			// Perform lookup w/ regex
			$callsigns = Callsigns::find('all', array(
				'fields' => array( 'Callsign' ),
				'conditions' => array(
					'Callsign' => array(
						'like' => '/^' . $requestedCall . '/'
					),
				),
			));
			
			// Determine if they're trying regex
			if ( preg_match('/[\[\?\.]/', $requestedCall )) {
				$hasRegex = true;	
			} else {
				$hasRegex = false;
				$response = String::insert($responses['notfound'], compact('requestedCall'));
			}

			// Check if we've found anything
			if ( count($callsigns) ) {

				$total = count($callsigns);

				// If there are more than 15 callsigns, get the fuck out
				if ( $total > 15 )
					return $response .= $total .' possibilities.';

				if (!$hasRegex)
					$response .= '. Could you have meant ';

				$count = 0;
				foreach ($callsigns as $call) {

					// If there's only one call found, just output it's message
					if ($total == 1)
						return $this->callMessage($call);

					// Comma between calls
					if ($count)
						$response .= ', '; 
					
					$response .= $call->Callsign;
					$count++;
				}

				if (!$hasRegex)
					$response .= '?';

				return $response;
			} else {

				// If the call isn't found but it has been spotted
				if ($spotString)
					$response .= ' but was ' . $spotString;

				return $response;
			}
		}

		// If we found the callsign just output the message
		$response .= $this->callMessage($callsign);

		if ($spotString)
			$response .= ' - ' . $spotString;

		return $response;
	}


	/**
	 * Generate the text for the callsign message
	 */

	private function callMessage( $callsign ) {

		$response .= $callsign->Callsign . ' - ';

		if ( !empty($callsign->LicenseAuthority->licenseClass) )
			$response .= 'Class: ' . $callsign->LicenseAuthority->licenseClass . ' - ';

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
		$response .= 'Lng: ' . $callsign->getLongitude();

		return $response;
	}
}

?>
