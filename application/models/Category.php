<?php

class Application_Model_Category extends Application_Model_AbstractDataStorage {

	protected function initVariableList() {
		$this -> variablesList = array('ID', 'parentID', 'name');
	}
	
	public function getAttributes() {
		$mapper = new Application_Model_AttributesMapper();
		return $mapper -> getByCategory($this -> data['ID']);
	}

}