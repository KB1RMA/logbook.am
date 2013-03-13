<div class="row main-content">
	<div class="profile-content clearfix">
		<h1 class="callsign"><?= $callsign->callsign ?></h1>
		<table style="width:90%;margin:0 auto">
			<thead>
				<tr>
					<th>Frequency</th>
					<th>Comment</th>
					<th>Time</th>
					<th>By</th>
				</tr>
			</thead>
			<tbody class="monospace">
				<? foreach ($spots as $spot) : ?>
				<tr>
					<td><?= $spot->frequency ?></td>
					<td><?= $spot->comment ?></td>
					<td><?= $spot->time->sec ?></td>
					<td><?= $spot->by ?></td>
				</tr>
				<? endforeach ?>
			</tbody>
		</table>
	</div>
</div>
