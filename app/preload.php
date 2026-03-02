<?php
// debug('Access check'); exit;
define('LOG_FILE', '~/logs/signup.log');

function debuglog($data) {
  file_put_contents(LOG_FILE, var_export($data, true), FILE_APPEND);
}

// Load .env
$env_file = dirname(__DIR__).'/.env';
if(file_exists($env_file)) {
    foreach(file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if(strpos(trim($line), '#') === 0) continue;
        if(strpos($line, '=') === false) continue;
        [$key, $val] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($val);
    }
}

MahoAPI::start($_ENV['MAHO_URL'], $_ENV['MAHO_USERNAME'], $_ENV['MAHO_API_KEY']);
$filter_id = $_ENV['MAHO_FILTER_ID'];

// $filter_id = 24632402;

if(!empty($_GET['failed'])) {
    
    return;
    
}

if(!empty($_GET['success'])) {
    
    $data = MahoAPI::call('search', array('uuid='.$_GET['success'], 'EventSignup'));
    
    if(empty($data['data']['results'])) {
        header('Location: '.url());
        return;
    }
    
    $data = MahoAPI::call('view', array(current($data['data']['results']), 'event'));
    $record = new MahoRecord($data['data']['entry']);
    $event_ids = $record->detail('EventSignup.event');
    
    $hasEvent = $hasMaterial = false;
    
    foreach($event_ids as $event_id) {
        $data = MahoAPI::call('view', array($event_id, 'category'));
        $record = new MahoRecord($data['data']['entry']);
        $category = $record->detail('Event.category');
        $matched = preg_match('/^materials\b/i', $category);
        
        $hasMaterial = $hasMaterial || $matched;
        $hasEvent = $hasEvent || !$matched;
        
        if($hasEvent && $hasMaterial) {
            break;
        }
    }
    
    
    
    return;
    
}

if(@$_POST['data'] && MahoForm::$data = $_POST['data']) {    
    $data = MahoAPI::call('new/EventSignup', array(), $_POST['data']);
// debuglog($data);
    $signup = MahoAPI::call('view', array($data['entry_id'], 'uuid'));
    $record = new MahoRecord($signup['data']['entry']);
// debuglog($signup);
    header('Location: '.url().'?success='.$record->detail('uuid'));
    return;
    
}

$select = $events = array();

if(!empty($data = MahoAPI::call('browse/Event', array('?' => array('filter' => $filter_id, 'limit' => 10, 'sort' => 'Event.start_date', 'direction' => 'asc')))) && !empty($data['data']['entries'])) {
// @$_GET['debug'] && debug($data);	
	$details = $data['data']['data'];

	foreach($details['Event.name'] as $entry_id => $name) {
	    
	    $isMaterial = preg_match('/^materials\b/i', $details['Event.category'][$entry_id]);
	    
		$events[$entry_id] = array(
			'name'	=> addslashes($name),
			'description'    => addslashes(@$details['Event.description'][$entry_id] ?: ''),
		    	'schedule'	     => $dates = $isMaterial ? 'Reference Material' : strip_tags($details['Event.schedule'][$entry_id]),
		    	'venue'		     => $isMaterial ? 'A link will be sent to your e-mail address' : $details['Event.venue'][$entry_id],
			'category'	     => $details['Event.category'][$entry_id]
		);
		$select[$entry_id] = $name.' - '.$dates;
	}
// @$_GET['debug'] && debug(compact('data', 'details', 'events'));	
}

if(empty($events)) {    
    $page_title = 'Sorry!';
    return;
    
}

MahoForm::setSelect('EventSignup.selected_event', $select);

$data = MahoAPI::call('new/EventSignup');
$page_title = 'Event Sign Up';
