<?php

namespace app\models;

class Callsigns extends \lithium\data\Model {

	protected $_schema = array(
		'_id' => array('type' => 'id'),
		'lotw_last_active' => array('type' => 'date')
	);

	public function fullName( $entity ) {
		return $entity->first_name . ' ' . $entity->mi . ' ' . $entity->last_name;	
	}
	
	public function fullAddress( $entity ) {

		$street_address = ( empty( $entity->po_box ) ? $entity->street_address : 'PO Box ' . $entity->po_box );

		return $street_address . ' ' . $entity->city . ' ' . $entity->state;
	}

	public function getLatitude( $entity ) {
		if ( empty($entity->latitude) )
			$entity->geocode();

		return $entity->latitude;
	}

	public function getLongitude( $entity ) {
		if ( empty($entity->longitude) )
			$entity->geocode();

		return $entity->longitude;
	}

	public function geocode( $entity ) {
		
		$full_address = trim($entity->fullAddress());

		if ( !empty($full_address) ) {	
			$geocoder = new \Geocoder\Geocoder();
			$adapter  = new \Geocoder\HttpAdapter\CurlHttpAdapter();
		
			$geocode = $geocoder
										->registerProvider(new \Geocoder\Provider\GoogleMapsProvider($adapter))
										->geocode($entity->fullAddress());
			
			$entity->latitude  = $geocode->getLatitude();
			$entity->longitude = $geocode->getLongitude();
			
			$entity->save();
		}
	}

	public function lotwIsActive( $entity ) {
		
		if ( empty($entity->lotw_last_active) )
			return 'No';

		if ( $entity->lotw_last_active->sec < strtotime('-1 year') )
			return 'No';
				
		return 'Yes';

	}

}

?>
