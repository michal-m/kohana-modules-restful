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


### Alternative ways

If you don't want to use the included [RESTful::route_filter] or you for some
reason you don't want to use routes here are examples of how you can do it:


#### Route & Filter

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


#### Controller::before()

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


## Request parsers and response renderers

Any API needs to either be able to return content, accept content or do both at
the same time. In order to handle any input/output the module requires a [Request Body Parser](#request-body-parser)
and/or [Response Body Renderer](#response-body-renderer). This module comes with
a set of basic parsers and renderers, but you are able to create and use your
own scripts to handle these.


### Request Body Parser

If a request contains any body (only POST and PUT requests should) it has to be
interpreted by the request handler, in this case our RESTful application. It is
not able to do it without knowing what format is the data in, therefore each
POST/PUT request has to contain a `Content-Type` header which describes the
request body format.

This module automatically reads the `Content-Type` header and runs appripriate
parser through the request body (when necessary). Once the request body is
parsed it is saved in [RESTful_Controller::$_request_data] and can be retreived
by [RESTful_Controller::request_data].

[!!] POST, GET and FILES can still be retreived the standard way.

The module contains following predefined parsers:

Mime Type                         | Handled by
----------------------------------|------------------------------------------------------------
application/json                  | [RESTful_Request_Parser::application_json]
application/php-serialized        | [RESTful_Request_Parser::application_php_serialized]
application/x-www-form-urlencoded | [RESTful_Request_Parser::application_x_www_form_urlencoded]
text/plain                        | [RESTful_Request_Parser::text_plain]

[!!] If the `Content-Type` header contains the content charset definition it is
ignored, i.e. `application/json` and `application/json; charset=utf-8` are
treated as the same.


#### Creating your own

If you want to create your own request parser it has to accept one parameter
which will contain the request body to be parsed. Example function:

    function my_json_parser($request_body)
    {
        return json_decode($request_body, TRUE, 512, JSON_BIGINT_AS_STRING);
    }

Now all you need to do is register it with the [RESTful_Request::parser]:

    RESTful_Request::parser('application/json', 'my_json_parser');

[!!] Note that this any request parsers have to be set before
[RESTful_Controller::before] is executed.

Because Request Parsers are ignoring the charset definition in the
`Content-Type` header there is no need to define parsers depending on
Content-Type charsets.

    RESTful_Request::parser('application/json', 'my_json_parser');
    // The below is obsolete:
    RESTful_Request::parser('application/json; charset=utf-8', 'my_json_parser');



### Response Body Renderer


#### Creating your own


### Overriding existing

You are able to override existing parsers/renderers. To do it, you simply
register it with the same mime type and the previous one will be returned, e.g.

    $previous_parser = RESTful_Request::parser('application/json', 'my_json_parser');
