<?php

class Application_Model_Category extends Application_Model_AbstractDataStorage {

	protected function initVariableList() {
		$this -> variablesList = array('ID', 'parentID', 'name');
	}

}