<?php

class Application_Model_Attribute extends Application_Model_AbstractDataStorage {
	
	protected function initVariableList() {
		$this -> variablesList = array('ID', 'name', 'type', 'options', 'unit', 'min', 'max');
	}
	
	public function addOption($option) {
		if(preg_match('/[a-zA-Z0-9\(\), ]+/', $option)) {
			$this -> data['options'][] = $option;
		}
	}

}