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
 * @copyright   (c) 2012 Michał Musiał
 *
 * @todo        Caching responses
 * @todo        Authentication (Authorization: ... header)
 * @todo        Investigate HEAD/OPTIONS methods
 * @todo        Investigate error messages being displayed even when they shouldn't
 */
abstract class RESTful_Controller extends Controller
{
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
     * Preferred response mime-type based on provided Accept header in the request.
     *
     * @var mixed String containing preferred Accept mime type or boolean FALSE if none matched.
     */
    protected $_preferred_response_content_type;

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

        $method_override = $this->request->headers('X-HTTP-Method-Override');
        $method = strtoupper((empty($method_override)) ? $this->request->method() : $method_override);

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

            if (RESTful_Request::get_parser($request_content_type) === FALSE)
            {
                throw HTTP_Exception::factory(415);
            }
            else
            {
                $request_body = $this->request->body();

                if (strlen($request_body) > 0)
                {
                    $request_data = call_user_func(RESTful_Request::get_parser($request_content_type), $request_body);
                }
                else
                {
                    $request_data = $_POST;
                }
            }

            if ($request_data !== FALSE AND ! empty($request_data))
            {
                $this->request->body($request_data);
            }
            else
            {
                throw HTTP_Exception::factory(400, 'MALFORMED_REQUEST_BODY');
            }
        }

        // Determining response content type
        $registered_response_content_types = array_keys(RESTful_Response::get_renderer());

        // Checking Accept mime-types
        $this->_preferred_response_content_type = $this->request->headers()->preferred_accept($registered_response_content_types);

        // Fail in no preferred response type could be determined.
        if ($this->_preferred_response_content_type === FALSE)
        {
            throw HTTP_Exception::factory(
                406,
                'This service delivers following types: :types.',
                array(':types' => implode(', ', array_keys($this->_response_types)))
            );
        }
    }

    /**
     * Prevents caching for PUT/POST/DELETE request methods.
     */
    public function after()
    {
        $method_override = $this->request->headers('X-HTTP-Method-Override');
        $method = strtoupper((empty($method_override)) ? $this->request->method() : $method_override);

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

    /**
     * Gets or sets the body of the response
     *
     * @return  mixed
     */
    public function response($content = NULL)
    {
        if ($content === NULL)
            return $this->response->body();

        $this->response->body($this->_render_response_data($content));
        return $this;
    }

    /**
     * Converts data given to a string using renderer selected during before().
     *
     * @param   mixed   $data
     * @throws  HTTP_Exception_500
     */
    protected function _render_response_data($data)
    {
        $success = FALSE;

        // Render response body
        $body = call_user_func(RESTful_Response::get_renderer($this->_preferred_response_content_type), $data);

        if ($body !== FALSE)
        {
            $this->response->body($body);
        }
        else
        {
            throw HTTP_Exception::factory(500, 'RESPONSE_RENDERER_FAILURE');
        }
    }
}
