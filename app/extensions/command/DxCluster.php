<?php

namespace app\extensions\command;

use app\extensions\command\dxcluster\Telnet;

/**
 * A set of commands to start and control the Lithium Bot.
 */
class DxCluster extends \lithium\console\Command {

	/**
	 * The main method of the command.
	 *
	 * @return void
	 */
	public function run() {
		return $this->telnet();
	}

	/**
	 * Starts the telnet DxCluster connection
	 *
	 * @return boolean
	 */
	public function telnet() {
		$command = new Telnet(array('request' => $this->request));
		return $command->run();
	}
}

?>
