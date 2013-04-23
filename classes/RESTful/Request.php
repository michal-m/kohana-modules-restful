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

    /**
     * @var string
     */
    protected $_body_content_type;

    /**
     * @var mixed
     */
    protected $_data;

    /**
     * Sets and gets the request body type.
     *
     * @param   string  $content_type
     * @return  mixed
     */
    public function body_content_type($content_type = NULL)
    {
        if ($content_type === NULL)
        {
            // Act as getter
            return $this->_request_content_type;
        }

        // Act as setter
        $this->_request_content_type = $content_type;

        return $this;
    }

    /**
     * Sets and gets the data from the request.
     *
     * @param   mixed   $data
     * @return  mixed
     */
    public function data($data = NULL)
    {
        if ($data === NULL)
        {
            // Act as getter
            return $this->_data;
        }

        // Act as setter
        $this->_data = $data;

        return $this;
    }

    /**
     * Parses Request body
     *
     * @return  mixed   Returns decoded request body
     * @throws  HTTP_Exception_500
     */
    public function parse_body()
    {
        if (strlen($this->_body) > 0)
        {
            $request_data = call_user_func(self::parser($this->_body_content_type), $this->_body);

            if ($request_data === FALSE)
                throw HTTP_Exception::factory(400, 'MALFORMED_REQUEST_BODY');

            $this->_data = $request_data;
        }
    }
}
