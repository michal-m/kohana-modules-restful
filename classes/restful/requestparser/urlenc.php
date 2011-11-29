<?php defined('SYSPATH') or die('No direct script access.');

/**
 * URLENC Request Data Parser class for text/plain mime-type.
 *
 * @package		RESTful
 * @category	Parsers
 * @author		Michał Musiał
 * @copyright	(c) 2011 Michał Musiał
 */
class RESTful_RequestParser_URLENC implements RESTful_IRequestParser
{
	/**
	 * @param	string $data
	 * @return	array
	 */
	static public function parse($request_body)
	{
		return urldecode($request_body);
	}
}
