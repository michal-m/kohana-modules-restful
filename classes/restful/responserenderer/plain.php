<?php defined('SYSPATH') or die('No direct script access.');

/**
 * PLAIN Data Response Renderer class for text/plain mime-type.
 *
 * @package		RESTful
 * @category	Renderers
 * @author		Michał Musiał
 * @copyright	(c) 2011 Michał Musiał
 */
class RESTful_ResponseRenderer_PLAIN implements RESTful_IResponseRenderer
{
	/**
	 * @param mixed $input
	 */
	static public function render($input)
	{
		if (is_object($input) AND ! method_exists($input, '__toString'))
			return 'Object of ' . get_class($input) . ' class';
		else
			return (string) $input;
	}
}
