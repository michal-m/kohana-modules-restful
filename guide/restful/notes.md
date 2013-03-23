# Important Notes

While the previous pages might have explained how to use this module there's a
couple of things you might want to bear in mind while developing your RESTful
API.

## Use of `before()`

[!!] If your controller has its own `before()` method defined, always **make
sure it calls `parent::before()`**.

## Request method override

If you want to allow API clients to override HTTP method you can implement it in
a couple of ways. I would recommend this is done inside a [Route Filter](config#route-filter),
but it should work just as well inside [`before()`](#controllerbefore) method.

### X-HTTP-Method-Override header

This is a widely accepted solution (even by [Google](https://developers.google.com/gdata/docs/2.0/basics#DeletingEntry)).
The way it works is your client supplies additional header called
`X-HTTP-Method-Override` with appropriate method as value, e.g.:

    X-HTTP-Method-Override: PUT

For your Controller to understand this your API [Route Filter](config#route-filter)
should look like this:

    ->filter(function ($route, $params, $request){
        // Override method if appropriate header provided
        if ($method_override = $request->headers('X-HTTP-Method-Override'))
        {
            $request->method($method_override);
        }

        // ... the rest of your filter function code

        return $params;
    })


### Request parameter

Another way to override the Request method is to do it via request parameter.
What you basically do is supply additional request variable that contains the
method, e.g. your client calls `http://api.example.com/resource/113?method=DELETE`.
Then, your [Route Filter](config#route-filter) might look like this:

    ->filter(function ($route, $params, $request){
        // Override method if appropriate header provided
        if ($method_override = $request->query('method'))
        {
            $request->method($method_override);
        }

        // ... the rest of your filter function code

        return $params;
    })


[!!] This method is particularly useful if your API is accessed via browsers which
by default send `GET` method.


### Controller::before()

Instead of using a [Route Filter](config#route-filter) you can always override
the request method inside your controller's `before()` method. This might be
useful if you want to have the control over it in one particular controller
rather than all of them. Your example `before()` method might look like this:

    public function before()
    {
        // Override method if appropriate header provided
        if ($method_override = $request->headers('X-HTTP-Method-Override'))
        {
            $request->method($method_override);
        }

        parent::before();
    }

[!!] If you choose this way to override the request method make sure you do it
before calling `parent::before()`.


## Default response renderer

It is also worth explaining how client requests are handled, when the `Accept`
header value contains wildcards and matches multiple registered ones.

**Always** the first one is matched. This means that if the request header is:

    Accept: application/*

You registered renderes in this order:

1. application/json
2. application/javascript
3. application/example

Then the first one will be matched and used by default.
