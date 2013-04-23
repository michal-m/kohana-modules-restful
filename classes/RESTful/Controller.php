<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Abstract Controller class for RESTful controller mapping.
 *
 * [!!] Using this class within a website is not recommended, due to most web
 * browsers only supporting the GET and POST methods. Generally, this class
 * should only be used for web services and APIs.
 *
 * @package     RESTful
 * @author      Michał Musiał
 * @copyright   (c) 2013 Michał Musiał
 */
abstract class RESTful_Controller extends Controller {

    /**
     * @var array Array of possible actions.
     */
    public static $action_map = array(
        HTTP_Request::GET    => 'get',
        HTTP_Request::PUT    => 'update',
        HTTP_Request::POST   => 'create',
        HTTP_Request::DELETE => 'delete',
    );

    /**
     * Executes the given action and calls the [Controller::before] and [Controller::after] methods.
     *
     * Can also be used to catch exceptions from actions in a single place.
     *
     * 1. Before the controller action is called, the [Controller::before] method
     * will be called.
     * 2. Next the controller action will be called.
     * 3. After the controller action is called, the [Controller::after] method
     * will be called.
     *
     * @throws  HTTP_Exception_405
     * @return  Response
     */
    public function execute()
    {
        // Execute the "before action" method
        $this->before();

        // Determine the action to use
        $action = 'action_'.$this->request->action();

        // If the action doesn't exist, it's a 405 Method Not Allowed
        if ( ! method_exists($this, $action))
        {
            throw HTTP_Exception::factory(405)
                ->headers('Allow', implode(', ', array_keys($this->_action_map)))
                ->request($this->request);
        }

        // Execute the action itself
        $this->{$action}();

        // Execute the "after action" method
        $this->after();

        // Return the response
        return $this->response;
    }

    /**
     * Preflight checks.
     */
    public function before()
    {
        parent::before();

        // Defaulting output content type to text/plain - will hopefully be overriden later
        $this->response->headers('Content-Type', 'text/plain');

        // Override default error view and content type
        HTTP_Exception::$error_view = 'restful/error';
        HTTP_Exception::$error_view_content_type = 'text/plain';

        $method = strtoupper($this->request->method());

        // Checking Content-Type. Considering only POST and PUT methods as other
        // shouldn't have any content.
        if (in_array($method, array(HTTP_Request::POST, HTTP_Request::PUT)))
        {
            $request_content_type_full = $this->request->headers('Content-Type');

            if ($request_content_type_full === NULL)
                throw HTTP_Exception::factory(400, 'MISSING_REQUEST_CONTENT_TYPE');

            $type_found = preg_match('|^([^/]+/[^;$]+)(;\s*charset=(.*))?|', $request_content_type_full, $matches);

            if ( ! $type_found)
                throw HTTP_Exception::factory(400, 'MALFORMED_REQUEST_CONTENT_TYPE');

            $request_content_type = $matches[1];
            // $request_content_charset = $matches[3];

            if (RESTful_Request::parser($request_content_type) === FALSE)
                throw HTTP_Exception::factory(415);

            $this->request->body_content_type($request_content_type);
            $this->request->parse_body();
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

        $this->response->headers('Content-Type', $preferred_response_content_type);
        RESTful_Response::default_type($preferred_response_content_type);
        Kohana_Exception::$error_view_content_type = $preferred_response_content_type;
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
}
