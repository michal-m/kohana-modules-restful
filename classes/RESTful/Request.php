<?php defined('SYSPATH') or die('No direct script access.');
/**
 * RESTful Request class
 *
 * @package     RESTful
 * @author      Michał Musiał
 * @copyright   (c) 2013 Michał Musiał
 */
class RESTful_Request {

    /**
     * @var array
     */
    protected static $_parsers = array();

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
        {
            return ($type === NULL) ? self::$_parsers : Arr::get(self::$_parsers, $type, FALSE);
        }

        $return = (array_key_exists($type, self::$_parsers)) ? self::$_parsers[$type] : TRUE;
        self::$_parsers[$type] = $callback;
        return $return;
    }

    /**
     * Registers new Request parser.
     *
     * [!!] Deprecated in favor of using [RESTful_Request::parser].
     *
     * @deprecated since version 3.3.0
     * @param   string      $type       Content MIME type
     * @param   callback    $callback
     * @return  mixed       Returns previous parser if one existed or TRUE otherwise
     */
    public static function register_parser($type, $callback)
    {
        return self::parser($type, $callback);
    }
}
