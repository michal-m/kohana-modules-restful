# About RESTful module

RESTful module provides a controller abstraction layer which allows creating
simple, yet powerful [RESTful Web Services](http://en.wikipedia.org/wiki/Representational_state_transfer#RESTful_web_services)
using Kohana framework. It attempts to follow the [HTTP 1.1 spec](http://www.w3.org/Protocols/rfc2616/rfc2616.html)
when it gets to handling [request methods](http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9)
and returning appropriate [response status codes](http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html#sec10).


## What the RESTful module does (and does not do)

@TODO


## Prefedined Request Parsers

The modules comes with a set of predefined Request Body Parsers, which can 
handle the following mime types:

 * `application/json` - [JSON](http://www.json.org/)
 * `application/php-serialized` - output of [`serialize()`](http://php.net/serialize)
 * `application/x-www-form-urlencoded` - [default post form data format](http://www.w3.org/TR/html401/interact/forms.html#adef-enctype)
 * `text/plain`

All of the above are defined in [RESTful_Request_Parser].

Read more:

 * How to access the request data? @TODO link
 * How to create own request parsers? @TODO link
 * How to add new request data parsers? @TODO link


## Predefined Response Renderes

The module also provides following Response Renderers:

 * `application/json` - [JSON](http://www.json.org/)
 * `application/php-serialized` - output of [`serialize()`](http://php.net/serialize)
 * `text/plain`

There are also additional, "helper", renderers:

 * `application/php-serialized-array` - output of [`serialize()`](http://php.net/serialize) with value type cast as an array
 * `application/php-serialized-object` - output of [`serialize()`](http://php.net/serialize) with value type cast as an object
 * `text/php-printr` - output of [`print_r()`](http://php.net/print_r) - useful for debugging.

All the predefined response renderers are located in [RESTful_Response_Renderer].

 * How to create own response renderers? @TODO link
 * How to add new response renderers? @TODO link
