<?php

require_once LIB.'MahoAPI'.PHP;
require_once LIB.'MahoForm'.PHP;
require_once LIB.'MahoRecord'.PHP;
	
if(!function_exists('debug')) {
	function debug($expression) {
		
		$content = var_export($expression, true);
		
		$lines = preg_split('/\n|\r\n?/', $content);
		
		$tabs = $output = '';
		
		$spaces = '   ';
		
		foreach($lines as $line) {
			
			$line = trim($line);
			
			switch($line) {
				case ')';
				case '),':
					$tabs = substr($tabs, 0, strlen($tabs) - strlen($spaces));
					break;
			}
			
			$output .= $tabs.$line."\r\n";
			
			switch($line) {
				case 'array (':
					$tabs .= $spaces;
					break;
			}
			
		}
		
		echo '<pre style="background:lightgray">'.$output.'</pre>';
		
	}
}

function url(){
	return sprintf(
		"%s://%s%s",
		isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
		$_SERVER['SERVER_NAME'],
		(@substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '/', 1)) ?: '')
	);
}


?>