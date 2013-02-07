<div class="main-content callProfile">
<?php if ( $callsign ) : ?>
	<div id="map_canvas"></div>	
	<div class="profile-content">
		<dl>
			<dt>Callsign: </dt>
				<dd><?= $callsign->callsign ?></dd>
			<dt>Name: </dt>
				<dd><?= $callsign->fullName() ?></dd>
			<dt>Full Addy: </dt>
				<dd><?= $callsign->fullAddress() ?></dd>
			<dt>License Class</dt>
				<dd><?= $callsign->license_class ?></dd>
			<dt>Latitude: </dt>
				<dd><span id="mapLat"><?=$callsign->getLatitude()?></span></dd>	
			<dt>Longitude: </dt>
				<dd><span id="mapLng"><?=$callsign->getLongitude()?></span></dd>	
		</dl>
	</div>

<?php else : ?>
<p><?= $requestedCall ?> not found!</p>
<?php endif ?>
</div>
