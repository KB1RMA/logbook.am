<?php

namespace app\models;

class Callsigns extends \lithium\data\Model {

	protected $_schema = array(
		'_id' => array('type' => 'id'),
		'callsign' => array('type' => 'string', 'null' => false),    
    'person' => array('type' => 'object'),
        'person.givenName' => array('type' => 'string', 'default' => 'test'),
        'person.additionalName' => array('type' => 'string', 'default' => 'test'), 
        'person.familyName' => array('type' => 'string'), 
        'person.email' => array('type' => 'string'),
        'person.birthDate' => array('type' => 'date'),
        'person.website' => array('type' => 'string'),
    'address' => array('type' => 'object'), 
        'address.postOfficeBoxNumber' => array('type' => 'string'),
        'address.streetAddress' => array('type' => 'string'),
        'address.locality' => array('type' => 'string', 'default' => ''),
        'address.region' => array('type' => 'string', 'default' => ''),
        'address.country' => array('type' => 'string'),
        'address.postalCode' => array('type' => 'integer'),
    'geoCoordinates' => array('type' => 'object'),
        'geoCoordinates.elevation' => array('type' => 'integer', 'default' => 0),
        'geoCoordinates.latitude' => array('type' => 'float'),
        'geoCoordinates.longitude' => array('type' => 'float'),
        'geoCoordinates.gridSquare' => array('type' => 'string'),
        'geoCoordinates.ituZone' => array('type' => 'integer'),
        'geoCoordinates.cqZone' => array('type' => 'integer'),
        'geoCoordinates.arrlSection' => array('type' => 'integer'),
        'geoCoordinates.iota' => array('type' => 'integer'),
        'geoCoordinates.source' => array('type' => 'string'),
    'qslInfo' => array('type' => 'object'),
        'qslInfo.lotwLastActive' => array('type' => 'date'),
        'qslInfo.eQSL' => array('type' => 'string'),
        'qslInfo.direct' => array('type' => 'boolean'),
        'qslInfo.buro' => array('type' => 'boolean'),
        'qslInfo.manager' => array('type' => 'boolean'),
    'uls' => array('type' => 'object'),
        'uls.fileNumber' => array('type' => 'integer'),
        'uls.frn' => array('type' => 'integer'),
        'uls.licenseClass' => array('type' => 'string'),
    'personalBio' => array('type' => 'string'),
	);

	public function fullName( $entity ) {
		return $entity->person->givenName . ' ' . $entity->person->additionalName . ' ' . $entity->person->familyName;
	}
	
	public function fullAddress( $entity ) {

		$street_address = ( empty( $entity->address->postOfficeBoxNumber ) ? $entity->address->streetAddress : 'PO Box ' . $entity->address->PostOfficeBoxNumber );

		return $street_address . ' ' . $entity->address->locality . ' ' . $entity->address->region;
	}

	public function getLatitude( $entity ) {
		if ( empty($entity->geoCoordinates->latitude) )
			$entity->geocode();

		return $entity->geoCoordinates->latitude;
	}

	public function getLongitude( $entity ) {
		if ( empty($entity->geoCoordinates->longitude) )
			$entity->geocode();

		return $entity->geoCoordinates->longitude;
	}

	public function geocode( $entity ) {
		
		$full_address = trim($entity->fullAddress());

		if ( !empty($full_address) ) {	
			$geocoder = new \Geocoder\Geocoder();
			$adapter  = new \Geocoder\HttpAdapter\CurlHttpAdapter();
		
			$geocode = $geocoder
										->registerProvider(new \Geocoder\Provider\GoogleMapsProvider($adapter))
										->geocode($entity->fullAddress());

			$entity->geoCoordinates = new \stdClass();	
			$entity->geoCoordinates->latitude  = $geocode->getLatitude();
			$entity->geoCoordinates->longitude = $geocode->getLongitude();
			$entity->geoCoordinates->source    = 'Google Geocoding API';

			$entity->save();
		}
	}

	public function lotwIsActive( $entity ) {
		
		if ( empty($entity->qslInfo->lotwLastActive) )
			return 'No';

		if ( $entity->qslInfo->lotwLastActive->sec < strtotime('-1 year') )
			return 'No';
				
		return 'Yes';

	}

}

?>
