<?php

namespace app\models;

use li3_geo\data\Geocoder;
use lithium\analysis\Logger;

class Callsigns extends \lithium\data\Model {

	protected $geoService = 'google';

	protected $_schema = array(
		'_id' => array('type' => 'id'),
		'Callsign' => array('type' => 'string', 'null' => false),    
		'Person' => array('type' => 'object'),
				'Person.givenName' => array('type' => 'string'),
				'Person.additionalName' => array('type' => 'string'), 
				'Person.familyName' => array('type' => 'string'), 
				'Person.email' => array('type' => 'string'),
				'Person.birthDate' => array('type' => 'date'),
				'Person.website' => array('type' => 'string'),
		'Address' => array('type' => 'object'), 
				'Address.postOfficeBoxNumber' => array('type' => 'string'),
				'Address.streetAddress' => array('type' => 'string'),
				'Address.locality' => array('type' => 'string'),
				'Address.region' => array('type' => 'string'),
				'Address.country' => array('type' => 'string'),
				'Address.postalCode' => array('type' => 'string'),
				'Address.county' => array('type' => 'string'),
		'GeoCoordinates' => array('type' => 'object'),
				'GeoCoordinates.elevation' => array('type' => 'integer', 'default' => 0),
				'GeoCoordinates.latitude' => array('type' => 'float'),
				'GeoCoordinates.longitude' => array('type' => 'float'),
				'GeoCoordinates.utcShift' => array('type' => 'float'),
				'GeoCoordinates.gridSquare' => array('type' => 'string'),
				'GeoCoordinates.continent' => array('type' => 'string'),
				'GeoCoordinates.ituZone' => array('type' => 'integer'),
				'GeoCoordinates.ituRegion' => array('type' => 'integer'),
				'GeoCoordinates.cqZone' => array('type' => 'integer'),
				'GeoCoordinates.wazZone' => array('type' => 'integer'),
				'GeoCoordinates.arrlSection' => array('type' => 'string'),
				'GeoCoordinates.iota' => array('type' => 'string'),
				'GeoCoordinates.geosource' => array('type' => 'string'),
				'GeoCoordinates.lastGeocoded' => array('type' => 'date'),
		'qslInfo' => array('type' => 'object'),
				'qslInfo.lotwLastActive' => array('type' => 'date'),
				'qslInfo.eQSL' => array('type' => 'string'),
				'qslInfo.direct' => array('type' => 'boolean'),
				'qslInfo.buro' => array('type' => 'boolean'),
				'qslInfo.manager' => array('type' => 'string'),
		'LicenseAuthority' => array('type' => 'object'),
				'LicenseAuthority.authority' => array('type' => 'string'),
				'LicenseAuthority.entityName' => array('type' => 'string'),
				'LicenseAuthority.fileNumber' => array('type' => 'integer'),
				'LicenseAuthority.frn' => array('type' => 'integer'),
				'LicenseAuthority.licenseClass' => array('type' => 'string'),
		'PersonalBio' => array('type' => 'string'),
		'Location' => array('type' => 'object'),
			'Location.lng' => array('type' => 'float'),
			'Location.lat' => array('type' => 'float'),
	);

	protected $_meta = array(
		'key' => 'Callsign',
	);

	public static function isValid( $callsign = '' ) {
		/**
		 * http://www.mail-archive.com/php-general@lists.php.net/msg180519.html
		 */

		$pattern  = "/^";
		$pattern .= "([0-9][A-Z][0-9][A-Z]{3})|"; //4N1UBG
		$pattern .= "([A-Z][0-9][A-Z]{1,3})|";    //N9URK, W1AW, W1W
		$pattern .= "([A-Z]{2}[0-9][A-Z]{1,3})"; //WB6NOA, AD4HZ, WA1W
		$pattern .= "/";


		if ( preg_match( $pattern, $callsign ) )
			return true;

		return false;

	}

	public function getFullName( $entity ) {
		if ( empty($entity->Person->givenName) ) {
			if ( !empty( $entity->LicenseAuthority->entityName ) )
				return $entity->LicenseAuthority->entityName;
			else
				return false;
		}

		return $entity->Person->givenName . ' ' . $entity->Person->additionalName . ' ' . $entity->Person->familyName;
	}
	
	public function getFullAddress( $entity ) {
		if ( !isset($entity->Address->locality) )
			return false;

		$street_Address = ( empty( $entity->Address->postOfficeBoxNumber ) ? $entity->Address->streetAddress : 'PO Box ' . $entity->Address->postOfficeBoxNumber );

		return $street_Address . ' ' . $entity->Address->locality . ' ' . $entity->Address->region;
	}

	public function getLatitude( $entity ) {
		if ( empty($entity->GeoCoordinates->latitude) )
			$entity->geocode();

		return $entity->GeoCoordinates->latitude;
	}

	public function getLongitude( $entity ) {
		if ( empty($entity->GeoCoordinates->longitude) )
			$entity->geocode();

		return $entity->GeoCoordinates->longitude;
	}

	public function getItuZone( $entity ) {
		if ( empty($entity->GeoCoordinates->ituZone) )
			$entity->dxcc();

		return $entity->GeoCoordinates->ituZone;
	}

	public function getWazZone( $entity ) {
		if ( empty($entity->GeoCoordinates->wazZone) )
			$entity->dxcc();

		return $entity->GeoCoordinates->wazZone;
	}

	public function getContinent( $entity ) {
		if ( empty($entity->GeoCoordinates->continent) )
			$entity->dxcc();

		return $entity->GeoCoordinates->continent;
	}

	public function getCountry( $entity ) {
		if ( empty($entity->Address->country) )
			$entity->dxcc();
		
		return $entity->Address->country;
	}

	public function geocode( $entity, $address = null ) {
		
		if (!$address) {
			$address = trim($entity->getFullAddress());
			
			// If there's no Address and one hasn't been specified, use DXCC
			// to find the country and geocode that
			if (!$address) {
				$entity->dxcc();	
				$address = $entity->Address->country;
			}
				
		}

		$location = Geocoder::find($this->geoService, array('address' => $address) );

		if ($location) { 
			$location = $location->coordinates();

			if ( !method_exists($entity->GeoCoordinates, 'data') )
				$entity->GeoCoordinates = new \lithium\data\entity\Document;	

			$entity->GeoCoordinates->latitude  = $location['latitude'];
			$entity->GeoCoordinates->longitude = $location['longitude'];
			$entity->GeoCoordinates->geosource = 'Google Geocoding API';
			$entity->GeoCoordinates->lastGeocoded = time();

			// Set Location for our 2D index in Mongodb
			if ( !method_exists($entity->Location, 'data') )
				$entity->Location = new \lithium\data\entity\Document;	

			$entity->Location->lat = $location['latitude'];
			$entity->Location->lng = $location['longitude'];

			$entity->save();

			// Write to the log so we know the call was geocoded
			$logMessage = $entity->Callsign . ' was geocoded via Google with the address: ' . $address;
			Logger::write('info', $logMessage); 

			return true;
		}

		// Record the failure
		$logMessage = $entity->Callsign . ' FAILED to geocode via Google with the address: ' . $address;
		Logger::write('error', $logMessage); 

		return false;

	}

	public function lotwIsActive( $entity ) {
		
		if ( empty($entity->qslInfo->lotwLastActive) )
			return 'No';

		if ( $entity->qslInfo->lotwLastActive->sec < strtotime('-1 year') )
			return 'No';
				
		return 'Yes';

	}

	public function getGridSquare( $entity, $force = false ) {

		if ( !empty($entity->GeoCoordinates->gridSquare) && !$force )
			return $entity->GeoCoordinates->gridSquare;

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

		$entity->GeoCoordinates->gridSquare = $grid;
		$entity->save();

		return $grid;	

	}

	public function dxcc( $entity ) {

		// DXCC perl script path
		$dxccPath = LITHIUM_APP_PATH . '/scripts/dxcc/dxcc ';
		$output = shell_exec( $dxccPath . $entity->Callsign );

		$output = explode("\n", $output);
		$output = array_filter($output);
		foreach( $output as $field ) {
			$row = explode(':', $field);
			$parsed[$row[0]] = trim($row[1]);
		}
		$parsed = array_filter($parsed);
		
		// Conform the output to our data schema

		foreach($parsed as $key=>$value) {
			switch($key) {
				case 'WAZ Zone':
					$conformed['wazZone'] = intval($value); 
					break;
				case 'ITU Zone':
					$conformed['ituZone'] = intval($value); 
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
					$country = $value;
					break;
			}
		}

		if (is_object($entity->Address))
			$entity->Address->country = $country;	
		else
			$entity->Address = (Object) array('country'=>$country);	
		
		// Don't overwrite lat lng from geocoding if they exist
		if (isset($entity->GeoCoordinates->latitude)) 
			unset( $conformed['latitude'] );

		if (isset($entity->GeoCoordinates->longitude)) 
			unset( $conformed['longitude'] );


		if (!method_exists($entity->GeoCoordinates, 'data')) {
			$entity->GeoCoordinates = (Object) $conformed;
			$entity->save();
		} else {
			$current = $entity->GeoCoordinates->data();
			$merged = array_merge($current, $conformed);

			foreach($merged as $key=>$value) {
				$entity->GeoCoordinates[$key] = $value;
			}
		}

		$entity->save();

	}

}

?>
