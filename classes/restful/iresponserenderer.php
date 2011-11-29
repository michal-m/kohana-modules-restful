<?php defined('SYSPATH') or die('No direct script access.');

/**
 * ResponseRenderer Interface
 *
 * @package		RESTful
 * @category	Interfaces
 * @author		Michał Musiał
 * @copyright	(c) 2011 Michał Musiał
 */
interface RESTful_IResponseRenderer
{
	/**
	 * @param mixed $data
	 */
	static public function render($data);
}
