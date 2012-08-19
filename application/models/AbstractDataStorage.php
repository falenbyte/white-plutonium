<?php

// getter i setter z kontrol¹ dostêpu do zmiennych

abstract class Application_Model_AbstractDataStorage {

	protected $data;
	protected $variablesList;
	private $readAccessList;
	private $writeAccessList;
	
	final public function __construct($initArray = false) {
		$this -> data = array();
		$this -> initVariableList();
		$init = is_array($initArray);
		foreach($this -> variablesList as $variable) {
			$variable = explode('|', $variable, 2);
			if(!isset($variable[1])) {
				$variable[1] = 'rw';
			}
			if(strpos($variable[1], 'r') !== false) {
				$this -> readAccessList[] = $variable[0];
			}
			if(strpos($variable[1], 'w') !== false) {
				$this -> writeAccessList[] = $variable[0];
			}
			if($init) $this -> data[$variable[0]] = $initArray[$variable[0]];
		}
	}
	
	// funkcja inicjalizuj¹ca listê dostêpow¹ do zmiennych
	abstract protected function initVariableList();
	
	final public function __get($name) {
		if(in_array($name, $this -> readAccessList)) {
			return $this -> data[$name];
		} else {
			throw new Exception('Trying to read wrong variable.');
		}
	}
	
	final public function __set($name, $value) {
		if(in_array($name, $this -> writeAccessList)) {
			$this -> data[$name] = $value;
		} else {
			throw new Exception('Trying to write to wrong variable.');
		}
	}

}

