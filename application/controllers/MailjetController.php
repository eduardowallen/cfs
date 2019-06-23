<?php

require 'vendor/autoload.php';

use \Mailjet\Resources;

// getenv will allow us to get the MJ_APIKEY_PUBLIC/PRIVATE variables we created before
$apikey = getenv('MJ_APIKEY_PUBLIC');
$apisecret = getenv('MJ_APIKEY_PRIVATE');

// use your saved credentials
$mj = new \Mailjet\Client(getenv('MJ_APIKEY_PUBLIC'), getenv('MJ_APIKEY_PRIVATE'), true, ['version' => 'v3.1']);

// Resources are all located in the Resources class
$response = $mj->get(Resources::$Contact);

/*
  Read the response
*/
if ($response->success())
  var_dump($response->getData());
else
  var_dump($response->getStatus());