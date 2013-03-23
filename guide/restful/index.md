# About RESTful module

@TODO Introduction


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

@TODO link to "how to access parsed request data"


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

@TODO link to "how to render data for output"
