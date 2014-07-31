<?php
	class regexp{
		/**
		 * Makes easy use of simple regular expressions
		 *
		 * @author Chris Zuber <shgysk8zer0@gmail.com>
		 * @copyright 2014, Chris Zuber
		 * @license http://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
		 * @package core_shared
		 * @version 2014-04-19
		 * @example:
		 * 		$reg = new regexp([$string]);
		 * 		$some_var = $reg->replace('foo')->with('bar')[->in($string)]->execute()
		 */

		protected $pattern, $replacement, $limit = -1, $find;
		public $in, $result;

		public function __construct($str = false) {
			/**
			 * Gets database connection info from /connect.ini (stored in $site)
			 * Uses that data to create a new PHP Data Object
			 *
			 * @param [string $str]
			 * @return void
			 * @example $reg = new regexp([$string])
			 */

			$this->pattern = array();
			$this->replacement = array();
			if($str) $this->in = $str;
		}

		public function __isset($name) {
			/**
			 * @param string $key
			 * @return boolean
			 * @example "isset({$reg->key})"
			 */

			return isset($this->$name);
		}

		public function set_pattern($type) {
			/**
			 * Set pattern according to presets
			 *
			 * @param string $type
			 * @return self
			 * @uses functions.php::pattern()
			 * @example "$reg->set_pattern('number')" sets $this->patttern to "\d+(\.\d{1,})?"
			 */

			$this->pattern = pattern($type);
			return $this;
		}

		public function replace($str) {
			/**
			 * Adds a new pattern to $pattern[]
			 *
			 * @param string $str
			 * @return self
			 */

			array_push($this->pattern, $this->regexp($str));
			return $this;
		}

		public function with($str) {
			/**
			 * Adds a new replacement to $replacement
			 *
			 * @param strign $str
			 * @return self
			 */

			array_push($this->replacement, $str);
			return $this;
		}

		public function ends_with($str) {
			/**
			 * RegExp at end of string
			 *
			 * @param string $str
			 * @return boolean
			 */

			$this->find = $this->regexp($str, 'end');
			return $this->test();
		}

		public function begins_with($str) {
			/**
			 * RegExp at beginning of string
			 *
			 * @param string $str
			 * @return boolean
			 */

			$this->find = $this->regexp($str, 'begin');
			return $this->test();
		}

		public function is($str) {
			/**
			 * RegExp of the full string. Begin and end
			 *
			 * @param string $str
			 * @return boolean
			 */

			$this->find = $this->regexp($str, 'full');
			return $this->test();
		}

		public function has($str) {
			/**
			 * Location agnostic RegExp
			 *
			 * @param string $str
			 * @return boolean
			 */

			$this->find = $this->regexp($str, null);
			return $this->test();
		}

		public function regexp($str, $loc = null) {
			/**
			 * Creates the RegExp format '/[^]pattern[$]/', replacing dangerous characters
			 *
			 * @param string $str[, string $loc]
			 * @return string (regular expression)
			 */

			$pattern = preg_quote($str, '/');
			switch($loc) {
				case 'begin':
				case '^':
					$pattern = "/^$pattern/";
					break;
				case 'end':
				case '$':
					$pattern = "/$pattern$/";
					break;
				case 'full':
				case '=':
					$pattern = "/^$pattern$/";
					break;
				default:
					$pattern = "/$pattern/";
			}
			return $pattern;
		}

		public function test() {
			/**
			 * Returns boolean result of a RegExp search
			 *
			 * @param void
			 * @return boolean
			 */

			return preg_match($this->find, $this->in);
		}

		public function find($str, $loc = null) {
			/**
			 * In the case of finding a needle in a haystack, this sis the needle
			 *
			 * @param string $str[, string $loc]
			 * @return self
			 */

			$this->find = $this->regexp($str, $full);
			return $this;
		}

		public function in($str) {
			/**
			 * In the case of finding a needle in a haystack, this sis the needle
			 * @param string $str
			 * @return self
			 */

			$this->in = $str;
			return $this;
		}

		public function limit($n) {
			/**
			 * Optional limit to replacements. Defaults to unlimited
			 *
			 * @param int $n
			 * @return self
			 */

			$this->limit = $n;
			return $this;
		}

		public function execute($update = true) {
			/**
			 * Runs the RegExp replacement, modifies and returns the string
			 *
			 * @param void
			 * @return string
			 */

			/*$this->in = preg_replace($this->pattern, $this->replacement, $this->in, $this->limit);
			return $this->in;*/
			if($update) {
				$this->in = preg_replace($this->pattern, $this->replacement, $this->in, $this->limit);
				return $this->in;
			}
			else return preg_replace($this->pattern, $this->replacement, $this->in, $this->limit);
		}

		public function value() {
			/**
			 * @depreciated ?
			 * @param void
			 * @return string
			 */

			return $this->in;
		}

		public function matches_pattern($type = null) {
			/**
			 * Returns input patterns for html inputs
			 *
			 * @param string $type
			 * @return boolean
			 */

			$pattern = pattern($type);
			$this->find = "/^$pattern$/";
			return $this->test();
		}

		public function debug() {
			debug($this);
		}
	}
?>
