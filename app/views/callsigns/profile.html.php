<div class="main-content callProfile">
<?php if ( $callsign ) : ?>
	<div id="map_canvas"></div>	
	<a href="javascript:;" id="show-elevation-profile">Elevation Profile</a>
	<div id="elevation_profile"></div>
	<div id="use-my-location" title="Enable your location on the map">My Location</div>
	<div class="profile-content">
		<dl>
			<dt>Callsign: </dt>
				<dd><?= $callsign->callsign ?></dd>
			<dt>Name: </dt>
				<dd><?= $callsign->fullName() ?></dd>
			<dt>Full Addy: </dt>
				<dd><?= $callsign->fullAddress() ?></dd>
			<dt>License Class</dt>
				<dd><?= $callsign->uls->licenseClass ?></dd>
			<dt>GridSquare</dt>
				<dd><?= $callsign->gridSquare() ?></dd>
			<dt>Latitude: </dt>
				<dd><span id="mapLat"><?=$callsign->getLatitude()?></span></dd>	
			<dt>Longitude: </dt>
				<dd><span id="mapLng"><?=$callsign->getLongitude()?></span></dd>	
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
	<div id="json-dump">
		<?= json_encode($callsign->data()) ?>
	</div>

<?php else : ?>
	<p><?= $requestedCall ?> not found!</p>
<?php endif ?>
</div>
