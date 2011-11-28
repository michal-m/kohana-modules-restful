<?php defined('SYSPATH') or die('No direct script access.');

/**
 * PHP Request Data Parser class for applicatopm/php-serialized mime-type.
 *
 * @package		RESTful
 * @category	Parsers
 * @author		Michał Musiał
 * @copyright	(c) 2011 Michał Musiał
 */
class RESTful_RequestParser_PHP implements RESTful_IRequestParser
{
	/**
	 * @param string $data
	 */
	static public function parse($data)
	{
		return unserialize($data);
	}
}
