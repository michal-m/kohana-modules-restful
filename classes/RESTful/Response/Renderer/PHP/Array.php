<?php defined('SYSPATH') or die('No direct script access.');
/**
 * PHP Data Response Renderer class for application/php-serialized mime-type.
 *
 * @package     RESTful
 * @category    Renderers
 * @author      Michał Musiał
 * @copyright   (c) 2013 Michał Musiał
 */
class RESTful_Response_Renderer_PHP_Array {

    /**
     * @param   mixed $input
     * @return  string
     */
    static public function render($data)
    {
        return serialize((array) $data);
    }
}
