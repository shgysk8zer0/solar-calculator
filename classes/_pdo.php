<?php
	/**
	 * Wrapper for standard PDO class. Allows
	 * standard MySQL to be used, while giving benefits
	 * of chained prepare->bind->execute...
	 *
	 * @author Chris Zuber <shgysk8zer0@gmail.com>
	 * @copyright 2014, Chris Zuber
	 * @license http://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
	 * @package core_shared
	 * @version 2014-04-19
	*/

	class _pdo extends pdo_resources {
		protected $pdo, $prepared, $connect;
		private $query;
		protected static $instances = [];

		public static function load($con = 'connect') {
			/**
			 * @method load
			 * @desc
			 * Static load function avoids creating multiple instances/connections
			 * It stores an array of instances in the static instances array.
			 * It uses $con as the key to the array, and the _pdo instance as
			 * the value.
			 *
			 * @param string $con (.ini file to use for database credentials)
			 * @return pdo_object/class
			 * @example $pdo = _pdo::load or $pdo = _pdo::load('connect')
			 */

			if(!array_key_exists($con, self::$instances)) {
				self::$instances[$con] = new self($con);
			}
			return self::$instances[$con];
		}

		public function __construct($con = 'connect') {
			/**
			 * @method __construct
			 * @desc
			 * Gets database connection info from /connect.ini (using ini::load)
			 * The default ini file to use is connect, but can be passed another
			 * in the $con argument.
			 *
			 * Uses that data to create a new PHP Data Object
			 *
			 * @param string $con (.ini file to use for database credentials)
			 * @return void
			 * @example $pdo = new _pdo()
			 */

			parent::__construct($con);
		}

		public function prepare($query) {
			/**
			 * Argument $query is a SQL query in prepared statement format
			 * "SELECT FROM `$table` WHERE `column` = ':$values'"
			 * Note the use of the colon. These are what we are going to be
			 * binding values to a little later
			 *
			 * Returns $this for chaining. Most further functions will do the same where useful
			 *
			 * @param string $query
			 * @return self
			*/

			$this->prepared = $this->pdo->prepare($query);
			return $this;
		}

		public function bind(array $array) {
			/**
			 * Binds values to prepared statements
			 *
			 * @param array $array
			 * @return self
			 * @example $pdo->prepare(...)->bind([
			 * 	'col_name' => $value,
			 * 	'col2' => 'something else'
			 * ])
			 */

			foreach($array as $paramater => $value) {
				$this->prepared->bindValue(':' . $paramater, $value);
			}
			return $this;
		}

		public function execute() {
			/**
			 * Executes prepared statements. Does not return results
			 *
			 * @param void
			 * @return self
			 */

			if($this->prepared->execute()) {
				return $this;
			}
			return false;
		}

		public function get_results($n = null) {
			/**
			 * Gets results of prepared statement. $n can be passed to retreive a specific row
			 *
			 * @param [int $n]
			 * @return mixed
			 */

			$results = array();
			foreach($this->prepared->fetchAll(PDO::FETCH_CLASS) as $data) {		//Convert from an associative array to a stdClass object
				$results[] = (object)$data;
			}
			//If $n is set, return $results[$n] (row $n of results) Else return all
			if(!count($results)) return false;
			if(is_int($n)) return $results[$n];
			else return $results;
		}

		public function close() {
			/**
			 * Need PDO method to close database connection
			 *
			 * @param void
			 * @return void
			 * @todo Make it actually close the connection
			 */

			unset($this->pdo);
			unset($this);
		}

		public function prepare_key_value(array &$arr) {
			/**
			 * While this works with multi-dimensional
			 * array, it is intended to be used for things
			 * like array_insert that use a simple array where
			 * the keys are columns and the values are the values
			 *
			 * It takes a pointer to the array as its argument,
			 * so $arr = $pdo->prepare_key_value($arr) is the same as
			 * $$pdo->prepare_key_value($arr)
			 *
			 * @depreciated
			 * @param array $arr
			 * @return array
			 * @usage $arr = [
			 * 	'Key' => 'Value',
			 * 'int' => 42,
			 * 'Index Only',
			 * 'Second_Level' => [...]
			 * ];
			 * $pdo->prepare_key_value($arr)
			 */

			$keys = array_keys($arr);
			$values = array_values($arr);
			$key_walker = function(&$key) {
				if(is_string($key)) {
					$this->escape($key);
					$key = "`{$key}`";
				}
			};
			$value_walker = function(&$value) {
				if(is_array($value)) $this->prepare_key_value($value);
				elseif (is_string($value)) $this->quote($value);
			};
			array_walk($keys, $key_walker);
			array_walk($values, $value_walker);
			$arr = array_combine($keys, $values);
			return $arr;
		}

		public function binders(array $arr, $prefix = null, $suffix = null) {
			/**
			 * Make setting up prepared statements much easier by
			 * setting up all of the components using $key => $value of $arr
			 *
			 * $binds->cols[] is an array of column names
			 * taken from the keys of $arr in the format of
			 * `{$pdo->escape($key)}`
			 *
			 * $binds->bindings is created from the keys to $arr
			 * and used in prepared statements as what will be bound to
			 * in the format of :{$prefix}{$key}{$suffix} with whitespaces
			 * converted to underscores
			 *
			 * $binds->values is array_values($arr) {original values
			 * without the keys}
			 *
			 * @depreciated
			 * @param array $arr
			 * @param string $prefix
			 * @param string $suffix
			 * @return object {cols:[], bindings: [], values: []}
			 * @usage:
			 * 	$binders = $pdo->binders([...])
			 * 	$pdo->prepare("
			 * 		INSERT INTO {$pdo->escape($table)}
			 * 		(" . join(',', $binders->cols) . ")
			 * 		VALUES(" . join(',', $binders->bindings) . ")
			 * ")->bind(array_combine($binders->bindings, $binders->values))->execute();
			 */

			$binds = new stdClass();
			$binds->cols = array_keys($this->prepare_keys($arr));
			$binds->bindings = [];
			$binds->values = array_values($arr);
			foreach(array_keys($arr) as $key) {
				array_push($binds->bindings, preg_replace('/\s/', '_', ":{$prefix}{$key}{$suffix}"));
			}
			return $binds;
		}

		public function query($query) {
			/**
			 * Get the results of a SQL query
			 *
			 * @param string $query
			 * @return
			 */

			return $this->pdo->query((string)$query);
		}

		public function fetch_array($query = null, $n = null) {
			/**
			 * Return the results of a query as an associative array
			 *
			 * @param string $query
			 * @return array
			 */

			$data = $this->query($query)->fetchAll(PDO::FETCH_CLASS);
			if(is_array($data)){
				return (is_int($n)) ? $data[$n] : $data;
			}
			return [];
		}

		public function get_table($table, $these = '*') {
			/**
			 * @param string $table[, string $these]
			 * @return array
			 */

			if($these !== '*') $these ="`{$these}`";
			return $this->fetch_array("SELECT {$these} FROM {$this->escape($table)}");
		}

		public function array_insert($table = null, array $content) {
			/**
			 *
			 * @param string $table, array $content
			 * @return self
			 * @example "$pdo->array_insert($table, array('var1' => 'value1', 'var2' => 'value2'))"
			 */

			return $this->prepare("
				INSERT INTO `{$this->escape($table)}`
				(" . join(', ', array_keys($this->prepare_keys($content))) . ")
				VALUES(:" . join(', :', array_keys($content)) . ")
			")->bind($content)->execute();
		}

		public function sql_table($query = null, $caption = null) {
			/**
			 * Converts a MySQL query into an HTML <table>
			 * complete with thead and tfoot and optional caption
			 *
			 * @param string $query (MySQL Query)
			 * @return string (HTML <table>)
			 * @example $pdo->sql_table('SELECT * FROM `table`')
			 */

			$results = $this->fetch_array($query);

			if(is_array($results) and count($results)) {
				$table = '<table>';
				$thead = '<thead><tr>';
				$tfoot = '<tfoot><tr>';
				$tbody = '<tbody>';

				if(isset($caption)) {
					$table .= "<caption>{$caption}</caption>";
					unset($caption);
				}

				foreach(array_keys(get_object_vars($results[0])) as $th) {
					$thead .= "<th>{$th}</th>";
					$tfoot .= "<th>{$th}</th>";
				}
				$thead .= '</tr></thead>';
				$tfoot .= '</tr></tfoot>';
				$table .= $thead;
				$table .= $tfoot;
				unset($thead);
				unset($tfoot);

				foreach($results as $result) {
					$tbody .= '<tr>';
					foreach(get_object_vars($result) as $td) {
						$tbody .= "<td>{$td}</td>";
					}
					$tbody .= '</tr>';
				}

				$tbody .= '</tbody>';
				$table .= $tbody;
				unset($tbody);
				$table .= '</table>';

				return $table;

			}

			return null;
		}

		public function update($table = null, $name = null, $value = null, $where = null) {
			/**
			 * Updates a table according to these arguments
			 *
			 * @param string $table, string $name, string $value, string $where
			 * @return
			 */

			return $this->query("UPDATE `{$table}` SET `{$name}` = {$this->quote($value)} WHERE {$where}");
		}

		public function show_tables() {
			/**
			 * Returns a 0 indexed array of tables in database
			 *
			 * @param void
			 * @return array
			 */

			$query = "SHOW TABLES";
			$results = $this->pdo->query($query);
			$tables = $results->fetchAll(PDO::FETCH_COLUMN, 0);
			return $tables;
		}

		public function show_databases() {
			/**
			 * Returns a 0 indexed array of tables in database
			 *
			 * @param void
			 * @return array
			 */

			$query = 'SHOW DATABASES';
			$results = $this->pdo->query($query);
			$databases = $results->fetchAll(PDO::FETCH_COLUMN, 0);
			return $databases;
		}

		public function table_headers($table = null) {
			/**
			 * Returns a 0 indexed array of column headers for $table
			 *
			 * @param string $table
			 * @return array
			 */

			$query = "DESCRIBE {$this->escape($table)}";
			$results = $this->pdo->query($query);
			$headers = $results->fetchAll(PDO::FETCH_COLUMN, 0);
			return $headers;
		}

		public function describe($table = null) {
			/**
			 * Describe $table, including:
			 * Field {name}
			 * Type {varchar|int... & (length)}
			 * Null (boolean)
			 * Default {value}
			 * Extra {auto_increment, etc}
			 *
			 * @param string $table
			 * @return array
			 */

			return $this->pdo->query("DESCRIBE `{$this->escape($table)}")->fetchAll(PDO::FETCH_CLASS);
		}

		public function value_properties($query = null) {
			/**
			 * Returns the results of a SQL query as a stdClass object
			 *
			 * @param string $query
			 * @return array
			 */

			$array = array();
			$results = $this->fetch_array($query);
			foreach($results as $result) {
				$data = new stdClass();
				foreach($results as $key => $value) {
					$key = trim($key);
					$value = trim($value);
					$data->$key = $value;
				}
				array_push($array, $data);
			}
			return $array;
		}

		public function name_value($table = null) {
			/**
			 * For simple Name/Value tables. Gets all name/value pairs. Returns stdClass object
			 *
			 * @param [string $table]
			 * @return obj
			 */

			$data = $this->fetch_array("SELECT `name`, `value` FROM `{$this->escape($table)}`");
			$values = new stdClass();
			foreach($data as $row) {
				$name = trim($row->name);
				$value = trim($row->value);
				$values->$name = $value;
			}
			return $values;
		}

		public function reset_table($table = null) {
			/**
			 * Removes all entries in a table and resets AUTO_INCREMENT to 1
			 *
			 * @param string $table
			 * @return void
			 */

			$this->escape($table);

			$this->query("DELETE FROM `{$table}`");
			$this->query("ALTER TABLE `{$table}` AUTO_INCREMENT = 1");
			return $this;
		}
	}
?>
