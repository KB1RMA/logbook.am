<?php $this->title($requestedCall. ' - Logbook.am') ?>
<div class="row main-content callProfile">
<?php if ( $callsign ) : ?>

	<div id="map_canvas"></div>	
	<a href="javascript:;" id="show-elevation-profile">Elevation Profile</a>
	<div id="elevation_profile"></div>
	<div id="use-my-location" title="Enable your location on the map">My Location</div>

	<div class="row profile-content clearfix">
		<div class="six columns">	
			<h1 class="callsign"><?= $callsign->Callsign ?></h1>
			<div class="general-info">
				<h2 class="grey-title">General Information</h2>
				<table>
					<?php if ( !empty($callsign->LicenseAuthority->licenseClass) ) : ?>
					<tr>
						<th>License Class</th><td><?= $callsign->LicenseAuthority->licenseClass ?></td>
					</tr>
					<?php endif ?>
					<tr>
						<th>GridSquare</th><td><?= $callsign->getGridSquare() ?></td>
					</tr><tr>
						<th>Latitude: </th><td><span id="mapLat"><?=$callsign->getLatitude()?></span></td>	
					</tr><tr>
						<th>Longitude: </th><td><span id="mapLng"><?=$callsign->getLongitude()?></span></td>	
					</tr><tr>
						<th>County: </th><td><?=$callsign->Address->county?></td>	
					</tr><tr>
						<th>ITU Zone: </th><td><?=$callsign->getItuZone()?></span></td>	
					</tr><tr>
						<th>WAZ Zone: </th><td><?=$callsign->getWazZone()?></span></td>	
					</tr><tr>
						<th>Continent: </th><td><?=$callsign->getContinent()?></span></td>	
					</tr><tr>
						<th>Country: </th><td><?=$callsign->getCountry()?></span></td>	
					</tr><tr>
						<th>LOTW Active?</th><td><?= $callsign->lotwIsActive() ?></td>	
					</tr>
					<?php if (!empty($callsign->qslInfo->lotwLastActive)) :?>
					<tr>
						<th>Last Upload to LOTW</th><td> <?= date('M d, Y', $callsign->qslInfo->lotwLastActive->sec) ?> </td>
					</tr>
					<?php endif ?>
				</table>
			</div>
		</div>

		<div class="six columns">	

			<div class="panel postage-stamp" itemscope itemtype="http://schema.org/Person">
				<h2 class="grey-title">Postal Address</h2>
			<?php if ( !empty($callsign->Person->givenName) ) : ?>
				<strong><span itemprop="givenName"><?= $callsign->Person->givenName ?></span
				>&nbsp;<span itemprop="additionalName"><?= $callsign->Person->additionalName ?></span
				>&nbsp;<span itemprop="familyName"><?= $callsign->Person->familyName ?></span></strong>
			<?php else : ?>
				<strong><?= $callsign->getFullName() ?></strong>
			<?php endif ?>
				<div class="Address" itemprop="Address" itemscope itemtype="http://schema.org/PostalAddress">
				<?php if ( $callsign->getFullAddress() ) : ?>
					<?php if ( !empty($callsign->Address->postOfficeBoxNumber) ) : ?>
						P.O. Box&nbsp;<span itemprop="postOfficeBoxNumber"><?= $callsign->Address->postOfficeBoxNumber ?></span><br>
					<?php else : ?>
						<span itemprop="streetAddress"><?= $callsign->Address->streetAddress ?></span><br>
					<?php endif ?>
						<span itemprop="AddressLocality"><?= $callsign->Address->locality ?></span
						>,&nbsp;<span itemprop="AddressRegion"><?= $callsign->Address->region ?></span
						>&nbsp;<span itemprop="AddressPostalCode"><?= $callsign->Address->postalCode ?></span>
				<?php else : ?>
					<p class="no-info"><em>No Address information available</em></p>
				<?php endif ?>
				</div>	
			</div>
			<div class="panel postage-stamp">
				<h2 class="grey-title">Spots</h2>
				<div id="dx-cluster-spots" class="monospace" data-callsign="<?= $callsign->Callsign ?>">
					<p class="no-info"><em>Loading spots...</em></p>
				</div>
			</div>
		</div>


	</div>

	<div id="json-dump" class="hidden">
		<?= json_encode($callsign->data()) ?>
	</div>
<?php else : ?>
	<?php if ( $isValid ) :?>
	<div class="profile-content clearfix">
		<p><?= $requestedCall ?> looks like a valid callsign, but we don't have any info</p>
		<h2 class="grey-title">Spots</h2>
		<div id="dx-cluster-spots" class="monospace" data-callsign="<?= $requestedCall?>" data-limit="10">
			<p class="no-info"><em>Loading spots...</em></p>
		</div>
	</div>
	<?php else : ?>
	<p><?= $requestedCall ?> doesn't look like a valid callsign!</p>
	<?php endif ?>
<?php endif ?>
</div>

<?php $this->scripts( $this->html->script('callprofile.js') ) ?>
