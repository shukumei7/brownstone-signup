<?php 
	if(isset($_GET['success'])) {
		require_once APP.'success'.CTP;
	} else if(isset($_GET['failed'])) {
		require_once APP.'error'.CTP;
	} else if(empty($events)) { 
		require_once APP.'noevents'.CTP;
	} else if(empty(MahoForm::$data)) {
		require_once APP.'signup'.CTP;
	}
?>