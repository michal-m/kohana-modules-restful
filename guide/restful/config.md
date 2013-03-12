# Configuration

The module does not require any Kohana-style configuration, but in order to
handle various request methods (GET/POST/PUT/DELETE) your are required to
translate them into controller action names.


## Request routing

This module comes with a predefined [RESTful::route_filter] method that you can
use as your route filter:

    Route::set('api', 'api/<resource>(/<id>(/<property>))')
        ->filter('RESTful::route_filter')
        ->defaults(array(
            'directory' => 'api',
        ));

[!!] Note that the Route uses doesn't contain `<controller>` defined. This is
because `<resource>` is more meaningful, but it is translated to the controller
in the filter.

The [RESTful::route_filter] translates request methods based on [RESTful_Controller::$action_map]
as following (*note different names for POST and PUT methods*):

Request method | Action name | Controller method name
---------------|-------------|-----------------------
GET            | get         | action_get
POST           | update      | action_update
PUT            | create      | action_create
DELETE         | delete      | action_delete

The supplied Route Filter method also caters for request method override using
[`X-HTTP-Method-Override` header](notes#x-http-method-override-header).


## Alternative ways

If you don't want to use the included [RESTful::route_filter] or you for some
reason you don't want to use routes here are examples of how you can do it:


### Route & Filter

The recommended way is to create an API route filter that translates request
into `<controller>` and `<action>`. An example Route:

    Route::set('api', 'api/<controller>(/<id>(/<property>))')
        ->filter(function ($route, $params, $request) {
            $params['action'] = strtolower($request->method());
            return $params;
        })
        ->defaults(array(
            'directory'  => 'api',
        ));


### Controller::before()

Alternatively the translation can also be accomplished in the `before()` method
of your Controller.

Example:

    public function before()
    {
        $this->action(strtolower($this->method()));
        parent::before();
    }

[!!] Make sure you call `parent::before()` after you set the action value.


Both of the above approaches translate the requests methods as following:

Request method | Action name | Controller method name
---------------|-------------|-----------------------
GET            | get         | action_get
POST           | post        | action_post
PUT            | put         | action_put
DELETE         | delete      | action_delete


