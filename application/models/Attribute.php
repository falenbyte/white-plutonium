<?php

class Application_Model_Attribute extends Application_Model_AbstractDataStorage {
	
	protected function initVariableList() {
		$this -> variablesList = array('ID', 'name', 'type', 'options', 'unit', 'min', 'max');
	}
	
	public function addOption($id, $name) {
		if(preg_match('/^[0-9]+$/', $id)) {
			if(preg_match('/^[a-zA-Z0-9\(\), ]+$/', $option)) {
				$this -> data['options'][$id] = $option;
			} else {
				throw new Exception('Trying to add option with wrong name.');
			}
		} else {
			throw new Exception('Trying to add option with wrong ID.');
		}
	}
	
	public function validateValue($value) {
		switch($this -> data['type']) {
			case '0':
				return (preg_match('/^[0-9]+$/', $value) && intval($value) >= intval($this -> data['min']) && intval($value) <= intval($this -> data['max']));
			case '1':
				return is_string($value);
			case '2':
				return array_key_exists($value, $this -> data['options']);
			case '3':
				return ($value === '0' || $value === '1');
			case '4':
				return (is_numeric($value) && floatval($value) >= floatval($this -> data['min']) && floatval($value) <= floatval($this -> data['max']));
		}
	}
	
	public function getTypeString() {
		switch($this -> data['type']) {
			case '0':
				return 'int';
			case '1':
				return 'text';
			case '2':
				return 'int';
			case '3':
				return 'int';
			case '4':
				return 'float';
		}
	}

}
