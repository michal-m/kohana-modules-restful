# Real world example

Here is a "real world" examples of using the RESTful module.

Let's assume the application runs on it's own domain, e.g. `http://api.example.com/`,
and we're running a library where we can manage books stock.

[!!] **SECURITY NOTE**
For the sake of simplicity in this example we're not doing any input
validation. This is a serious security risk you should make sure you do it in
your applications.


## Files

### bootstrap.php (route)

    // Default API Route
    Route::set('default', '<resource>(/<id>(/<property>))')
        ->filter('RESTful::route_filter');

We'll be accepting following URLs:

- To get a list of all books:  
    `http://api.example.com/books/`
- To get details of a single book:  
    `http://api.example.com/books/1`
- To get a property of a single book:  
    `http://api.example.com/books/1/title`

### classes/Controller/API/Books.php

    <?php defined('SYSPATH') or die('No direct script access.');

    class Controller_Books extends RESTful_Controller {

        /**
         * Retreives Books and their details
         */
        public function action_get()
        {
            if ($id = $this->request->param('id'))
            {
                $book = ORM::factory('Book', $id);

                if ( ! $book->loaded())
                    throw HTTP_Exception::factory(404);

                if ($property = $this->request->param('property'))
                {
                    /**
                     * If the property doesn't exist ORM will throw an exception.
                     * We want to catch it and throw the 404 HTTP Exception.
                     */
                    try
                    {
                        $property_value = $book->$property;
                    }
                    catch (Exception $e)
                    {
                        throw HTTP_Exception::factory(404);
                    }

                    $response_data = $property_value;
                }
                else
                {
                    $response_data = $book->as_array();
                }
            }
            else
            {
                $books = ORM::factory('Book')->find_all()->as_array();

                foreach ($books as $book)
                {
                    $response_data[] = $book->as_array();
                }
            }

            $this->response->body(RESTful_Response::render($response_data));
        }

        /**
         * Updated Books and their details
         */
        public function action_update()
        {
            /**
             * We don't want client's to update all the books at the same time, so
             * we're throwing a 405 Method Not Allowed.
             */
            if ( ! $id = $this->request->param('id'))
                throw HTTP_Exception::factory(405);

            // Initiate Object
            $book = ORM::factory('Book', $id);

            if ( ! $book->loaded())
                throw HTTP_Exception::factory(404);

            /**
             * Request contained a property name in the URL so we're going to update
             * just the one.
             */
            if ($property = $this->request->param($property))
            {
                try
                {
                    $book->$property = $this->request_data();
                }
                catch (Exception $e)
                {
                    echo $exc->getTraceAsString();
                }
            }
            /**
             * We're expecting array of properties here
             */
            elseif (is_array($this->_request_data))
            {
                $properties_allowed = array_keys($book->table_columns());

                foreach ($this->_request_data as $key => $value)
                {
                    if ( ! in_array($key, $properties_allowed))
                        throw HTTP_Exception::factory(400, 'Request body contains invalid property: :property', array(':property' => $key));

                    $book->$key = $value;
                }
            }
            else
                throw HTTP_Exception::factory(400, 'Invalid content type, expected array.');

            if ( ! $book->update()->saved())
                throw HTTP_Exception::factory(500);

            $this->response->status(204);
        }
    }


### classes/Model/Book.php

    <?php defined('SYSPATH') or die('No direct script access.');

    class Model_Book extends ORM {

        /**
         * Table columns
         * @var array
         */
        protected $_table_columns = array(
            'id'            => NULL,
            'title'         => NULL,
            'author'        => NULL,
            'isbn'          => NULL,
            'date_added'    => NULL,
            'price'         => NULL,
            'in_stock'      => NULL,
        );
    }


## Request examples


### GET - Get list of all books

We'll try to retreive a list of books.

#### Request


#### Response


### GET - Get book's property

Now let's try and get a book's property to see if it is currently in stock.

#### Request


#### Response


### PUT - Update a book

We're gonna update the `in_stock` property.

#### Request


#### Response

