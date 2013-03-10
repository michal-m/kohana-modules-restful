<?php defined('SYSPATH') or die('No direct script access.');
/**
 * HTTP Exception class
 *
 * @package     RESTful
 * @author      Michał Musiał
 * @copyright   (c) 2013 Michał Musiał
 */
abstract class HTTP_Exception extends Kohana_HTTP_Exception {

    /**
     * Generate a Response for the current Exception
     *
     * @return Response
     */
    public function get_response()
    {
        return ($this instanceof RESTful_HTTP_Exception) ? RESTful_HTTP_Exception::response($this) : Kohana_Exception::response($this);
    }
}
