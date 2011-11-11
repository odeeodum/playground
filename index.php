<?php
	require_once (dirname(__FILE__) . '/config/config.php');
	
	// Set the auto loader and open channel to config functions
	$config = new Config();
	$db = $config->database();

	// Load helper class
	$helper = new Helpers();

	// Load Json class
	$jsonObject = new Json();

	$json = json_decode($jsonObject->json);
	
	processJSON($json);

	$helper->show($json);
	
	function processJSON()
	{
		
	}
?>