<?php defined('SYSPATH') or die('No direct script access.');
/**
 * JSON Data Response Renderer class.
 *
 * @package     RESTful
 * @category    Renderers
 * @author      Michał Musiał
 * @copyright   (c) 2013 Michał Musiał
 */
class RESTful_Response_Renderer {

    /**
     * Renderer for application/json mime type.
     *
     * @param   mixed   $input
     * @return  string
     */
    static public function application_json($data)
    {
        $json = json_encode($data);

        if (json_last_error() !== JSON_ERROR_NONE)
            throw HTTP_Exception::factory(500, 'RENDERER_ERROR_JSON_ENCODE');

        return $json;
    }

    /**
     * Renderer for application/php-serialized mime type.
     *
     * @param   mixed   $input
     * @return  string
     */
    static public function application_php_serialized($data)
    {
        return serialize($data);
    }

    /**
     * Renderer for application/php-serialized-array mime type.
     *
     * @param   mixed   $input
     * @return  string
     */
    static public function application_php_serialized_array($data)
    {
        return serialize( (array) $data);
    }

    /**
     * Renderer for application/php-serialized-object mime type.
     *
     * @param   mixed   $input
     * @return  string
     */
    static public function application_php_serialized_object($data)
    {
        return serialize( (object) $data);
    }

    /**
     * Renderer for text/php-printr mime type.
     *
     * @param   mixed $input
     * @return  string
     */
    static public function text_php_printr($data)
    {
        return print_r($data, TRUE);
    }

    /**
     * Renderer for text/plain mime type.
     *
     * @param   mixed $input
     * @return  string
     */
    static public function text_plain($data)
    {
        if (is_object($data) AND ! method_exists($data, '__toString'))
            return 'Object of ' . get_class($data) . ' class';
        elseif (is_scalar($data))
            return (string) $data;
        else
            return gettype($data);
    }
}
