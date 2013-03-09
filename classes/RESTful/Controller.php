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
 * @package     RESTful
 * @category    Controllers
 * @author      Michał Musiał
 * @copyright   (c) 2013 Michał Musiał
 *
 * @todo        Caching responses
 * @todo        Authentication (Authorization: ... header)
 * @todo        Investigate HEAD/OPTIONS methods
 * @todo        Investigate error messages being displayed even when they shouldn't
 */
abstract class RESTful_Controller extends Controller {

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
     * Contains parsed request data.
     *
     * @var mixed
     */
    protected $_request_data;

    /**
     * Controller Constructor
     *
     * @param   Request     $request
     * @param   Response    $response
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
        parent::before();

        // Defaulting output content type to text/plain - will hopefully be overriden later
        $this->response->headers('Content-Type', 'text/plain');

        if ($method_override = $this->request->headers('X-HTTP-Method-Override'))
        {
            $this->request->method($method_override);
        }

        $method = strtoupper($this->request->method());

        // Checking requested method
        if ( ! isset($this->_action_map[$method]))
        {
            return $this->request->action('invalid');
        }
        elseif ( ! method_exists($this, 'action_' . $this->_action_map[$method]))
        {
            throw HTTP_Exception::factory(500, 'METHOD_MISCONFIGURED');
        }
        else
        {
            $this->request->action($this->_action_map[$method]);
        }

        // Checking Content-Type. Considering only POST and PUT methods as other
        // shouldn't have any content.
        if (in_array($method, array(HTTP_Request::POST, HTTP_Request::PUT)))
        {
            $request_content_type = $this->request->headers('Content-Type');

            if (empty($request_content_type))
            {
                throw HTTP_Exception::factory(400, 'NO_CONTENT_TYPE_PROVIDED');
            }

            if (RESTful_Request::parser($request_content_type) === FALSE)
            {
                throw HTTP_Exception::factory(415);
            }

            $request_body = $this->request->body();
            $this->_request_data = (strlen($request_body) > 0) ? RESTful_Request::parse($request_body, $request_content_type): NULL;
        }

        // Determining response content type
        $registered_response_content_types = array_keys(RESTful_Response::renderer());

        // Checking Accept mime-types
        $preferred_response_content_type = $this->request->headers()->preferred_accept($registered_response_content_types);

        // Fail in no preferred response type could be determined.
        if ($preferred_response_content_type === FALSE)
            throw HTTP_Exception::factory(
                406,
                'This service delivers following types: :types.',
                array(':types' => implode(', ', array_keys($this->_response_types)))
            );

        RESTful_Response::default_type($preferred_response_content_type);
    }

    /**
     * Prevents caching for PUT/POST/DELETE request methods.
     */
    public function after()
    {
        $method = strtoupper($this->request->method());

        // Prevent caching
        if ($method == HTTP_Request::PUT OR
            $method == HTTP_Request::DELETE OR
            ($method == HTTP_Request::POST AND $this->response->headers('Cache-Control') === NULL AND $this->response->headers('Expires') === NULL))
        {
            $this->response->headers('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
            $this->response->headers('Expires', -1);
            $this->response->headers('Pragma', 'no-cache');
        }

        parent::after();
    }

    /**
     * Throws a HTTP_Exception_405 as a response with a list of allowed actions.
     */
    public function action_invalid()
    {
        // Send the "Method Not Allowed" response
        $this->response->headers('Allow', implode(', ', array_keys($this->_action_map)));
        throw HTTP_Exception::factory(405);
    }
}
