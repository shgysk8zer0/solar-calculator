<?php
	/**
	 * Custom error handling.
	 * Catch errors using custom function using set_error_handler($callback_function, ERROR_LEVEL)
	 *
	 * @link http://us3.php.net/set_error_handler
	 * @author Chris Zuber <shgysk8zer0@gmail.com>
	 * @copyright 2014, Chris Zuber
	 * @license http://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
	 * @package core_shared
	 * @version 2014-06-05
	 */

	class error_reporter extends _pdo {

		protected static $instance = null;

		private $defined_levels = [];

		public $method, $log = 'errors.log';

		public static function load($method = 'default') {
			/**
			 * Static load method should always be used,
			 * especially when in development and multiple
			 * errors may be reported.
			 */

			if(is_null(self::$instance)) {
				self::$instance = new self((string)$method);
			}
			return self::$instance;
		}

		public function __construct($method = 'default') {
			/**
			 * Custom error handling.
			 * Catch errors using custom function using set_error_handler($callback_function, ERROR_LEVEL)
			 *
			 * @link http://us3.php.net/set_error_handler
			 * @link http://us3.php.net/manual/en/errorfunc.constants.php
			 * @param int $error_level (Type of error, see E_*)
			 * @param string $error_message (a short message for the error)
			 * @param string $file (The absolute path to the file)
			 * @param int $line (Which line the error occured on)
			 * @param array $scope (An array of all variables in the scope, some of which
			 * might themselves be array or object).
			 * @ return mixed (return false to make PHP handle the error in the default way)
			 */

			$this->method = strtolower((string)$method);

			/**
			 * Get defined constants using get_defined_constants(true)
			 * Then get 'Core' section (Which includes the error constants)
			 * Next, convert the int $error_level into its definer by doing array_search,
			 * which returns the array key for the array value.
			 *
			 * This method should always work because we are looking the information
			 * that is used to define it to begin with.
			 *
			 * To define error level constants, PHP must be reading some ini file somewhere,
			 * and then loops through [Core] and doing define($key, $value) where Core is
			 * [$key => $value, ...]. What we are doing here is getting is the array used
			 * to define them, and getting back $key from $value.
			 *
			 * We could use a local array written as $value = $key and then
			 * use $error_array[$error_level], but the constants are changing
			 * (ironic, right?) between various versions of PHP, so while E_NOTICE
			 * is currently 8, there is no guarentee that this will always be the case.
			 *
			 * Do this during class construction to avoid running through
			 * multiple times.
			 *
			 * A bit of hacekry, but should avoid issues where PHP
			 * uses different values for different versions of PHP
			 */

			$this->defined_levels = get_defined_constants(true)['Core'];

			if($this->method === 'database') {
				/**
				 * construct the _pdo class and create prepared
				 * statement here. Since we will be using the static
				 * method to load this class, we will avoid
				 * both of these steps in subsequent calls.
				*/

				parent::__construct();

				$this->prepare("
					INSERT INTO `PHP_errors`
					(
						`datetime`,
						`file`,
						`line`,
						`error_message`,
						`error_type`
					)
					VALUES(
						:datetime,
						:file,
						:line,
						:error_message,
						:error_type
					)
				");
			}
			elseif($this->method === 'log') {
				/**
				 * Open the file during class construct. Same reasons as above.
				 */

				$this->log = fopen(BASE . '/' . $this->log, 'a');
			}
		}

		public function report($error_level = null, $error_message = null, $file = null, $line = null, $scope = null) {
			/**
			 * Public method to report errors. Just calls the private private
			 * method according to a switch on $this->method
			 * The default is to return false, which will handle the error
			 * with PHP's built in error reporting.
			 *
			 * @param int $error_level (Error level. Numeric value for E_*)
			 * @param string $error_message (The message provided by PHP for the error)
			 * @param string $file (absolute path for the file)
			 * @param int $line (The line on which the error occured in the file)
			 * @param mixed $scope (All variables set in the current scope when the error occured).
			 * @return boolean (false will tell PHP to handle the error by its own means)
			 */

			switch($this->method) {
				case 'database': {
					return $this->database((int)$error_level, (string)$error_message, (string)$file, (int)$line, $scope);
				} break;

				case 'log': {
					return $this->logger((int)$error_level, (string)$error_message, (string)$file, (int)$line, $scope);
				} break;

				default: {
					return false;
				}
			}
		}

		private function logger($error_level, $error_message, $file, $line, $scope) {
			/**
			 * Writes errors to the already open log file.
			 *
			 * @return boolean (Whether or not the write was successful)
			 */

			$error_level = array_search($error_level, $this->defined_levels);
			return !!fwrite($this->log, "{$error_level}: {$error_message} in {$file} on line {$line} at " . date('Y-m-d\TH:i:s') . PHP_EOL);
		}

		private function database($error_level, $error_message, $file, $line, $scope) {
			/**
			 * Binds to and executes a prepared statement, created during construct.
			 *
			 * @return boolean (Whether or not it executed)
			 */

			return $this->bind([
				'datetime' => date('Y-m-d\TH:i:s'),
				'file' => $file,
				'line' => $line,
				'error_message' => $error_message,
				'error_type' => array_search($error_level, $this->defined_levels)
			])->execute();
		}
	}
?>
