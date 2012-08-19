<?php

abstract class Application_Model_AbstractDataStorage {

	protected $data;
	protected $variablesList;
	
	final public function __construct($init = null) {
		$this -> data = array();
		$this -> initVariableList();
		if(is_array($init)) {
			foreach($this -> variablesList as $variable) {
				$this -> data[$variable] = $init[$variable];
			}
		}
	}
	
	abstract protected function initVariableList();
	
	final public function __get($name) {
		if(in_array($name, $this -> variablesList)) {
			return $this -> data[$name];
		} else {
			throw new Exception('Trying to read wrong variable.');
		}
	}
	
	final public function __set($name, $value) {
		if(in_array($name, $this -> variablesList)) {
			$this -> data[$name] = $value;
		} else {
			throw new Exception('Trying to write to wrong variable.');
		}
	}

}

