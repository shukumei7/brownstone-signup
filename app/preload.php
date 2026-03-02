<?php
// debug('Access check'); exit;
define('LOG_FILE', '~/logs/signup.log');

function debuglog($data) {
  file_put_contents(LOG_FILE, var_export($data, true), FILE_APPEND);
}

$api = true;
$debug = false;

if($debug) {
	MahoAPI::start('http://localhost:8000', 'allan', '8d4267b1f0ff2c229997f5b6bfb63c986916a7db'); // local
	$filter_id = '17863104';
} else {
	MahoAPI::start('http://localhost:8082', 'batincsignup', 'c6f1d47e912d76d7ddd7aee6b4994ff6'); // live
	$filter_id = '60432566';
	
}

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

if($debug && !$api) {
	
	$events = array(
		123 => array(
			'name'	        => $n1 = 'Test Event',
			'description'	=> 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean accumsan, quam et accumsan tristique, purus elit facilisis turpis, vel venenatis eros nisi nec ex. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Pellentesque vulputate mattis diam, vitae aliquet nibh. Nam a eros sapien. Sed convallis at nulla commodo convallis. Pellentesque fermentum est id enim fermentum rutrum in sed ante. Sed id nulla ultrices, auctor diam ac, pulvinar turpis.

Mauris eget convallis augue, quis imperdiet justo. Phasellus mattis in tortor sed mollis. Duis lobortis finibus lorem a lacinia. Nunc vitae dictum mi. Pellentesque mollis orci eu pretium commodo. Curabitur euismod convallis neque, a porta arcu tempor non. Nam eget augue in turpis iaculis tristique sit amet sit amet arcu. Pellentesque vel accumsan urna, eget suscipit neque. Morbi convallis lacus sapien, at posuere neque consequat eu. Nullam vestibulum, nisi at tempor eleifend, erat felis viverra lectus, vitae consectetur justo risus eget justo. Phasellus blandit dictum aliquet. Praesent at iaculis magna. Curabitur scelerisque augue interdum quam scelerisque cursus. Maecenas ultricies arcu sit amet porttitor malesuada.',
			'schedule'		=> $d1 = date('Y-m-d'),
			'venue'			=> 'Webinar',
			'category'		=> 'Test Event'
		),
		456	=> array(
			'name'	        => $n2 = 'Another Event',
			'description'	=> 'Nullam vestibulum ligula aliquet mi malesuada dignissim. Pellentesque vel imperdiet nisi, ut dapibus est. Nullam turpis justo, convallis quis pharetra ut, blandit molestie turpis. Donec erat neque, rhoncus a aliquet vitae, pharetra at lorem. Maecenas placerat efficitur accumsan. Nunc ac ligula et ipsum euismod tincidunt et eu urna. Vivamus ultricies molestie risus, et accumsan nisl suscipit quis. In non lorem vulputate, feugiat metus a, congue nibh. Aenean sit amet sem laoreet nunc faucibus efficitur ac sed risus.',
			'schedule'		=> $d2 = date('Y-m-d', strtotime('next week')),
			'venue'			=> 'Webinar',
			'category'		=> 'Test Event'
		),
	    789 => array(
	        'name'	        => $n3 = 'Attached Material',
	        'description'	=> 'Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Integer ornare fringilla massa, in tincidunt purus facilisis ut. Proin elementum congue odio, ut pretium massa eleifend et. Suspendisse ornare elementum iaculis. Mauris dignissim mauris quis nulla lobortis, ac venenatis nulla finibus. Nunc lobortis nisl leo. Maecenas aliquam est lorem, malesuada venenatis erat vehicula vitae. Aliquam imperdiet odio eget sollicitudin porta. Mauris diam nunc, dignissim vel est quis, volutpat condimentum tellus. Ut sed mollis tortor. Phasellus lectus mauris, sagittis vel elit a, vehicula convallis ex. Donec scelerisque porta neque, at faucibus sapien pretium eu. Donec vestibulum metus sed elit maximus, a sagittis eros laoreet. Nam ut tincidunt mauris. Ut finibus iaculis diam, et dignissim eros interdum id.

Pellentesque eget gravida sapien. Sed facilisis ipsum ut est lobortis dignissim. Quisque eros nibh, efficitur a tincidunt vel, gravida at purus. Etiam vestibulum nibh vitae purus semper aliquet. Fusce posuere egestas odio ac malesuada. Sed ut enim mi. Ut ultrices leo laoreet consequat vulputate.',
	        'schedule'		=> 'Material',
	        'venue'		    => 'Link will be sent to your e-mail',
	        'category'		=> 'Material'
	    )
	);
	
	$select[123] = $n1.' - '.$d1;
	$select[456] = $n2.' - '.$d2;
	$select[789] = $n3.' - Material';
	
} else if(!empty($data = MahoAPI::call('browse/Event', array('?' => array('filter' => $filter_id, 'limit' => 10, 'sort' => 'Event.start_date', 'direction' => 'asc')))) && !empty($data['data']['entries'])) {
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

