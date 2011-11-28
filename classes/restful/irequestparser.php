<?php defined('SYSPATH') or die('No direct script access.');
/**
 * RequestParser Interface
 *
 * @package		RESTful
 * @category	Parsers
 * @author		Michał Musiał
 * @copyright	(c) 2011 Michał Musiał
 */
interface RESTful_IRequestParser
{
	static public function parse($data);
}
