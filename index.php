<?php
require_once(dirname(__FILE__) . '/config/config.php');
// Set the auto loader
$autoloader = new Config();

// Load helper class
$helper = new Helpers();

// Load Json class
$jsonObject = new Json();

$json = json_decode($jsonObject->json);

$helper->show($json);




?>