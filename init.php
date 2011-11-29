<?php defined('SYSPATH') or die('No direct script access.');

RESTful_RequestParser::register('application/json',						'RESTful_RequestParser_JSON::parse');
RESTful_RequestParser::register('application/php-serialized',			'RESTful_RequestParser_PHP::parse');
RESTful_RequestParser::register('application/x-www-form-urlencoded',	'RESTful_RequestParser_URLENC::parse');
RESTful_RequestParser::register('text/plain',							'RESTful_RequestParser_PLAIN::parse');

RESTful_ResponseRenderer::register('application/json',					'RESTful_ResponseRenderer_JSON::render');
RESTful_ResponseRenderer::register('application/php-serialized',		'RESTful_ResponseRenderer_PHP::render');
RESTful_ResponseRenderer::register('application/php-serialized-array',	'RESTful_ResponseRenderer_PHP_Array::render');
RESTful_ResponseRenderer::register('application/php-serialized-object',	'RESTful_ResponseRenderer_PHP_Object::render');
RESTful_ResponseRenderer::register('text/php-printr',					'RESTful_ResponseRenderer_PRINTR::render');
RESTful_ResponseRenderer::register('text/plain',						'RESTful_ResponseRenderer_PLAIN::render');