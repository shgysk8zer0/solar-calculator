<?php
	/**
	 * Creates and sends a JSON encoded response for XMLHTTPRequests
	 * Optimized to be handled by handleJSON in functions.js
	 *
	 * @author Chris Zuber <shgysk8zer0@gmail.com>
	 * @copyright 2014, Chris Zuber
	 * @license http://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
	 * @package core_shared
	 * @version 2014-04-19
	 * @var array $response
	 * @var array $instances
	 *
	 * @example $resp = new json_response();
	 * $resp->notify(...)->html(...)->append(...)->prepend(...)->before(...)->after(...)->attributes(...)->remove(...)->send();
	 */

	class json_response {
		protected $response = [];
		private static $instance = null;

		public static function load($arr = null) {
			if(is_null(self::$instance)) {
				self::$instance = new self($arr);
			}
			return self::$instance;
		}

		public function __construct(array $arr = null) {
			if(is_array($arr)) {
				$this->response = $arr;
			}
		}

		public function __set($key, $value) {
			/**
			 * Setter method for the class.
			 *
			 * @param string $key
			 * @param mixed $value
			 * @return void
			 * @example "$resp->key = $value"
			 */

			$this->response[$key] = $value;
		}

		public function __get($key) {
			/**
			 * The getter method for the class.
			 *
			 * @param string $key
			 * @return mixed
			 * @example "$resp->key" Returns $value
			 */

			if(array_key_exists($key, $this->response)) {
				return $this->response[$key];
			}
			return false;
		}

		public function __isset($key) {
			/**
			 * @param string $key
			 * @return boolean
			 * @example "isset({$resp->key})"
			 */

			return array_key_exists($key, $this->data);
		}

		public function __unset($key) {
			/**
			 * Removes an index from the array.
			 *
			 * @param string $key
			 * @return void
			 * @example "unset($resp->key)"
			 */

			unset($this->response[$key]);
		}

		public function __call($name, array $arguments) {
			/**
			 * Chained magic getter and setter
			 * @param string $name, array $arguments
			 * @example "$resp->[getName|setName]($value)"
			 */

			$name = strtolower($name);
			$act = substr($name, 0, 3);
			$key = substr($name, 3);
			switch($act) {
				case 'get': {
					if(array_key_exists($key, $this->response)) {
						return $this->response[$key];
					}
					else{
						return false;
					}
				} break;
				case 'set': {
					$this->response[$key] = $arguments[0];
					return $this;
				} break;
			}
		}

		public function text($selector = null, $content = null) {
			/**
			 * Sets textContent of elements matching $selector to $content
			 *
			 * @param string $selector
			 * @param string $content
			 *
			 */

			$this->response['text'][(string)$selector] = (string)$content;
			return $this;
		}

		public function notify($title = null, $body = null, $icon = null) {
			/**
			 * Creates a notification (or alert)
			 *
			 * @param string $title
			 * @param string $body
			 * @param string $icon
			 * @usage $resp->notify('Title', 'Body', 'path/to/icon.png');
			 */

			$this->response['notify'] = [];
			if(isset($title)) $this->response['notify']['title'] = (string)$title;
			if(isset($body)) $this->response['notify']['body'] = (string)$body;
			if(is_string($icon)) $this->response['notify']['icon'] = $icon;
			return $this;
		}

		public function html($selector = null, $content = null) {
			/**
			 * @param string $selector
			 * @param string $content
			 * @usage $resp->html('.cssSelector', '<p>Some HTML content</p>');
			 */
			if(!array_key_exists('html', $this->response)) $this->response['html'] = [];
			$this->response['html'][(string)$selector] = (string)$content;
			return $this;
		}

		public function append($selector = null, $content = null) {
			/**
			 * @param string $selector
			 * @param string $content
			 * @usage $resp->append('.cssSelector', '<p>Some HTML content</p>');
			 */

			if(!array_key_exists('append', $this->response)) $this->response['append'] = [];
			$this->response['append'][(string)$selector] = (string)$content;
			return $this;
		}

		public function prepend($selector = null, $content = null) {
			/**
			 * @param string $selector
			 * @param string $content
			 * @usage $resp->prepend('.cssSelector', '<p>Some HTML content</p>');
			 */

			if(!array_key_exists('prepend', $this->response)) $this->response['prepend'] = [];
			$this->response['prepend'][(string)$selector] = (string)$content;
			return $this;
		}

		public function before($selector = null, $content = null) {
			/**
			 * @param string $selector
			 * @param string $content
			 * @usage $resp->before('.cssSelector', '<p>Some HTML content</p>');
			 */

			if(!array_key_exists('before', $this->response)) $this->response['before'] = [];
			$this->response['before'][(string)$selector] = (string)$content;
			return $this;
		}

		public function after($selector = null, $content = null) {
			/**
			 * @param string $selector
			 * @param string $content
			 * @usage $resp->after('.cssSelector', '<p>Some HTML content</p>');
			 */

			$this->response['after'][(string)$selector] = (string)$content;
			return $this;
		}

		public function addClass($selector = null, $classes = null) {
			/**
			 * @param string $selector
			 * @param string $classes
			 * @usage $resp->addClass('.cssSelector', 'newClass, otherClass');
			 */

			$this->response['addClass'][(string)$selector] = (string)$classes;
			return $this;
		}

		public function removeClass($selector = null, $classes = null) {
			/**
			 * @param string $selector
			 * @param string $classes
			 * @usage $resp->removeClass('.cssSelector', 'someClass, someOtherClass');
			 */

			$this->response['removeClass'][(string)$selector] = (string)$classes;
			return $this;
		}

		public function remove($selector = null) {
			/**
			 * @param string $selector
			 * @usage $resp->remove('html .class > #id');
			 */

			(array_key_exists('remove', $this->response)) ? $this->response['remove'] .= ',' . (string)$selector : $this->response['remove'] = (string)$selector;
			return $this;
		}

		public function attributes($selector = null, $attribute = null, $value = true) {
			/**
			 * @param string $selector
			 * @param string $attribute
			 * @param mixed $value
			 * @usage $resp->attributes(
			 * 	'html', 'contextmenu', false
			 * )->attributes(
			 * 	'html', 'data-menu', 'admin'
			 * );
			 */

			$this->response['attributes'][(string)$selector][(string)$attribute] = $value;
			return $this;
		}

		public function script($js = null) {
			/**
			 * handleJSON in functions.js will eval() $js
			 * Requires 'unsafe-eval' be set on script-src in csp.ini
			 * which is generally a BAD idea.
			 * Including because it is useful.
			 * *USE WITH CAUTION* and watch your quotes
			 *
			 * @param string $js (script to execute)
			 * @usage $resp->script("alert('Hello world')");
			 */

			(array_key_exists('script', $this->response)) ? $this->response['script'] .= ';' . (string)$js : $this->response['script'] = (string)$js;
			return $this;
		}

		public function sessionStorage($key = null, $value = null) {
			/**
			 * handleJSON in functions.js will do sessionStorage[$key] = $value
			 * Useful for storing data temporarily (session) on the client side
			 *
			 * @param string $key
			 * @param mixed $value
			 * @usage $resp->sessionStorage('nonce', $session->nonce)
			 */

			$this->response['sessionStorage'][(string)$key] = $value;
			return $this;
		}

		public function localStorage($key = null, $value = null) {
			/**
			 * handleJSON in functions.js will do localStorage[$key] = $value
			 * Useful for storing data more permenantly on the client side
			 *
			 * @param string $key
			 * @param mixed $value
			 * @usage $resp->localStorage('greeting', 'Hello World!')
			 */

			$this->response['localStorage'][(string)$key] = $value;
			return $this;
		}

		public function log() {
			/**
			 * handleJSON in functions.js will console.log functions arguments
			 *
			 * @param mixed (arguments passed to function)
			 * @usage $resp->log($session->nonce, $_SERVER['SERVER_NAME']);
			 */

			$args = func_get_args();
			$this->response['log'] = (count($args) == 1) ? $args[0] : $args;
			return $this;
		}

		public function info() {
			/**
			 * handleJSON in functions.js will console.info functions arguments
			 *
			 * @param mixed (arguments passed to function)
			 * @usage $resp->info($session->nonce, $_SERVER['SERVER_NAME']);
			 */

			$args = func_get_args();
			$this->response['info'] = (count($args) == 1) ? $args[0] : $args;
			return $this;
		}

		public function warn() {
			/**
			 * handleJSON in functions.js will console.warn functions arguments
			 *
			 * @param mixed (arguments passed to function)
			 * @usage $resp->warn($session->nonce, $_SERVER['SERVER_NAME']);
			 */

			$args = func_get_args();
			$this->response['warn'] = (count($args) == 1) ? $args[0] : $args;
			return $this;
		}

		public function error() {
			/**
			 * handleJSON in functions.js will console.error functions arguments
			 *
			 * @param mixed (arguments passed to function)
			 * @usage $resp->error($error);
			 */

			$args = func_get_args();
			$this->response['error'] = (count($args) == 1) ? $args[0] : $args;
			return $this;
		}

		public function scrollTo($sel = 'body', $nth = 0) {
			/**
			 * Will use document.querySellectorAll($sel).item($nth).scrollIntoView()
			 * which means that you can scroll to any given element (body
			 * is default)
			 *
			 * @param string $sel (CSS selector)
			 * @param int $nth
			 * @example $resp->scrollTo('ul.myList li', 3)
			 */

			$this->response['scrollTo'] = [
				'sel' => (string)$sel,
				'nth' => (int)$nth
			];
			return $this;
		}

		public function focus($sel = 'input') {
			/**
			 * Will use document.querySellector($sel).focus()
			 *
			 * @param string $sel (CSS selector)
			 * @example $resp->focus('input[name="password"]')
			 */

			$this->response['focus'] = (string)$sel;
			return $this;
		}

		public function select($sel = 'input') {
			/**
			 * Will use document.querySellector($sel).sselect()
			 *
			 * @param string $sel (CSS selector)
			 * @example $resp->select('input[name="password"]')
			 */

			$this->response['focus'] = (string)$sel;
			return $this;
		}

		public function reload() {
			/**
			 * Triggers window.location.reload() in handleJSON
			 *
			 * @param void
			 * @example $resp->reload()
			 */

			$this->response['reload'] = null;
		}

		public function clear($form = null) {
			/**
			 * Triggers document.forms[$form].reset() in handleJSON
			 *
			 * @param string $form (name of the form)
			 * @example $resp->clear('login')
			 */
			$this->response['clear'] = (string)$form;
			return $this;
		}

		public function triggerEvent($selector = null, $event = null) {
			/**
			 * Will trigger an event ($event) on targets ($selector) in handleJSON
			 *
			 * handleJSON needs to determine which type of event to trigger
			 *
			 * @link https://developer.mozilla.org/en-US/docs/Web/Events
			 * @param string $selector (CSS selector for target(s))
			 * @param string $event (Event to be triggered)
			 * @example $resp->triggerEvent('button[type=submit]', 'click')
			 */
			if(!array_key_exists('triggerEvent', $this->response)) {
				$this->response['triggerEvent'] = [];
			}
			$this->response['triggerEvent'][(string)$selector] = (string)$event;
			return $this;
		}

		public function open($url = null, array $paramaters = null, $replace = false, $name = '_blank') {
			/**
			 * Creates a popup window via JavaScript's window.open()
			 *
			 * @link http://www.w3schools.com/jsref/met_win_open.asp
			 * @param string $url
			 * @param array $paramaters,
			 * @param boolean $replace
			 * @example $resp->open(
			 * 	'http://example.com',
			 * 	[
			 * 		'height' => 500,
			 * 		'width' => 500
			 * 	],
			 * 	false
			 * )
			 */

			$specs = [
				'height' => 500,
				'width' => 500,
				'top' => 0,
				'left' => 0,
				'resizable' => 1,
				'titlebar' => 0,
				'menubar' => 0,
				'toolbar' => 0,
				'status' => 0
			];

			if(is_array($paramaters)) {
				foreach($paramaters as $key => $value) {
					$specs[$key] = (string)$value;
				}
			}

			$this->response['open'] = [
				'url' => $url,
				'name' => $name,
				'specs' => $specs,
				'replace' => $replace
			];

			return $this;
		}

		public function show($sel = null) {
			/**
			 * Causes handleJSON to run show() on all $sel.
			 *
			 * For <deails>, this will add the 'open' attribute.
			 * For <dialog> this will run the native show() method, if
			 * available. Otherwise, just adds the 'open' attribute there as well.
			 *
			 * @param string $sel (CSS selector)
			 * @example $resp->show('dialog')
			 */

			$this->response['show'] = (string)$sel;
			return $this;
		}

		public function showModal($sel = null) {
			/**
			 * Causes handleJSON to run show() on all $sel.
			 *
			 * For <deails>, this will add the 'open' attribute.
			 * For <dialog> this will run the native show() method, if
			 * available. Otherwise, just adds the 'open' attribute there as well.
			 *
			 * @param string $sel (CSS selector)
			 * @example $resp->show('dialog')
			 */

			$this->response['showModal'] = (string)$sel;
			return $this;
		}

		public function close($sel = null) {
			/**
			 * Inverse of show() method. This removes
			 * the 'open' attribute or runs the native close() method
			 * for <dialog>
			 *
			 * @param string $sel (CSS selector)
			 * @example $resp->close('dialog,details')
			 */

			$this->response['close'] = (string)$sel;
			return $this;
		}

		public function enable($sel = null) {
			/**
			 * Removes the 'disabled' attribute on all nodes matching $sel
			 *
			 * @param string $sel (CSS selector)
			 * @example $resp->enable(:disabled)
			 */

			return $this->attributes(
				$sel,
				'disabled',
				false
			);
			return $this;
		}

		public function disable($sel = null) {
			/**
			 * Sets the 'disabled' attribute on all nodes
			 * matching $sel.
			 *
			 * @param string $sel (CSS selector)
			 * @example $resp->disable('button, menuitem, fieldset')
			 */

			return $this->attributes(
				$sel,
				'disabled',
				true
			);
		}

		public function hidden($sel = null, $hide = true) {
			/**
			 * Sets/removes the hidden attribute on all nodes matching $sel
			 *
			 * @param string $sel (CSS selector)
			 * @param boolean $hide (true will add hidden, false will remove it)
			 * @example $resp->hidden('[hidden]', false)
			 */

			return $this->attributes(
				$sel,
				'hidden',
				$hide
			);
		}

		public function dataset($sel = null, $name = null, $value = null) {
			/**
			 * Sets data-* using $this->attributes.
			 *
			 * Makes necessary conversions
			 *
			 * @param string $sel (CSS selector)
			 * @param string $name (data-$name)
			 * @param string $value (string or boolean)
			 * @return json_response Class/Object
			 * @example $resp->dataset('menuitem[label="Click Me"]', 'request', 'action=test')
			 * @link https://developer.mozilla.org/en-US/docs/Web/API/HTMLElement.dataset
			 */

			if(!is_array($this->response['dataset'])) $this->response['dataset'] = [];
			$this->response['dataset'][(string)$sel][(string)$name] = (string)$value;

			return $this;
		}

		public function style($sel = null, $property = null, $value = null) {
			if(!is_array($this->response['style'])) $this->response['style'] = [];
			$this->response['style'][(string)$sel][(string)$property] = (string)$value;
			return $this;
		}

		public function id($sel = null, $id = false) {
			if(is_string($id)) $id = preg_replace(['/\s/', '/[\W]/'], ['_', null], trim($id));
			return $this->attributes(
				(string)$sel,
				'id',
				$id
			);
		}

		public function serverEvent($uri = null) {
			/**
			 * Creates a new server event using handleJSON.
			 *
			 * Server Events are events sent by the server in specific time intervals,
			 * allowing continuous communication from server to browser
			 *
			 * @link https://developer.mozilla.org/en-US/docs/Server-sent_events/Using_server-sent_events
			 * @param string $uri (location of the source of the server event)
			 * @example $resp->serverEvent('event_source.php')
			 */

			$this->response['serverEvent'] = (string)$uri;
			return $this;
		}

		/*public function template($template) {
			$this->response['template'] = $template;
		}*/

		public function debug($format = false) {
			/**
			 * @param boolean $format
			 * @usage $resp->debug((true|false)?);
			 */

			if($format) {
				return json_encode($this->response);
			}
			else {
				return print_r($this, true);
			}
		}

		public function send($key = null) {
			/**
			 * Sends everything with content-type of application/json,
			 * Exits with json_encode($this->response)
			 * An optional $key argument can be used to only
			 * send a subset of $this->response
			 *
			 * @param $key
			 * @usage $resp->send() or $resp->send('notify')
			 */

			if(count($this->response) and !headers_sent()) {
				header('Content-Type: application/json');
				(is_string($key)) ? exit(json_encode([$key => $this->response[$key]])) : exit(json_encode($this->response));
			}
			else {
				http_response_code(403);
				exit();
			}
		}
	}
?>
