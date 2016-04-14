<?php

/**
 * ExampleAPI.php
 * 
 * Example API using PHPRestAPI skeleton.
 * @author Simon Johansson <sijohans@kth.se>
 * @version 1.0.0
 * @package PHPRestAPI
 *
 *
 */

require('../PHPRestAPI.class.php');

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
