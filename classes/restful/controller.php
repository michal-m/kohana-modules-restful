<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Abstract Controller class for RESTful controller mapping. Supports GET, PUT,
 * POST, and DELETE. By default, these methods will be mapped to these actions:
 *
 * GET
 * :  Mapped to the "get" action, lists all objects
 *
 * POST
 * :  Mapped to the "create" action, creates a new object
 *
 * PUT
 * :  Mapped to the "update" action, update an existing object
 *
 * DELETE
 * :  Mapped to the "delete" action, delete an existing object
 *
 * Additional methods can be supported by adding the method and action to
 * the `$_action_map` property.
 *
 * @package		RESTful
 * @category	Controllers
 * @author		Michał Musiał
 * @copyright	(c) 2011 Michał Musiał
 *
 * @todo		Caching responses
 * @todo		Fallback to $_POST['_method'] if detected
 * @todo		Authentication (Authorization: ... header)
 * @todo		Move request related properties to $request property.
 * @todo		Investigate HEAD/OPTIONS methods
 */
abstract class RESTful_Controller extends Controller
{
	/**
	 * @var RESTful_Request
	 */
	public $request;
	
	/**
	 * @var RESTful_Response
	 */
	public $response;
	
	/**
	 * @var array Array of possible actions.
	 */
	protected $_action_map = array(
		'GET'    => 'get',
		'PUT'    => 'update',
		'POST'   => 'create',
		'DELETE' => 'delete',
	);

	/**
	 * @var array Array of mime-types this controller is able to respond
	 *			  with. Format:
	 *			  mime-type => renderer
	 */
	protected $_response_types = array(
		'application/json'				=> 'JSON',
		'text/plain'					=> 'PLAIN',
		'application/php-serialized'	=> 'PHP',
		'text/php-printr'				=> 'PRINTR',
	);

	/**
	 * @var array Array of mime-types this controller is able to
	 *			  understand (when request method is POST or PUT). Format:
	 *			  mime-type => parser
	 */
	protected $_accept_types = array(
		'text/plain'						=> 'PLAIN',
		'application/x-www-form-urlencoded'	=> 'URLENC',
		'application/json'					=> 'JSON',
		'application/php-serialized'		=> 'PHP',
	);

	/**
	 * @var string
	 */
	protected $_request_data_raw;

	/**
	 * @var mixed
	 */
	protected $_request_data;

	/**
	 * Full name of the Renderer class
	 *
	 * @var string
	 */
	protected $_renderer;

	/**
	 * Controller Constructor
	 * 
	 * @param Request $request
	 * @param Response $response
	 */
	public function __construct(Request $request, Response $response)
	{
		if (Kohana::$errors === TRUE)
		{
			// Enable RESTful internal error handling
			set_exception_handler(array('RESTful', 'exception_handler'));
		}
		
		parent::__construct($request, $response);
	}

	/**
	 * Preflight checks.
	 */
	public function before()
	{
		// Defaulting output content type to text/plain - will hopefully be overriden later
		$this->response->headers('Content-Type', 'text/plain');
		
		// Checking requested method
		if ( ! isset($this->_action_map[$this->request->method()]))
		{
			$this->response->headers('Allow', implode(', ', array_keys($this->_action_map)));
			$this->_error(405);
			return FALSE;
		}
		else
		{
			$this->request->action($this->_action_map[$this->request->method()]);
		}

		// Checking Content-Type. Considering only POST and PUT methods as other
		// shouldn't have any content.
		if (in_array($this->request->method(), array('POST', 'PUT')))
		{
			$request_content_type = (array_key_exists('CONTENT_TYPE', $_SERVER)) ? $_SERVER['CONTENT_TYPE'] : FALSE;
			$parser_prefix = 'RESTful_RequestParser_';

			if ( ! array_key_exists($request_content_type, $this->_accept_types) OR
				 ! class_exists($parser_prefix . $this->_accept_types[$request_content_type]))
			{
				$this->_error(415);
				return FALSE;
			}
			else
			{
				// Looking for Raw POST data
				if (isset($HTTP_RAW_POST_DATA))
				{
					$this->_request_data_raw = $HTTP_RAW_POST_DATA;
				}
				else
				{
					$this->_request_data_raw = file_get_contents('php://input');
				}
				
				if ( ! empty($this->_request_data_raw))
				{
					$parser = $parser_prefix . $this->_accept_types[$request_content_type];
					$this->_request_data = call_user_func(array($parser, 'parse'), $this->_request_data_raw);
				}
				else
				{
					$this->_request_data = $_POST;
				}
			}

			if ($this->_request_data === FALSE)
			{
				$this->_error(500, 'ERROR_PARSING_REQUEST_DATA');
				return FALSE;
			}
		}

		// Checking Accept mime-types
		$requested_mime_types = Request::accept_type();
		$renderer_prefix = 'RESTful_ResponseRenderer_';
		$config_defaults = Kohana::$config->load('restful.defaults');

		if (count($requested_mime_types) == 0 OR (count($requested_mime_types) == 1 AND isset($requested_mime_types['*/*'])))
		{
			$this->response->headers('Content-Type', $config_defaults->get('content-type'));
			$this->_renderer = $renderer_prefix . $this->_response_types[$config_defaults->get('content-type')];
		}
		else
		{
			foreach ($requested_mime_types as $type => $q)
			{
				if (array_key_exists($type, $this->_response_types))
				{
					$this->response->headers('Content-Type', $type);
					$this->_renderer = $renderer_prefix . $this->_response_types[$type];
					break;
				}
			}
		}

		// Script should fail only if requester expects any content returned,
		// that is when it uses GET method.
		if ($this->request->method() == 'GET' AND ! $this->_renderer)
		{
			$this->_error(406, 'This service delivers following types: ' . implode(', ', array_keys($this->_response_types)) . '.');
			return FALSE;
		}
		
		return TRUE;
	}

	/**
	 * @param int $status
	 * @param string $msg 
	 */
	protected function _error($status = 500, $msg = '')
	{
		$this->request->action('error');
		$this->response->status($status);

		if (strlen($msg))
		{
			$this->response->headers('Content-Type', 'text/plain');
			$this->response->body($msg);
		}
	}

	/**
	 * @param mixed $input
	 */
	protected final function _set_response_content($input)
	{
		$response = call_user_func(array($this->_renderer, 'render'), $input);

		if ($response !== FALSE)
		{
			$this->response->body($response);
		}
		else
		{
			throw new RESTful_Exception(
				':error',
				array(':error' => 'Error generating response content using ' . $this->_renderer));
		}
	}

	/**
	 * Error action.
	 */
	public function action_error() {}
}
