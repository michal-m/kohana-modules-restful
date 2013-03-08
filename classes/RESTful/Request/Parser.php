<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Request Data Parser class.
 *
 * @package     RESTful
 * @category    Parsers
 * @author      Michał Musiał
 * @copyright   (c) 2013 Michał Musiał
 */
class RESTful_Request_Parser {

    /**
     * Parser for application/json mime type.
     *
     * @param   string  $data
     * @return  mixed
     */
    static public function application_json($request_body)
    {
        $decoded = json_decode($request_body);
        return (json_last_error() === JSON_ERROR_NONE) ? $decoded : FALSE;
    }

    /**
     * Parser for application/php-serialized mime type.
     *
     * @param   string  $data
     * @return  mixed
     */
    static public function application_php_serialized($request_body)
    {
        return @unserialize($request_body);
    }

    /**
     * Parser for application/x-www-form-urlencoded mime type.
     *
     * @param   string  $data
     * @return  array
     */
    static public function application_x_www_form_urlencoded($request_body)
    {
        $data = array();
        parse_str($request_body, $data);
        return $data;
    }

    /**
     * Parser for text/plain mime type.
     *
     * @param   string  $data
     * @return  string
     */
    static public function text_plain($request_body)
    {
        return $request_body;
    }
}
