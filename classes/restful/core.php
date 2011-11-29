<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @package    RESTful
 * @author     Michał Musiał
 * @copyright  (c) 2011 Michał Musiał
 */
class RESTful_Core
{
	/**
	 * @var array
	 */
	protected static $_parsers = array();
	
	/**
	 * @var array
	 */
	protected static $_renderers = array();
	
	
	
	/**
	 * @param string $type Content MIME type
	 * @param callback $callback
	 * @return mixed Returns previous parser if one existed or TRUE otherwise
	 */
	public static function register_parser($type, $callback)
	{
		if (array_key_exists($type, self::$_parsers))
		{
			$return = self::$_parsers[$type];
		}
		else
		{
			$return = TRUE;
		}
		
		self::$_parsers[$type] = $callback;
		return $return;
	}
	
	/**
	 * @param string $type Content MIME type
	 * @param callback $callback
	 * @return mixed Returns previous renderer if one existed or TRUE otherwise
	 */
	public static function register_renderer($type, $callback)
	{
		if (array_key_exists($type, self::$_renderers))
		{
			$return = self::$_renderers[$type];
		}
		else
		{
			$return = TRUE;
		}
		
		self::$_renderers[$type] = $callback;
		return $return;
	}
	
	/**
	 * @param string $type
	 * @return mixed Returns all parsers if $type not specified, a parser callback if found or boolean FALSE otherwise
	 */
	public static function parsers($type = NULL)
	{
		if ($type === NULL)
		{
			return self::$_parsers;
		}
		else
		{
			return (array_key_exists($type, self::$_parsers)) ? self::$_parsers[$type] : FALSE;
		}
	}

	/**
	 * @param string $type
	 * @return mixed Returns all renderers if $type not specified, a parser callback if found or boolean FALSE otherwise
	 */
	public static function renderers($type = NULL)
	{
		if ($type === NULL)
		{
			return self::$_renderers;
		}
		else
		{
			return (array_key_exists($type, self::$_renderers)) ? self::$_renderers[$type] : FALSE;
		}
	}
}