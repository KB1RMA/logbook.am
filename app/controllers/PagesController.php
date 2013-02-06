<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2013, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace app\controllers;

class PagesController extends \lithium\action\Controller {

	public function view() {

		$options = array();
		$path = func_get_args();

		if (!$path || $path === array('home')) {

			$path = array('home');
			$options['compiler'] = array('fallback' => true);

			// Set a flag for home - Used for the top search/user-info bar
			$this->set(array('isHome' => true));		
		}

		$options['template'] = join('/', $path);
		return $this->render($options);
	}

}

?>
