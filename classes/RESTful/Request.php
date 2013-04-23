<?php defined('SYSPATH') or die('No direct script access.');
/**
 * RESTful Request class
 *
 * @package     RESTful
 * @author      Michał Musiał
 * @copyright   (c) 2013 Michał Musiał
 */
class RESTful_Request extends Kohana_Request {

    /**
     * @var array
     */
    protected static $_parsers = array();

    /**
     * Parses Request body
     *
     * @param   string  $request_body
     * @param   string  $type
     * @return  mixed   Returns decoded request body
     * @throws  HTTP_Exception_500
     */
    public static function parse($request_body, $type)
    {
        $request_params = call_user_func(self::parser($type), $request_body);

        if ($request_params === FALSE AND ! empty($request_body))
            throw HTTP_Exception::factory(400, 'MALFORMED_REQUEST_BODY');

        return $request_params;
    }

    /**
     * Getter/setter for Request parsers.
     *
     * @param   string      $type       Content MIME type
     * @param   callback    $callback
     * @return  mixed       When used as setter returns previous parser if one existed or TRUE otherwise. When used as getter returns all parsers if $type not specified, a parser callback if found or boolean FALSE otherwise.
     */
    public static function parser($type = NULL, $callback = NULL)
    {
        if ($callback === NULL)
            return ($type === NULL) ? self::$_parsers : Arr::get(self::$_parsers, $type, FALSE);

        $return = (array_key_exists($type, self::$_parsers)) ? self::$_parsers[$type] : TRUE;
        self::$_parsers[$type] = $callback;
        return $return;
    }
}
