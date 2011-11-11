<?php
require_once('settings.php');

class Config {
	private $debug;
	
	public function __construct($debug=false)
	{
		spl_autoload_register(array($this,'autoload'));
		if($debug) $this->debug = $debug;
	}
	
	private function autoload($className)
	{
		if ($this->debug) $info = 'Trying to load <span style="font-weight:bold;">' . $className . '</span> via ' . __METHOD__ . "() <br />";
		
		if (class_exists($className,false) OR interface_exists($className,false))
		{
			return false;
		}
		
		$class = "class/" . strtolower($className) . '.php';
		
		if ($this->debug) $info .= "The path to the attempted class is: '" . $class . "'";
		
		if ($this->debug) echo $info;
		
		if (file_exists($class))
		{
			require_once $class;
			return true;
		}
		return false;
	}
	
	public function database()
	{
		$mysqli = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_DTBS);
		
		if (mysqli_connect_error())
		{
			die('Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
		}
		
		return $mysqli;
	}
}
?>