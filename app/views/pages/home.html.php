<div id="call-search">
<?= $this->form->create( null, array( 
	'url' => array( 
		'controller' 	=> 'callsigns', 
		'action' 			=> 'profile' ), 
	'method' 	=> 'POST',
	'id' 	=> 'callsign-entry' 
)) ?>
	<h2 class="no-margin">Enter a callsign:</h2>
	<?= $this->form->text('callsign', array('id' => 'callsign-input')) ?>
	<?= $this->form->submit('Find', array('id' => 'callsign-find' )) ?>
<?= $this->form->end() ?>
	<div id="callsign-results">
		<table>
		
		</table>
	</div>
</div>
