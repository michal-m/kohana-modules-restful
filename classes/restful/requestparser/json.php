<?php defined('SYSPATH') or die('No direct script access.');

/**
 * JSON Request Data Parser class for application/json mime-type.
 *
 * @package		RESTful
 * @category	Parsers
 * @author		Michał Musiał
 * @copyright	(c) 2011 Michał Musiał
 */
class RESTful_RequestParser_JSON implements RESTful_IRequestParser
{
	/**
	 * @param string $data
	 */
	static public function parse($request_body)
	{
		return json_decode($request_body);
	}
}
