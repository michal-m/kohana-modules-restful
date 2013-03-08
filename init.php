<?php defined('SYSPATH') or die('No direct script access.');

RESTful_Request::parser('application/json',                         'RESTful_Request_Parser::application_json');
RESTful_Request::parser('application/php-serialized',               'RESTful_Request_Parser::application_php_serialized');
RESTful_Request::parser('application/x-www-form-urlencoded',        'RESTful_Request_Parser::application_x_www_form_urlencoded');
RESTful_Request::parser('text/plain',                               'RESTful_Request_Parser::text_plain');

RESTful_Response::renderer('application/json',                     'RESTful_Response_Renderer::application_json');
RESTful_Response::renderer('application/php-serialized',           'RESTful_Response_Renderer::application_php_serialized');
RESTful_Response::renderer('application/php-serialized-array',     'RESTful_Response_Renderer::application_php_serialized_array');
RESTful_Response::renderer('application/php-serialized-object',    'RESTful_Response_Renderer::application_php_serialized_object');
RESTful_Response::renderer('text/php-printr',                      'RESTful_Response_Renderer::text_php_printr');
RESTful_Response::renderer('text/plain',                           'RESTful_Response_Renderer::text_plain');
