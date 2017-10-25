<?php

define('INC_ROOT', dirname(__DIR__));

//Include Configuration

require_once(INC_ROOT.'/config/config.php');


ini_set('display_errors', '0'); //We need to set display_errors to off because the mfinante.ro website is full of markup errors which make our DOM parser throw a ton of warnings (which we don't want polluting our json file)

//Autoload Classes
function __autoload($class_name){
	require_once(INC_ROOT.'/lib/'.$class_name . '.php');
}