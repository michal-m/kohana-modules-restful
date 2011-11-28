<?php defined('SYSPATH') or die('No direct script access.');

/**
 * ResponseRenderer Interface
 *
 * @package		RESTful
 * @category	Renderers
 * @author		Michał Musiał
 * @copyright	(c) 2011 Michał Musiał
 */
interface RESTful_IResponseRenderer
{
	static public function render($input);
}
