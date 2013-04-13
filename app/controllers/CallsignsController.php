<?php

namespace app\controllers;


use app\models\Callsigns;

class CallsignsController extends \lithium\action\Controller {


	/**
	 * Index of /callsigns is of no use to us, so we redirect it back
	 * to the homepage
	 */

	public function index() {

		$this->redirect(array(
			'controller' => 'Pages', 'action' => 'view'
		));

		return;
	}

	
	/**
	 * Used to return the single callsign profile page
	 */
	
	public function profile( $requestedCall = null ) {
	

		// Always looks calls up in uppercase
		
		$requestedCall = strtoupper($requestedCall);
		

		// If this is a post request for a callsign, redirect them to the proper url
		
		if ( !empty($this->request->data['callsign']) ) {
				$this->redirect(array( 
					'controller' => 'Callsigns', 
					'action' => 'profile', 
					'args' => $this->request->data['callsign']
				));
				return;
		}


		// Find the appropriate callsign
		
		$callsign = Callsigns::first(array( 
			'conditions' => array( 
				'callsign' => $requestedCall,
			)
		));

		if (!count($callsign) )
			$isValid = Callsigns::isValid($requestedCall);

		return compact('callsign', 'requestedCall', 'isValid');
			
	}

	
	/**
	 * Used to autocomplete callsigns in the search container
	 * 
	 * Returns JSON for parsing by Javascript
	 */

	public function autocomplete( ) {


		// Grab the partial callsign from the request
		if ( array_key_exists( 'callsign', $this->request->data ) ) 
			$partialCall = strtoupper($this->request->data['callsign']);
		else
			$partialCall = '';

		
		/**
		 * Fastest expression to match the beginning of the callsigns according to:
		 *
		 * http://docs.mongodb.org/manual/reference/operator/regex/
		 */

		// Query the db

		$callsigns = Callsigns::find('all', array(
			'fields' => array( 
				'callsign', 
				'person.givenName', 
				'person.familyName', 
				'address.locality', 
				'address.region',
				'address.addressCountry',
				'qslInfo.lotwLastActive' 
			),
			'limit'	=> '20',
			'conditions' => array(
				'callsign' =>	array(
					'like' => '/^' . $partialCall . '/'),
			)) 
		);

		$this->render(array(
			'type' => 'json',
			'data' => compact('callsigns')
		));
		
	}

}

?>
