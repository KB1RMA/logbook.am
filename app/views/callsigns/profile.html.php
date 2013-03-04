<?php $this->title($callsign->callsign . ' - Logbook.am') ?>
<div class="main-content callProfile">
<?php if ( $callsign ) : ?>
	<div id="map_canvas"></div>	
	<a href="javascript:;" id="show-elevation-profile">Elevation Profile</a>
	<div id="elevation_profile"></div>
	<div id="use-my-location" title="Enable your location on the map">My Location</div>
	<div class="profile-content clearfix">
		<h1 class="callsign"><?= $callsign->callsign ?></h1>
		<div class="postage-stamp right" itemscope itemtype="http://schema.org/Person">
			<h2 class="grey-title">Postal Address</h2>
		<?php if ( isset($callsign->person) ) : ?>
			<strong><span itemprop="givenName"><?= $callsign->person->givenName ?></span
			>&nbsp;<span itemprop="additionalName"><?= $callsign->person->additionalName ?></span
			>&nbsp;<span itemprop="familyName"><?= $callsign->person->familyName ?></span></strong>
		<?php endif ?>
			<div class="address" itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
			<?php if ( $callsign->getFullAddress() ) : ?>
				<?php if ( !empty($callsign->address->postOfficeBoxNumber) ) : ?>
					P.O. Box&nbsp;<span itemprop="postOfficeBoxNumber"><?= $callsign->address->postOfficeBoxNumber ?></span><br>
				<?php else : ?>
					<span itemprop="streetAddress"><?= $callsign->address->streetAddress ?></span><br>
				<?php endif ?>
					<span itemprop="addressLocality"><?= $callsign->address->locality ?></span
					>,&nbsp;<span itemprop="addressRegion"><?= $callsign->address->region ?></span
					>&nbsp;<span itemprop="addressPostalCode"><?= $callsign->address->postalCode ?></span>
			<?php else : ?>
				<p class="no-info"><em>No address information available</em></p>
			<?php endif ?>
			</div>	
		</div>
		<div class="general-info">
			<h2 class="grey-title">General Information</h2>
			<dl>
				<?php if (isset($callsign->uls)) : ?>
				<dt>License Class</dt>
					<dd><?= $callsign->uls->licenseClass ?></dd>
				<?php endif ?>
				<dt>GridSquare</dt>
					<dd><?= $callsign->getGridSquare() ?></dd>
				<dt>Latitude: </dt>
					<dd><span id="mapLat"><?=$callsign->getLatitude()?></span></dd>	
				<dt>Longitude: </dt>
					<dd><span id="mapLng"><?=$callsign->getLongitude()?></span></dd>	
				<dt>ITU Zone: </dt>
					<dd><?=$callsign->getItuZone()?></span></dd>	
				<dt>WAZ Zone: </dt>
					<dd><?=$callsign->getWazZone()?></span></dd>	
				<dt>Continent: </dt>
					<dd><?=$callsign->getContinent()?></span></dd>	
				<dt>Country: </dt>
					<dd><?=$callsign->getCountry()?></span></dd>	
				<dt>LOTW Active?</dt>
					<dd><?= $callsign->lotwIsActive() ?></dd>	
				<?php if (!empty($callsign->qslInfo->lotwLastActive)) :?>
				<dt>Last Upload to LOTW</dt>
					<dd>
						<?= date('M d, Y', $callsign->qslInfo->lotwLastActive->sec) ?>
					</dd>
				<?php endif ?>
			</dl>
		</div>
		<div class="postage-stamp right">
			<h2 class="grey-title">Spots</h2>
			<div id="dx-cluster-spots" class="monospace" data-callsign="<?= $callsign->callsign ?>">
				<p class="no-info"><em>Loading spots...</em></p>
			</div>
		</div>
	</div>
	<div id="json-dump" class="hidden">
		<?= json_encode($callsign->data()) ?>
	</div>

<?php else : ?>
	<p><?= $requestedCall ?> not found!</p>
<?php endif ?>
</div>
