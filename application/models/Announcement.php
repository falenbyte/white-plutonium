<?php

class Application_Model_Announcement extends Application_Model_AbstractDataStorage {

	protected function initVariableList() {
		$this -> variablesList = array('ID', 'userID', 'catID', 'title', 'content', 'date', 'expires');
	}
	
	public function getCategory() {
		$mapper = new Application_Model_CategoriesMapper();
		return $mapper -> getByID($this -> data['catID']);
	}

}

