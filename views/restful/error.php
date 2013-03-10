<?php
/**
 * Sorry about the logic within this template but I couldn't figure out how to
 * handle it any other way.
 */
if (HTTP_Exception::$error_view_content_type === 'text/plain')
{
    echo $message;
    return;
}

$error_data = array(
    'code'      => ($e instanceof HTTP_Exception) ? $e->getCode() : 500,
    'message'   => $message,
);

if (Kohana::$environment === Kohana::DEVELOPMENT)
{
    $error_data['debug'] = array(
        'file'  => $file,
        'line'  => $line,
        'trace' => $trace,
    );
}

echo RESTful_Response::render($error_data, HTTP_Exception::$error_view_content_type);
