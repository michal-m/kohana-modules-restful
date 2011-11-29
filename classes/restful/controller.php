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
 * [!!] Using this class within a website will require heavy modification,
 * due to most web browsers only supporting the GET and POST methods.
 * Generally, this class should only be used for web services and APIs.
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
		HTTP_Request::GET    => 'get',
		HTTP_Request::PUT    => 'update',
		HTTP_Request::POST   => 'create',
		HTTP_Request::DELETE => 'delete',
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
		// Enable RESTful internal error handling
		set_exception_handler(array('RESTful_Exception', 'handler'));
		// Enable Kohana error handling, converts all PHP errors to exceptions.
		set_error_handler(array('RESTful', 'error_handler'));
		
		parent::__construct($request, $response);
	}

	/**
	 * Preflight checks.
	 */
	public function before()
	{
		// Defaulting output content type to text/plain - will hopefully be overriden later
		$this->response->headers('Content-Type', 'text/plain');
		
		$method = Arr::get($_SERVER, 'HTTP_X_HTTP_METHOD_OVERRIDE', $this->request->method());
		
		// Checking requested method
		if ( ! isset($this->_action_map[$method]))
		{
			return $this->request->action('invalid');
		}
		elseif ( ! method_exists($this, 'action_' . $this->_action_map[$method]))
		{
			throw new HTTP_Exception_500('METHOD_MISCONFIGURED');
		}
		else
		{
			$this->request->action($this->_action_map[$method]);
		}

		// Checking Content-Type. Considering only POST and PUT methods as other
		// shouldn't have any content.
		if (in_array($method, array(HTTP_Request::POST, HTTP_Request::PUT)))
		{
			$request_content_type = Arr::get($_SERVER, 'CONTENT_TYPE', FALSE);
			
			if ($request_content_type === FALSE)
			{
				throw new HTTP_Exception_400('NO_CONTENT_TYPE_PROVIDED');
			}
			
			if ( ! array_key_exists($request_content_type, $this->_accept_types) OR
				 ! class_exists($parser_prefix . $this->_accept_types[$request_content_type]))
			{
				throw new HTTP_Exception_415();
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
				throw new HTTP_Exception_400('MALFORMED_REQUEST_BODY');
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
		if ($method === HTTP_Request::GET AND ! $this->_renderer)
		{
			throw new HTTP_Exception_406(
				'This service delivers following types: :types.',
				array(':types' => implode(', ', array_keys($this->_response_types)))
			);
		}
		
		return TRUE;
	}
	
	/**
	 * Renders response body using renderer selected during before().
	 * Prevents caching for PUT/POST/DELETE request methods.
	 */
	public function after()
	{
		// Render response body
		$body = call_user_func(array($this->_renderer, 'render'), $this->response->body());

		if ($body !== FALSE)
		{
			$this->response->body($body);
		}
		else
		{
			throw new HTTP_Exception_500(
				'Error generating response content using in: :mime.',
				array(':mime' => '')
			);
		}
		
		// Prevent caching
		if (in_array(Arr::get($_SERVER, 'HTTP_X_HTTP_METHOD_OVERRIDE', $this->request->method()), array(
			HTTP_Request::PUT,
			HTTP_Request::POST,
			HTTP_Request::DELETE)))
		{
			$this->response->headers('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
		}
	}
	
	/**
	 * Throws a HTTP_Exception_405 as a response with a list of allowed actions.
	 */
	public function action_invalid()
	{
		// Send the "Method Not Allowed" response
		$this->response->headers('Allow', implode(', ', array_keys($this->_action_map)));
		throw new HTTP_Exception_405();
	}
}
