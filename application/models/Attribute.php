<?php

class Application_Model_Attribute extends Application_Model_AbstractDataStorage {

	protected function initVariableList() {
		$this -> variablesList = array('ID', 'name', 'type', 'options', 'unit', 'min', 'max', 'main');
	}

	public function addOption($id, $name) {
		if(preg_match('/^[0-9]+$/', $id)) {
			$this -> data['options'][$id] = $name;
		} else {
			throw new Exception('Trying to add option with wrong ID.');
		}
	}

	public function validateValue($value) {
		switch($this -> data['type']) {
			case '0':
				return (preg_match('/^[0-9]+$/', $value) && ($this -> data['min'] === null || intval($value) >= intval($this -> data['min'])) && ($this -> data['max'] === null || intval($value) <= intval($this -> data['max'])));
			case '1':
				return is_string($value);
			case '2':
				return array_key_exists($value, $this -> data['options']);
			case '3':
				return ($value === '0' || $value === '1');
			case '4':
				return (is_numeric($value) && ($this -> data['min'] === null || floatval($value) >= floatval($this -> data['min'])) && ($this -> data['max'] === null || floatval($value) <= floatval($this -> data['max'])));
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

	public function getString($value) {
		$result = '<tr><td class="attribute_name">' . $this->name . ':</td><td class="attribute_value">';

		switch($this->data['type'])
		{
			case '0':
				$result .= $value . ' ' . $this->unit;
				break;

			case '1':
				$result .= $value;
				break;

			case '2':
				$result .= $this->options[$value];
				break;

			case '3':
				$result .= ($value == '1' ? 'tak' : 'nie');
				break;

			case '4':
				$result .= $value . ' ' . $this->unit;
				break;
		}

		return $result . '</td></tr>';
	}

}
