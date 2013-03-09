<?php defined('SYSPATH') or die('No direct script access.');
/**
 * RESTful HTTP Exception class
 *
 * @package     RESTful
 * @author      Michał Musiał
 * @copyright   (c) 2013 Michał Musiał
 */
abstract class RESTful_HTTP_Exception extends HTTP_Exception {

    /**
     * Generate a Response for the current Exception
     *
     * @uses   Kohana_Exception::response()
     * @return Response
     */
    public function get_response()
    {
        return Kohana_Exception::response($this);
    }
}
