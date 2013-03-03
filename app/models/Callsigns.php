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
				'geoCoordinates.utcShift' => array('type' => 'float'),
				'geoCoordinates.gridSquare' => array('type' => 'string'),
				'geoCoordinates.continent' => array('type' => 'string'),
				'geoCoordinates.ituZone' => array('type' => 'integer'),
				'geoCoordinates.ituRegion' => array('type' => 'integer'),
				'geoCoordinates.cqZone' => array('type' => 'integer'),
				'geoCoordinates.arrlSection' => array('type' => 'string'),
				'geoCoordinates.iota' => array('type' => 'string'),
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

	public function getFullName( $entity ) {
		if ( empty($entity->person) )
			return false;

		return $entity->person->givenName . ' ' . $entity->person->additionalName . ' ' . $entity->person->familyName;
	}
	
	public function getFullAddress( $entity ) {
		if ( !isset($entity->address->locality) )
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

	public function getItuZone( $entity ) {
		if ( empty($entity->geoCoordinates->ituZone) )
			$entity->dxcc();

		return $entity->geoCoordinates->ituZone;
	}

	public function getWazZone( $entity ) {
		if ( empty($entity->geoCoordinates->wazZone) )
			$entity->dxcc();

		return $entity->geoCoordinates->wazZone;
	}

	public function getContinent( $entity ) {
		if ( empty($entity->geoCoordinates->continent) )
			$entity->dxcc();

		return $entity->geoCoordinates->continent;
	}

	public function getCountry( $entity ) {
		if ( empty($entity->address->addressCountry) )
			$entity->dxcc();
		
		return $entity->address->addressCountry;
	}

	public function geocode( $entity, $address = null ) {
		
		if (!$address) {
			$address = trim($entity->getFullAddress());
			
			// If there's no address and one hasn't been specified, use DXCC
			// to find the country and geocode that
			if (!$address) {
				$entity->dxcc();	
				$address = $entity->address->addressCountry;
			}
				
		}

		$geocoder = new \Geocoder\Geocoder();
		$adapter  = new \Geocoder\HttpAdapter\CurlHttpAdapter();
	
		try {
			$geocode = $geocoder
			             ->registerProvider(new \Geocoder\Provider\GoogleMapsProvider($adapter))
			             ->geocode($address);

			$entity->geoCoordinates = new \stdClass();	
			$entity->geoCoordinates->latitude  = $geocode->getLatitude();
			$entity->geoCoordinates->longitude = $geocode->getLongitude();
			$entity->geoCoordinates->source    = 'Google Geocoding API';

			$entity->save();
		} catch (Exception $e) {
			// suppress geocoder errors for now
		}

		return;

	}

	public function lotwIsActive( $entity ) {
		
		if ( empty($entity->qslInfo->lotwLastActive) )
			return 'No';

		if ( $entity->qslInfo->lotwLastActive->sec < strtotime('-1 year') )
			return 'No';
				
		return 'Yes';

	}

	public function getGridSquare( $entity, $force = false ) {

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
		if ($entity->getFullAddress()) {
			$grid .= chr(ord('a') + intval(($lon - (intval($lon/2)*2)) / (5/60)));
			$grid .= chr(ord('a') + intval(($lat - (intval($lat/1)*1)) / (2.5/60)));
		}

		$entity->geoCoordinates->gridSquare = $grid;
		$entity->save();

		return $grid;	

	}

	public function dxcc( $entity ) {

		// DXCC perl script path
		$dxccPath = LITHIUM_APP_PATH . '/scripts/dxcc/dxcc ';

		$output = shell_exec( $dxccPath . $entity->callsign );

		$output = explode("\n", $output);
		$output = array_filter($output);
		foreach( $output as $field ) {
			$row = explode(':', $field);
			$parsed[$row[0]] = trim($row[1]);
		}
		$parsed = array_filter($parsed);
		
		// Conform the output to our data schema
		$conformed = array('source' => 'DXCC.pl');

		foreach($parsed as $key=>$value) {
			switch($key) {
				case 'WAZ Zone':
					$conformed['wazZone'] = $value; 
					break;
				case 'ITU Zone':
					$conformed['ituZone'] = $value; 
					break;
				case 'Continent':
					$conformed['continent'] = $value; 
					break;
				case 'Latitude':
					$conformed['latitude'] = $value; 
					break;
				case 'Longitude': 
					$conformed['longitude'] = $value; 
					break;
				case 'Country Name':
					if (!$entity->getFullAddress())
						$entity->address = (Object) array( 'addressCountry' => $value );
					else
						$entity->address['addressCountry'] = $value;
					break;
			}
		}
		
		// Grab current Geo Data from model	

		if (isset($entity->geoCoordinates->latitude))
			$GeoCoordinates = (array)$entity->geoCoordinates;
		else
			$GeoCoordinates = array();

		// Merge new data from DXCC output with the current model's data
		$entity->geoCoordinates = (Object)array_merge( $conformed, $GeoCoordinates );

		$entity->save();

	}

}

?>
