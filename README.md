# PHPRestAPI
Skeleton for a PHP Rest API

Inspired by http://coreymaynard.com/blog/creating-a-restful-api-with-php

## A Simple Example

Create a class inheriting PHPRestAPI:
```php
require('PHPRestAPI.class.php');

class ExampleAPI extends PHPRestAPI {

   private $movies = array(
      'Jurassic Park' => array(1993, '127min'),
      'Armageddon' => array(1998, '150min'),
      'The Abyss' => array(1989, '145min')
   );

   public function __construct($request) {
      parent::__construct($request);

      $this->_registerRoute('GET', 'movies', 'GET_movies');
      $this->_registerRoute('GET', 'movies/*', 'GET_movies_by_title');

   }

   protected function GET_movies() {
      return array_keys($this->movies);
   }

   protected function GET_movies_by_title() {
      if (array_key_exists($this->args[0], $this->movies)) {
         return $this->movies[$this->args[0]];
      }
      return 'Movie not in stock.';
   }

}
```

And index.php could look like:
```php
require('ExampleAPI.php');

$_REQUEST['request'] = isset($_REQUEST['request']) ? $_REQUEST['request'] : '';

try {
   $API = new ExampleAPI($_REQUEST['request']);
   echo $API->processAPI();
} catch (Exception $e) {
   echo json_encode(Array('error' => $e->getMessage()));
}
```
