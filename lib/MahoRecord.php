<?php

class MahoRecord {
	
	private $__entry = array();
	private $__details = array();
	private $__states = array();
	private $__histories = array();
	private $__queues = array();
	private $__access = array();
	private $__type = array();
	private $__definitions = array();
	private $__deleted = array();
	private $__names = array();
	private $__users = array();
	private $__cached = null;
	private $__categories = array();
	
	public function __construct($entry) {
		if(empty($entry)) {
			return null;
		}
		$this->__entry = $entry['Entry'];
		$this->__type = $entry['Type'];
		$this->__definitions = $entry['Definition'];
		$this->__states = $entry['State'];
		$this->__histories = $entry['History'];
		$this->__cached = @$entry['cache_head'];
		$this->__access = $entry['Access'];
		$this->__deleted = @$entry['Deleted'];
		$this->__names = @$entry['names'];
		$this->__users = @$entry['users'];
		
		foreach($entry['Detail'] as $group => $details) {
			$this->__categories[$group] = array_keys($details);
			$this->__details += $details;
		}
	}
	
	public function type($field = null) {
		if($field) {
			return @$this->__type[$field];
		}
		return $this->__type;
	}
	
	public function states() {
		return $this->__states;
	}
	
	public function categories() {
		return $this->__categories;
	}
	
	public function detail_codes() { // deprecate
		return array_keys($this->__details);
	}
	
	public function details($values = false) {
	    if($values) {
	        return $this->__details;
	    }
		return array_keys($this->__details);
	}
	
	private function __detail_code($detail_code) {
		if(strpos($detail_code, '.') === false) {
			return $this->__type['code'].'.'.strtolower(preg_replace('/\s+/', '_', $detail_code));
		}
		return $detail_code;
	}
	
	private function __item($args, $value) {
		
		if(empty($args) || empty($code = array_shift($args))) {
			return $value;
		}
		
		$names = $definitions = $out = array();
		
		foreach($value as $type => $body) {
			$definitions += $body['definitions'];
			$out += $body['data'];
			$names += $body['names'];
		}
			
		if(!isset($out[$code])) {
			throw new Exception('Detail Code not found : '.$code);
		}
		
		if(!empty($entry_id = array_shift($args)) && !empty($out[$code][$entry_id])) {
			return $this->__detail($code, $definitions, array($code => $out[$code][$entry_id]), $names, $args);
		}
		
		return $out[$code];
		
	}
	
	/*
	private function __entry($value, $definition, $args) {
		
		if(empty($args) || empty($entry_id = array_shift($args))) {
			return $value;
		}
		
		if(!empty($field = array_shift($args))) {
			return @$value[$entry_id][$field];
		}
		
		$definition['single'] && !is_numeric($entry_id) && $value = current($value);

		return @$value[$entry_id];
	}
	*/
	
	private function __detail($code, $definitions, $details, $entries, $args) {
		
	    if(!isset($details[$code]) || !isset($definitions[$code])) {
			return array();
		}
		
		$value = $details[$code];
		
		/*
		if(in_array($definitions[$code]['type'], array('entry'))) {
			return $this->__entry(array_intersect_key($entries, array_flip($value = is_array($value)? $value : array($value)) + array_fill_keys($value, array('name' => 'Unknown Record'))), $definitions[$code], $args);
		}
		*/
		
		if($definitions[$code]['type'] === 'user') {
			return $value;
		}
		
		if($definitions[$code]['type'] === 'item' && !$definitions[$code]['single']) {
			return $this->__item($args, $value);
		}
		
		if($definitions[$code]['single'] && is_array($value) && ($value = array_filter($value))) {
			return current($value);
		}
		
		return $value;
	}
	
	public function detail($detail_code = null) {
		if(!$detail_code) {
			return array_keys($this->__details);
		}
		
		$args = func_get_args();
		array_shift($args);
		
		return $this->__detail($this->__detail_code($detail_code), $this->__definitions, $this->__details, $this->__names, $args);
	}
	
	public function histories() {
		return $this->__histories;
	}
	
	public function access($type) {
		return @$this->__access[$type];
	}
	
	public function deleted($detail_code = null) {
		if($detail_code === null) {
			return $this->__deleted;
		}
		if(($detail_id = MahoData::codes($this->__detail_code($detail_code))) === null) {
			return null;
		}
		return @$this->__deleted[$detail_id];
	}
	
	public function cache() {
		return $this->__cache;
	}
	
	public function __get( $field ) {
		if(strpos($field, '.') !== false) {
			return @$this->__details[$field];
		}
		return @$this->__entry[$field];
	}
}