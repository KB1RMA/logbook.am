<div class="main-content">
<table style="width:90%;margin:0 auto">
	<thead>
		<tr>
			<th>Callsign</th>
			<th>Frequency</th>
			<th>Comment</th>
			<th>Time</th>
			<th>By</th>
		</tr>
	</thead>
	<tbody>
		<? foreach ($spots as $spot) : ?>
		<tr>
			<td><?= $spot->callsign ?></td>
			<td><?= $spot->frequency ?></td>
			<td><?= $spot->comment ?></td>
			<td><?= $spot->time->sec ?></td>
			<td><?= $spot->by ?></td>
		</tr>
		<? endforeach ?>
	</tbody>
</table>
</div>
