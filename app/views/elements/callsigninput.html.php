<div id="call-search">
<?= $this->form->create( null, array( 
	'url' => array( 
		'controller' 	=> 'callsigns', 
		'action' 			=> 'profile' ), 
	'method' 	=> 'POST',
	'id' 	=> 'callsign-entry' 
)) ?>
	<?= $this->form->text('callsign', array('id' => 'callsign-input', 'autocomplete' => 'off')) ?>
<?= $this->form->end() ?>
	<div id="callsign-results">
		<table></table>
	</div>
</div>
