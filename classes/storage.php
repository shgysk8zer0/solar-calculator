<?php
	class storage {
	/**
	 * Consists almost entirely of magic methods.
	 * Functionality is similar to globals, except new entries may be made
	 * and the class also has save/load methods for saving to or loading from $_SESSION
	 * Uses a private array for storage, and magic methods for getters and setters
	 *
	 * I just prefer using $session->key over $_SESSION[key]
	 * It also provides some chaining, so $session->setName(value)->setOtherName(value2)->getExisting() can be done
	 *
	 * @author Chris Zuber <shgysk8zer0@gmail.com>
	 * @copyright 2014, Chris Zuber
	 * @license http://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
	 * @package core_shared
	 * @version 2014-04-19
	 */

		private static $instance = null;
		private $data;

		public static function load() {
			/**
			 * Static load function avoids creating multiple instances/connections
			 * It checks if an instance has been created and returns that or a new instance
			 *
			 * @params void
			 * @return storage object/class
			 * @example $storage = storage::load
			 */

			if(is_null(self::$instance)) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		public function __construct() {
			/**
			 * Creates new instance of storage.
			 *
			 * @params void
			 * @return void
			 * @example $storage = new storage
			 */

			$this->data = array();
		}

		public function __set($key, $value) {
			/**
			 * Setter method for the class.
			 *
			 * @param string $key, mixed $value
			 * @return void
			 * @example "$storage->key = $value"
			 */

			$key = preg_replace('/_/', '-', preg_quote($key, '/'));
			$this->data[$key] = $value;
		}

		public function __get($key) {
			/**
			 * The getter method for the class.
			 *
			 * @param string $key
			 * @return mixed
			 * @example "$storage->key" Returns $value
			 */

			$key = preg_replace('/_/', '-', preg_quote($key, '/'));
			if(array_key_exists($key, $this->data)) {
				return $this->data[$key];
			}
			return false;
		}

		public function __isset($key) {
			/**
			 * @param string $key
			 * @return boolean
			 * @example "isset({$storage->key})"
			 */

			return array_key_exists(preg_replace('/_/', '-', $key), $this->data);
		}

		public function __unset($index) {
			/**
			 * Removes an index from the array.
			 *
			 * @param string $key
			 * @return void
			 * @example "unset($storage->key)"
			 */

			unset($this->data[preg_replace('/_/', '-', $index)]);
		}

		public function __call($name, $arguments) {
			/**
			 * Chained magic getter and setter
			 * @param string $name, array $arguments
			 * @example "$storage->[getName|setName]($value)"
			 */

			$name = strtolower($name);
			$act = substr($name, 0, 3);
			$key = preg_replace('/_/', '-', substr($name, 3));
			switch($act) {
				case 'get':
					if(array_key_exists($key, $this->data)) {
						return $this->data[$key];
					}
					else{
						die('Unknown variable.');
					}
					break;
				case 'set':
					$this->data[$key] = $arguments[0];
					return $this;
					break;
				default:
					die('Unknown method.');
			}
		}

		public function keys() {
			/**
			 * Returns an array of all array keys for $thsi->data
			 *
			 * @param void
			 * @return array
			 */

			return array_keys($this->data);
		}

		public function save() {
			/**
			 * Saves all $data to $_SESSION
			 *
			 * @param void
			 * @return void
			 */

			//[TODO] Make work with more types of data
			$_SESSION['storage'] = $this->data;
		}

		public function restore() {
			/**
			 * Loads existing $data array from $_SESSION
			 *
			 * @param void
			 * @return void
			 */

			//[TODO] Make work with more types of data
			if(array_key_exists('storage', $_SESSION)) {
				$this->data = $_SESSION['storage'];
			}
		}

		public function clear() {
			/**
			 * Destroys/clears/deletes
			 * This message will self destruct
			 *
			 * @param void
			 * @return void
			 */
			unset($this->data);
			unset($this);
		}

		public function debug() {
			/**
			 * Prints out class information using print_r
			 * wrapped in <pre> and <code>
			 *
			 * @param void
			 * @return void
			 */

			debug($this);
		}
	}
?>
