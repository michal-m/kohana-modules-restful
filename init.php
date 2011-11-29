<?php defined('SYSPATH') or die('No direct script access.');

RESTful::register_parser('application/json',					'RESTful_RequestParser_JSON::parse');
RESTful::register_parser('application/php-serialized',			'RESTful_RequestParser_PHP::parse');
RESTful::register_parser('application/x-www-form-urlencoded',	'RESTful_RequestParser_URLENC::parse');
RESTful::register_parser('text/plain',							'RESTful_RequestParser_PLAIN::parse');

RESTful::register_renderer('application/json',					'RESTful_ResponseRenderer_JSON::render');
RESTful::register_renderer('application/php-serialized',		'RESTful_ResponseRenderer_PHP::render');
RESTful::register_renderer('text/php-printr',					'RESTful_ResponseRenderer_PRINTR::render');
RESTful::register_renderer('text/plain',						'RESTful_ResponseRenderer_PLAIN::render');