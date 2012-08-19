<?php

class Application_Model_Category {

	private $category;
	private $variablesList;

	public function __construct($catID, $init = null) {
		$this -> category = array();
		$this -> variablesList = array('name', 'parentID');
		if($catID !== null) {
			if(preg_match('/[0-9]+/', $catID)) {
				$db = Zend_Registry::get('db');
				$this -> category = $db -> fetchRow('SELECT name, parentID FROM categories WHERE ID = ?', $catID);
				//$this -> category['attributes'] = Application_Model_AttributesMapper::getByCategoryID($catID);
			} else {
				throw new Exception('Supplied category ID is invalid.');
			}
		} else if(is_array($init)) {
			$this -> category = $init;
		}
	}

	public function __get($name) {
		if(in_array($name, $this -> variablesList)) {
			return $this -> category[$name];
		} else {
			throw new Exception('Trying to read wrong variable.');
		}
	}
	
	public function __set($name, $value) {
		if(in_array($name, $this -> variablesList)) {
			$this -> category[$name] = $value;
		} else {
			throw new Exception('Trying to write to wrong variable.');
		}
	}

}

class Application_Model_CategoriesMapper {
	
	private $db;
	
	public function __construct() {
		$this -> db = Zend_Registry::get('db');
	}
	
	public function getAll() {
		$result = $this -> db -> fetchAssoc('SELECT * FROM categories');
		foreach($result as $row) {
			$categories[$row['ID']] = new Application_Model_Category(null, $row);
		}
		return $categories;
	}
	
	public function getByID($catID) {
		if(preg_match('/[0-9]+/', $catID)) {
			$result = $this -> db -> fetchAssoc('SELECT * FROM categories WHERE ID = ?', $catID);
			return new Application_Model_Category(null, $result);
		} else {
			throw new Exception('Supplied category ID is invalid.');
		}
	}
	
	public function getChildren($catID) {
		if(preg_match('/[0-9]+/', $catID)) {
			$result = $this -> db -> fetchAssoc('SELECT * FROM categories WHERE parentID = ?', $catID);
			foreach($result as $row) {
				$categories[$row['ID']] = new Application_Model_Category(null, $row);
			}
			return $categories;
		} else {
			throw new Exception('Supplied category ID is invalid.');
		}
	}
	
}