<?php defined('SYSPATH') or die('No direct script access.');
/**
 * @package     RESTful
 * @author      Michał Musiał
 * @copyright   (c) 2013 Michał Musiał
 */
class RESTful_Core {

    // Release version
    const VERSION = '2.0.0-beta3';

    /**
     * Filters RESTful Route. Sets <controller> as a <resource> name and maps
     * Request method with appropriate action name.
     *
     * @param   Route   $route
     * @param   array   $params
     * @param   Request $request
     * @return  array
     */
    public static function route_filter($route, $params, $request)
    {
        // Override method if appropriate header provided
        if ($method_override = $request->headers('X-HTTP-Method-Override'))
        {
            $request->method($method_override);
        }

        $params['resource'] = $params['controller'];
        $params['action'] = RESTful_Controller::$action_map[strtoupper($request->method())];

        return $params;
    }
}
