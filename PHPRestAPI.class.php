<?php

/**
 * PHPRestApi.class.php
 * 
 * Abstract class for a PHP Rest API.
 * @author Simon Johansson <sijohans@kth.se>
 * @version 1.0.0
 * @package PHPRestAPI
 *
 * Inspired by http://coreymaynard.com/blog/creating-a-restful-api-with-php/
 *
 */

abstract class PHPRestAPI {

   /* Private members */
   private $routes = array();

   /* Protected members */
   protected $request = null;
   protected $method = null;
   protected $data = null;
   protected $args = array();
   protected $files = array();

   public function __construct($request) {

      header('Access-Control-Allow-Origin: *');
      header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT');
      header('Content-Type: application/json');

      $this->request = $request;

      $this->method = $_SERVER['REQUEST_METHOD'];
      if ($this->method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
         if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
            $this->method = 'DELETE';
         } else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
            $this->method = 'PUT';
         } else {
            throw new Exception('Unexpected Header');
         }
      }

      switch($this->method) {
         case 'DELETE':
         case 'POST':
            $this->files = $_FILES;
            $this->data = json_decode(file_get_contents('php://input'), true);
            break;
         case 'GET':
            break;
         case 'PUT':
            $this->data = json_decode(file_get_contents('php://input'), true);
            break;
         case 'OPTIONS':
            header('Access-Control-Allow-Headers: Content-Type');
            die();
         default:
            $this->response('Invalid Method', 405);
            break;
      }

   }

   /* Public methods */

   public function processAPI() {
      foreach ($this->routes as $route) {
         preg_match_all($route[1], $this->request, $this->args, PREG_SET_ORDER);
         if (count($this->args) > 0 && $this->method == $route[0]) {
            $this->args = $this->args[0];
            array_splice($this->args, 0, 1);
            if (method_exists($this, $route[2])) {
               try {
                  return $this->response($this->{$route[2]}());
               } catch (PHPRestAPIException $e) {
                  $code = ($e->getCode() != 0) ? $e->getCode() : 400;
                  return $this->response(array(
                     'message' => $e->getMessage(),
                     'reasons' => $e->getErrors()
                  ), $code);
               } catch (Exception $e) {
                  $code = ($e->getCode() != 0) ? $e->getCode() : 400;
                  return $this->response($e->getMessage(), $code);
               }
            }
         }
      }
      return $this->response(
         sprintf('No Endpoint: %s for method %s',
            $this->request,
            $this->method),
         404
      );
   }

   /* Protected methods */

   protected function _registerRoute($method, $pattern, $callback, $simplified = True) {
      /*
       * Use a simplified pattern when registering routes, make real
       * regular expressions here. But also allow real regex patterns.
       */
      if ($simplified) {
         $replace = array(
            array(
               '/',
               '*',
               '[int]'
            ),
            array(
               '\/',
               '(.*)',
               '([1-9][0-9]*)'
            )
         );

         $pattern = '/^'.str_replace($replace[0], $replace[1], $pattern).'$/';
      }

      array_push($this->routes, array($method, $pattern, $callback));

   }

   protected function response($data, $status = 200) {
      header('HTTP/1.1 ' . $status . ' ' . $this->_requestStatus($status));
      return json_encode($data);
   }

   private function _requestStatus($code) {
      $status = array(  
         100 => 'Continue',
         101 => 'Switching Protocols',
         102 => 'Processing',
         200 => 'OK',
         201 => 'Created',
         202 => 'Accepted',
         203 => 'Non-authoritative Information',
         204 => 'No Content',
         205 => 'Reset Content',
         206 => 'Partial Content',
         207 => 'Multi-Status',
         208 => 'Already Reported',
         226 => 'IM Used',
         300 => 'Multiple Choices',
         301 => 'Moved Permanently',
         302 => 'Found',
         303 => 'See Other',
         304 => 'Not Modified',
         305 => 'Use Proxy',
         307 => 'Temporary Redirect',
         308 => 'Permanent Redirect',
         400 => 'Bad Request',
         401 => 'Unauthorized',
         402 => 'Payment Required',
         403 => 'Forbidden',
         404 => 'Not Found',
         405 => 'Method Not Allowed',
         406 => 'Not Acceptable',
         407 => 'Proxy Authentication Required',
         408 => 'Request Timeout',
         409 => 'Conflict',
         410 => 'Gone',
         411 => 'Length Required',
         412 => 'Precondition Failed',
         413 => 'Payload Too Large',
         414 => 'Request-URI Too Long',
         415 => 'Unsupported Media Type',
         416 => 'Requested Range Not Satisfiable',
         417 => 'Expectation Failed',
         418 => 'I\'m a teapot',
         421 => 'Misdirected Request',
         422 => 'Unprocessable Entity',
         423 => 'Locked',
         424 => 'Failed Dependency',
         426 => 'Upgrade Required',
         428 => 'Precondition Required',
         429 => 'Too Many Requests',
         431 => 'Request Header Fields Too Large',
         451 => 'Unavailable For Legal Reasons',
         499 => 'Client Closed Request',
         500 => 'Internal Server Error',
         501 => 'Not Implemented',
         502 => 'Bad Gateway',
         503 => 'Service Unavailable',
         504 => 'Gateway Timeout',
         505 => 'HTTP Version Not Supported',
         506 => 'Variant Also Negotiates',
         507 => 'Insufficient Storage',
         508 => 'Loop Detected',
         510 => 'Not Extended',
         511 => 'Network Authentication Required',
         599 => 'Network Connect Timeout Error'
      ); 
      return (isset($status[$code])) ? $status[$code] : $status[500]; 
   }

}

class PHPRestAPIException extends Exception {

   private $errors;

   public function __construct($message, $errors, $code = 404) {
      parent::__construct($message, $code);
      $this->errors = $errors;
   }

   public function getErrors() {
      return $this->errors;
   }

}
