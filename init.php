<?php defined('SYSPATH') or die('No direct script access.');

RESTful_Request::parser('application/json',                         'RESTful_Request_Parser::application_json');
RESTful_Request::parser('application/php-serialized',               'RESTful_Request_Parser::application_php_serialized');
RESTful_Request::parser('application/x-www-form-urlencoded',        'RESTful_Request_Parser::application_x_www_form_urlencoded');
RESTful_Request::parser('text/plain',                               'RESTful_Request_Parser::text_plain');

RESTful_Response::renderer('application/json',                     'RESTful_Response_Renderer_JSON::render');
RESTful_Response::renderer('application/php-serialized',           'RESTful_Response_Renderer_PHP::render');
RESTful_Response::renderer('application/php-serialized-array',     'RESTful_Response_Renderer_PHP_Array::render');
RESTful_Response::renderer('application/php-serialized-object',    'RESTful_Response_Renderer_PHP_Object::render');
RESTful_Response::renderer('text/php-printr',                      'RESTful_Response_Renderer_PRINTR::render');
RESTful_Response::renderer('text/plain',                           'RESTful_Response_Renderer_PLAIN::render');
