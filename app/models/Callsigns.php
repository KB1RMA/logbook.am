<?php

namespace app\models;

class Callsigns extends \lithium\data\Model {

	protected $_schema = array(
		'_id' => array('type' => 'id'),
		'callsign' => array('type' => 'string', 'null' => false),    
    'person' => array('type' => 'object'),
        'person.givenName' => array('type' => 'string'),
        'person.additionalName' => array('type' => 'string'), 
        'person.familyName' => array('type' => 'string'), 
        'person.email' => array('type' => 'string'),
        'person.birthDate' => array('type' => 'date'),
        'person.website' => array('type' => 'string'),
    'address' => array('type' => 'object'), 
        'address.postOfficeBoxNumber' => array('type' => 'string'),
        'address.streetAddress' => array('type' => 'string'),
        'address.locality' => array('type' => 'string'),
        'address.region' => array('type' => 'string'),
        'address.country' => array('type' => 'string'),
        'address.postalCode' => array('type' => 'string'),
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
		if ( empty($entity->person) )
			return false;

		return $entity->person->givenName . ' ' . $entity->person->additionalName . ' ' . $entity->person->familyName;
	}
	
	public function fullAddress( $entity ) {
		if ( empty($entity->address) )
			return false;

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

	public function gridSquare( $entity, $force = false ) {

		if ( !empty($entity->geoCoordinates->gridSquare) && !$force )
			return $entity->geoCoordinates->gridSquare;

		$grid = '';
		$lat = $entity->getLatitude();
		$lon = $entity->getLongitude();

		$lat  = ($lat + 90);
		$lon  = ($lon + 180);

		$grid .= chr(ord('A') + intval($lon / 20));
		$grid .= chr(ord('A') + intval($lat / 10));
		$grid .= chr(ord('0') + intval(($lon % 20)/2));
		$grid .= chr(ord('0') + intval(($lat % 10)/1));
		$grid .= chr(ord('a') + intval(($lon - (intval($lon/2)*2)) / (5/60)));
		$grid .= chr(ord('a') + intval(($lat - (intval($lat/1)*1)) / (2.5/60)));

		$entity->geoCoordinates->gridSquare = $grid;
		$entity->save();

		return $grid;	

	}

}

?>
