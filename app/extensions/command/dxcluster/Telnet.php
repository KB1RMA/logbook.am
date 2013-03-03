<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2009, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace app\extensions\command\dxcluster;

use lithium\core\Libraries;
use app\models\DxSpots;

class Telnet extends \lithium\console\Command {

	public $socket = null;

	protected $_running = false;
	protected $_resource = null;
	protected $_call = 'KB1RMA';

	protected $_classes = array(
		'socket' => 'lithium\net\socket\Stream',
		'response' => 'lithium\console\Response',
		'spot' => '',
	);

	public function _init() {
		parent::_init();

		$plugin = dirname(dirname(dirname(__DIR__)));
		$this->_config += parse_ini_file($plugin . '/config/li3_dxcluster.ini');
		foreach ($this->_config as $key => $value) {
			$key = "_{$key}";
			if (isset($this->{$key}) && $key !== '_classes') {
				$this->{$key} = $value;
				if ($value && strpos($value, ',') !== false) {
					$this->{$key} = array_map('trim', (array) explode(',', $value));
				}
			}
		}
		$this->socket = $this->_instance('socket', $this->_config);
	}

	public function run() {
		try {
			$this->_running = (boolean) $this->socket->open();
			$this->_resource = $this->socket->resource();
		} catch (Exception $e) {
			$this->out($e);
		}

		if ($this->_running) {
			$this->out('connected');
			$this->_connect();
		}
		while ($this->_running && !$this->socket->eof()) {
			$this->_process(fgets($this->_resource));
		}
	}

	protected function _connect() {
		$this->out("Logging in with " . $this->_call);
		fwrite($this->_resource, $this->_call . "\r\n");
	}

	protected function _process($line) {
		if ( preg_match('/^DX/', $line) ) {
			$this->out(print_r($this->_parseSpot( $line)));
		}
	}

	protected function _parseSpot( $line ) {
		$spot['by'] = substr($line, 6, 10);
		$spot['frequency'] = (float)substr($line, 17, 9);
		$spot['callsign'] = substr($line, 26, 12);
		$spot['comment'] = substr($line, 39, 31);
		// Want higher time precision than provided by the cluster, so use server time
		$spot['time'] = time();

		// Trim whitespace from all array elements
		$spot = array_map('trim', $spot);
		
		if (DxSpots::create($spot)->save())
			$this->out('Saved');

		return $spot;

	}

}

?>
