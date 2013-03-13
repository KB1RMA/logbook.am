<?php $this->title("DX Spot Live Statistics") ?>
<div class="row main-content">
	<h2 class="grey-title">Latest Stats</h2>
	<div id="all-band-stats" style="width:100%;height:200px"></div>
	<h2 class="grey-title">Last 50 Spots</h2>
	<table style="width:90%;margin:0 auto">
		<thead>
			<tr>
				<th>Callsign</th>
				<th>Band</th>
				<th>Frequency</th>
				<th>Comment</th>
				<th>Time</th>
				<th>By</th>
			</tr>
		</thead>
		<tbody class="monospace">
			<? foreach ($spots as $spot) : ?>
			<tr>
				<td><?= $spot->callsign ?></td>
				<td><?= $spot->band ?></td>
				<td><?= $spot->frequency ?></td>
				<td><?= $spot->comment ?></td>
				<td><?= $spot->time->sec ?></td>
				<td><?= $spot->by ?></td>
			</tr>
			<? endforeach ?>
		</tbody>
	</table>
</div>

<?php $this->scripts( $this->html->script('vendor/jquery.flot.js') ) ?>
<?php $this->scripts( $this->html->script('statplotting.js') ) ?>
