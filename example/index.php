<?php

/**
 * index.php
 * 
 * Index file for Example API using PHPRestAPI skeleton.
 * @author Simon Johansson <sijohans@kth.se>
 * @version 1.0.0
 * @package PHPRestAPI
 *
 */

require('ExampleAPI.php');

$_REQUEST['request'] = isset($_REQUEST['request']) ? $_REQUEST['request'] : '';

try {
   $API = new ExampleAPI($_REQUEST['request']);
   echo $API->processAPI();
} catch (Exception $e) {
   echo json_encode(Array('error' => $e->getMessage()));
}
