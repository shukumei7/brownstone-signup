<?php

class MahoForm {
	
	public static $data;
	
	private static $__maxSelect = 5;
	private static $__options;
	
	public static function setSelect($detail_code, $options) {
		self::$__options[$detail_code] = $options;
	}
	
	public static function input($detail) {
	    if(empty(($type = @$detail['data_type']) || ($type = @$detail['type']))) {
			throw new Exception('Detail type is not provided');
		}
		
		switch($type) {
			case 'text':
				return self::__textInput($detail);
			case 'entry':
				if(!$detail['single']) {
					$detail['multiple'] = 1; 
				}
				return self::__selectInput($detail);
			case 'string':
			case 'email':
				return self::__defaultInput($detail);
			case 'bool':
			    return self::__checkInput($detail);
			default:
				// return 'Unknown type '.$detail['data_type'].' for '.$detail['name'];
		}
	}
	
	public static function submit($text, $class = '') {
		return self::__tag('button', $text, array(
			'type'	=> 'submit',
			'class'	=> 'btn btn-'.($class ?: 'primary')
		));
	}
	
	private static function __tag($name, $content, $params = array()) {
		$out = '<'.$name;
		if(is_array($content)) {
			$params = $content;
			$content = null;
		}
		if(!empty($params)) {
			foreach($params as $key => $value) {
				$value && $out .= ' '.$key.'="'.$value.'"';
			}
		}
		if($content === null) {
			return $out.'/>';
		}
		$out .= '>'.$content.'</'.$name.'>';
		return $out;
	}
	
	private static function __inputName($detail_code) {
		$names = explode('.', $detail_code);
		$out = 'data['.implode('][', $names).']';
		return $out;
	}
	
	private static function __inputValue($detail_code) {
		if(empty($data = self::$data)) {
			return null;	
		}
		
		$names = explode('.', $detail_code);
		
		foreach($names as $name) {
			if(!isset($data[$name])) {
				return null;
			}
			$data = $data[$name];
		}
		
		return $data;
	}
	
	private function __defaultWrap($detail, $input) {
	    return self::__formGroup(self::__label((@$detail['name'] ? ucwords($detail['name']) : '').(!empty($detail['required'])? '<span style="color:red">*</span>' : ''), $detail['code']).$input);
	}
	
	private function __helpMessage($detail) {
		return (!empty($detail['help_message'])? self::__tag('small', $detail['help_message'], array(
			'id'	=> $detail['code'].'.help',
			'class'	=> 'form-text text-muted'
		)) : '');
	}
	
	private function __attributes($detail) {
		$options = array();
		foreach(array('disabled', 'readonly', 'autofocus', 'placeholder', 'required') as $attr) {
			@$detail[$attr] && $options[$attr] = $detail[$attr];
		}
		return array(
			'class'	=> 'form-control',
			'id'	=> str_replace('.', '-', strtolower($detail['code'])),
			'name'	=> self::__inputName($detail['code']),
		    'value'	=> @$detail['type'] == 'password' || @$detail['data_type'] == 'password'? null : self::__inputValue($detail['code'])
		) + $options + (!empty($detail['help_message'])? array('aria-describedby' => $detail['code'].'.help') : array()) +
		(!empty($detail['formula'])? array('placeholder' => $detail['formula']) : array()) +
		(!empty($detail['required'])? array('required' => 'true') : array());
	}
	
	private static function __defaultInput($detail) {
		return self::__defaultWrap($detail, self::__tag('input', array(
			'type'	=> $detail['data_type']
		) + self::__attributes($detail)).self::__helpMessage($detail));
	}
	
	private static function __checkInput($detail) {
	    return self::__defaultWrap($detail, self::__tag('input', array(
	        'type'	   => 'checkbox',
	        'class'    => 'form-check-input'
	    ) + self::__attributes($detail)).self::__helpMessage($detail));
	}
	
	private static function __textInput($detail) {
		$options = array('maxlength' => 1000);
		foreach(array('cols', 'rows', 'maxlength', 'wrap') as $attr) {
			@$detail[$attr] && $options[$attr] = $detail[$attr];
		}
		empty($options['placeholder']) && $options['placeholder'] = 'Enter a maximum of '.number_format($options['maxlength']).' characters';
		return self::__defaultWrap($detail, self::__tag('textarea', '', self::__attributes($detail) + $options).self::__helpMessage($detail));
	}
	
	private static function __selectInput($detail) {
		$options = '';
		if(!$detail['required']) {
			$options .= self::__tag('option', @$detail['empty'] ?: '(Nothing selected)');
		}
		if(!empty($selections = @$detail['options']) || !empty($selections= @self::$__options[$detail['code']])) {
			foreach($selections as $val => $text) {
				$options .= self::__tag('option', $text, array('value' => $val));
			}
		}
		if($detail['multiple'] && !isset($detail['size'])) {
			$detail['size'] = min(@count($selections) ?: 0, self::$__maxSelect);
		}
		$selOptions = array();
		foreach(array('multiple', 'size') as $attr) {
			@$detail[$attr] && $selOptions[$attr] = $detail[$attr]; 
		}
		return self::__defaultWrap($detail, self::__tag('select', $options, self::__attributes($detail) + $selOptions).self::__helpMessage($detail));
	}
	
	private static function __formGroup($content) {
		return self::__tag('div', $content, array('class' => 'form-group'));
	}
	
	private static function __label($string, $for = '') {
		return self::__tag('label', $string, $for? array('for' => $for) : array());
	}
	
}