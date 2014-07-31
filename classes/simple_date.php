<?php
	/**
	 * @author Chris Zuber <shgysk8zer0@gmail.com>
	 * @copyright 2014, Chris Zuber
	 * @license http://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
	 * @package core_shared
	 * @version 2014-04-19
	 */

	class simple_date {
		public $obj, $data = [], $src, $months = [
			'January',
			'February',
			'March',
			'April',
			'May',
			'June',
			'July',
			'August',
			'September',
			'October',
			'November',
			'December'
		],
		$days = [
			'Sunday',
			'Monday',
			'Tuesday',
			'Wednesday',
			'Thursday',
			'Friday',
			'Saturday'
		];
		
		public function __construct($t = null) {
			/**
			 * Creates an associave array containing date info
			 * Keys[seconds, minutes, hours, mday, wday, mon, year, yday, weekday, month, timestamp]
			 * All values are stripped of leading '0's
			 * Seconds, minutes, are exactly what they are... no need to explain.
			 * Hours are Hours in [0-23]
			 * mday is numeric day of month [1-31]
			 * wday is numerica day of the week, starting on Sunday [0-6]
			 * mon = numeric month [1-12]
			 * year is numeric year [1-9999]
			 * yday is numeric day of the year [1-366]
			 * weekday is the textual day of the week [Sunday-Saturday]
			 * month is textual month name [January-December]
			 * timestamp is the unix timstamp and can be either positive or negative integer[+/- int]
			 * 
			 * $t can be nearly any form of communicating time including a Unix timestamp, a variety
			 * of datetime formats, date only, time only, or nothing at all
			 * 
			 * date formats include m/d/y, y-m-d, written [long form or abbreviated]
			 * time formats include 12/24 hour formats (depending on if AM/PM are included). Seconds are optional
			 * 
			 * @link http://www.php.net/manual/en/function.getdate.php
			 * @link http://www.php.net/manual/en/datetime.construct.php
			 * @param mixed $t (null, int unix_timestamp, [date][time]_format)
			 */
			(preg_match('/^\d+$/', $t)) ? $this->data = getdate($t) : $this->data = getdate(date_timestamp_get(date_create($t)));
			$this->data['timestamp'] = array_pop($this->data);
			$this->obj = $this->make();
			$this->src = $t;
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
			if($key === 'mon') {
				$this->data['month'] = $this->months[(int)$value - 1];
			}
			elseif($key === 'month') {
				$this->data['mon'] = array_search(caps($value), $this->months) +1;
			}
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
					return $this->$key;
//					if(array_key_exists($key, $this->data)) {
//						return $this->data[$key];
//					}
//					else{
//						die('Unknown variable.');
//					}
					break;
				case 'set':
					//$this->data[$key] = $arguments[0];
					$this->$key = $arguments[0];
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
		
		public function out($format = 'Y-m-d\TH:i:s') {
			/**
			 * Converts the object's timestamp into the requested format
			 * 
			 * @param string $format
			 * @return string
			 * @link http://php.net/manual/en/function.date.php
			 */
			return date($format, $this->data['timestamp']);
		}
		
		public function update() {
			/**
			 * @param void
			 * @retrun void
			 */
			$str = "{$this->year}-{$this->mon}-{$this->mday}T{$this->hours}:{$this->minutes}:{$this->seconds}";
			$this->data['timestamp'] = date_timestamp_get(date_create($str));
//			$updated = new simple_date(
		}
		
		public function make() {
			return new DateTime(date($this->out(), $this->data['timestamp']));
		}
		
		public function diff($t) {
			if(!preg_match('/\d{10}/', $t)) $t = date_timestamp_get(date_create($t));
			return $this->data['timestamp'] - $t;
		}
	}
?>
