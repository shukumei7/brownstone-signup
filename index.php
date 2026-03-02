<?php 

	define('DS', '/');
	define('ROOT', '.');
	define('PHP', '.php');
	define('CTP', '.ctp');
	define('APP', ROOT.DS.'app'.DS);
	define('LIB', ROOT.DS.'lib'.DS);
	define('SRC', ROOT.DS.'src'.DS);
	
	require_once LIB.'libraries'.PHP;
	
	require_once APP.'preload'.PHP;
	
	require_once SRC.'html'.CTP;
?>

