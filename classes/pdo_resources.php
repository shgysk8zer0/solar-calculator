<?php
	/**
	 * Wrapper for standard PDO class.
	 *
	 * This class is meant only to be extended and
	 * not used directly. It offers only a protected
	 * __construct method and a public escape.
	 *
	 * @author Chris Zuber <shgysk8zer0@gmail.com>
	 * @copyright 2014, Chris Zuber
	 * @license http://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
	 * @package core_shared
	 * @version 2014-08-27
	*/

	class pdo_resources {
		public $connected;

		protected function __construct($ini = 'connect') {
			/**
			 * @method __construct
			 * @desc
			 * Gets database connection info from /connect.ini (using ini::load)
			 * The default ini file to use is connect, but can be passed another
			 * in the $ini argument.
			 *
			 * Uses that data to create a new PHP Data Object
			 *
			 * @param string $ini (.ini file to use for database credentials)
			 * @return void
			 * @example parent::__construct($ini)
			 */

			$this->pdo = (is_string($ini)) ? pdo_connect::load($ini) : new pdo_connect($ini);
			$this->connected = $this->pdo->connected;
		}

		public function escape(&$val) {
			/**
			 * For lack of a pdo escape, use quote, trimming off the quotations
			 *
			 * @param mixed $str
			 * @return mixed
			 */

			$val = preg_replace('/^\'|\'$/', null, $this->pdo->quote($val));
			return $val;
		}

		public function quote(&$str) {
			$str = $this->pdo->quote((string)$str);
			return $str;
		}

		public function restore($fname = null) {
			return $this->pdo->restore($fname);
		}

		public function dump($filename = null) {
			return $this->pdo->dump($filename);
		}
	}
?>
