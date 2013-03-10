<?php defined('SYSPATH') or die('No direct script access.');
/**
 * RESTful Response class
 *
 * @package     RESTful
 * @author      Michał Musiał
 * @copyright   (c) 2013 Michał Musiał
 */
class RESTful_Response {

    /**
     * @var string
     */
    protected static $_default_type;


     /**
     * @var array
     */
    protected static $_renderers = array();

    /**
     * Gets or sets default renderer type.
     *
     * @param   string  $type
     * @return  string
     */
    public static function default_type($type = NULL)
    {
        if ($type === NULL)
        {
            return self::$_default_type;
        }

        self::$_default_type = $type;
    }

    /**
     * Renders and returns
     *
     * @param   mixed   $data
     * @param   string  $type
     * @return  string  Returns rendered response body.
     * @throws  HTTP_Exception_500
     */
    public static function render($data, $type = NULL)
    {
        if ($type === NULL)
        {
            $type = self::$_default_type;
        }

        $response_body = call_user_func(self::renderer($type), $data);

        if ($response_body === FALSE)
            throw HTTP_Exception::factory(500, 'RESPONSE_RENDERER_ERROR');

        return $response_body;
    }

    /**
     * Getter/setter for Response renderes.
     *
     * @param   string      $type       Content MIME type
     * @param   callback    $callback
     * @return  mixed       When used as setter returns previous renderer if one existed or TRUE otherwise. When used as a setter returns all renderers if $type not specified, a parser callback if found or boolean FALSE otherwise
     */
    public static function renderer($type = NULL, $callback = NULL)
    {
        if ($callback === NULL)
        {
            return ($type === NULL) ? self::$_renderers : Arr::get(self::$_renderers, $type, FALSE);
        }

        $return = (array_key_exists($type, self::$_renderers)) ? self::$_renderers[$type] : TRUE;
        self::$_renderers[$type] = $callback;
        return $return;
    }

    /**
     * Registers new Response renderers.
     *
     * [!!] Deprecated in favor of using [RESTful_Response::renderer].
     *
     * @deprecated since version 3.3.0
     * @param   string      $type       Content MIME type
     * @param   callback    $callback
     * @return  mixed       Returns previous renderer if one existed or TRUE otherwise
     */
    public static function register_renderer($type, $callback)
    {
        return self::renderer($type, $callback);
    }
}
