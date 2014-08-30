<?php
	class ini {
		/**
		 * Reads an ini file and stores as an object
		 *
		 * @author Chris Zuber <shgysk8zer0@gmail.com>
		 * @copyright 2014, Chris Zuber
		 * @license http://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
		 * @package core_shared
		 * @version 2014-06-01
		 *
		 * @todo: Make work when $multi evaluates as true. Will read the file,
		 * but __set, __get, __unset, __isset, and __call will be ineffective
		 */

		private static $instance = [];
		private $data = [];

		public static function load($file = null, $multi = false) {
			/**
			 * Unlike most static load methods, there is an additional
			 * advantage to using this method instead of __construct.
			 * __construct() can only handle a single file, whereas
			 * ::load() creates an array of instances with the key
			 * set to the filename.
			 *
			 * If $multi is passed and true, it will create a multi-dimensional array
			 * 	[to_level]
			 * 		key = "val"
			 * becomes $ini['top_level']['key'] = 'val'
			 *
			 * Otherwise,
			 *	 key = 'val'
			 * becomes $ini['key'] = 'val'
			 *
			 * @param string $file
			 * @param boolean $multi
			 * @usage $ini = ini::load('connect'[, false]);
			 */

			$file = (string)$file;
			if(!array_key_exists($file, self::$instance)) self::$instance[$file] = new self($file, $multi);
			return self::$instance[$file];
		}

		public function __construct($file, $multi = false) {
			/**
			 * See documentation on ::load()
			 *
			 * @param string $file
			 * @param boolean $multi
			 */

			$file = (string)$file;
			$this->data = parse_ini_file("{$file}.ini", $multi);
		}

		public function __set($key, $value) {
			/**
			 * Setter method for the class.
			 *
			 * @param string $key, mixed $value
			 * @return void
			 * @example "$ini->key = $value"
			 */

			$key = str_replace('_', '-', $key);
			$this->data[$key] = (string)$value;
		}

		public function __get($key) {
			/**
			 * The getter method for the class.
			 *
			 * @param string $key
			 * @return mixed
			 * @example "$ini->key" Returns $value
			 */

			$key = str_replace('_', '-', $key);
			if(array_key_exists($key, $this->data)) {
				return $this->data[$key];
			}
			return false;
		}

		public function __isset($key) {
			/**
			 * @param string $key
			 * @return boolean
			 * @example "isset({$ini->key})"
			 */

			return array_key_exists(str_replace('_', '-', $key), $this->data);
		}

		public function __unset($key) {
			/**
			 * Removes an index from the array.
			 *
			 * @param string $key
			 * @return void
			 * @example "unset($ini->key)"
			 */

			unset($this->data[str_replace('_', '-', $key)]);
		}

		public function __call($name, array $arguments) {
			/**
			 * Chained magic getter and setter
			 * @param string $name
			 * @param mixed $arguments
			 * @example "$ini->[getName|setName]($value)"
			 */

			$name = strtolower($name);
			$act = substr($name, 0, 3);
			$key = preg_replace('/_/', '-', substr($name, 3));
			switch($act) {
				case 'get': {
					if(array_key_exists($key, $this->data)) {
						return $this->data[$key];
					}
					else{
						return false;
					}
				} break;
				case 'set': {
					$this->data[$key] = $arguments[0];
					return $this;
				} break;
				default: {
					die('Unknown method.');
				}
			}
		}

		public function keys() {
			/**
			 * Returns an array of all array keys for $ini->data
			 *
			 * @param void
			 * @return array
			 */

			return array_keys($this->data);
		}
	}
?>
