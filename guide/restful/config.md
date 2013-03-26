# Configuration

The module does not require any Kohana-style configuration, but if you want to
add additional Request Parsers or Response Renderes you have to define them
using [RESTful_Request::parser] or [RESTful_Respose::renderer] methods before
the [RESTful_Controller::before()] method is executed. Examples:

1. Your controller's `__construct()`

        public function __construct()
        {
            parent::__construct();
            RESTful_Request::parser('my/mime-type', 'my_mime_type_parser_callback');
        }

2. Your controller's `before()` method body

        public function before()
        {
            RESTful_Request::parser('my/mime-type', 'my_mime_type_parser_callback');
            parent::before();
        }



3. Module's `init.php`

    Alternatively, you can define additional parsers/renderes in the module's
    `init.php` file.

@TODO You can read more about how to create additional parsers and renderers here.
