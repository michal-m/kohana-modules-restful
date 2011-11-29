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

	/**
	 * Inline exception handler, displays the error message, source of the
	 * exception, and the stack trace of the error.
	 *
	 * @uses    Kohana_Exception::text
	 * @param   object   exception object
	 * @return  boolean
	 */
	public static function exception_handler(Exception $e)
	{
		try
		{
			// Get the exception information
			$type    = get_class($e);
			$code    = $e->getCode();
			$message = $e->getMessage();
			$file    = $e->getFile();
			$line    = $e->getLine();

			// Create a text version of the exception
			$error = Kohana_Exception::text($e);

			if (is_object(Kohana::$log))
			{
				// Add this exception to the log
				Kohana::$log->add(Log::ERROR, $error);

				// Make sure the logs are written
				Kohana::$log->write();
			}

			if (Kohana::$is_cli)
			{
				// Just display the text of the exception
				echo "\n{$error}\n";

				return TRUE;
			}

			if ($e instanceof ErrorException)
			{
				if (isset(Kohana_Exception::$php_errors[$code]))
				{
					// Use the human-readable error name
					$code = Kohana_Exception::$php_errors[$code];
				}
			}

			if ( ! headers_sent())
			{
				// Make sure the proper content type is sent with a 500 status
				header('Content-Type: text/plain; charset='.Kohana::$charset, TRUE, 500);
			}

			if (Kohana::$environment === Kohana::DEVELOPMENT)
			{
				echo $error;
			}

			return TRUE;
		}
		catch (Exception $e)
		{
			// Clean the output buffer if one exists
			ob_get_level() and ob_clean();

			// Display the exception text
			echo Kohana_Exception::text($e), "\n";

			// Exit with an error status
			// exit(1);
		}
	}


	/**
	 * Catches errors that are not caught by the error handler, such as E_PARSE.
	 *
	 * @uses    RESTful::exception_handler
	 * @return  void
	 */
	public static function shutdown_handler()
	{
		try
		{
			if (Kohana::$caching === TRUE AND Kohana::$_files_changed === TRUE)
			{
				// Write the file path cache
				Kohana::cache('Kohana::find_file()', Kohana::$_files);
			}
		}
		catch (Exception $e)
		{
			// Pass the exception to the handler
			RESTful::exception_handler($e);
		}

		if (Kohana::$errors AND $error = error_get_last() AND in_array($error['type'], Kohana::$shutdown_errors))
		{
			// Clean the output buffer
			ob_get_level() and ob_clean();

			// Fake an exception for nice debugging
			RESTful::exception_handler(new ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));

			// Shutdown now to avoid a "death loop"
			exit(1);
		}
	}
}