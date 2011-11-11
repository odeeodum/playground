<?php
class Helpers {
	function __construct()
	{
		
	}
	
	public function show($val,$exit=false,$varDump=false)
	{
		echo "<pre>";
		if($varDump) var_dump($val);
		else print_r($val);
		echo "<pre>";
		if($exit) exit();
	}
}
?>