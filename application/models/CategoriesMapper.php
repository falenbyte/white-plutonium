<?php

class Application_Model_CategoriesMapper {

	private $db;

	public function __construct() {
		$this -> db = Zend_Registry::get('db');
	}

	public function getAll() {
		$result = $this -> db -> fetchAssoc('SELECT * FROM categories');
		foreach($result as $row) {
			$categories[$row['ID']] = new Application_Model_Category($row);
		}
		return $categories;
	}

	public function getByID($catID) {
		if(preg_match('/^[0-9]+$/', $catID)) {
			$result = $this -> db -> fetchRow('SELECT * FROM categories WHERE ID = ?', $catID, Zend_Db::FETCH_ASSOC);
			return new Application_Model_Category($result);
		} else {
			throw new Exception('Supplied category ID is invalid.');
		}
	}

	public function getChildren($catID) {
		if(preg_match('/^[0-9]+$/', $catID)) {
			$result = $this -> db -> fetchAssoc('SELECT * FROM categories WHERE parentID = ?', $catID);
			foreach($result as $row) {
				$categories[$row['ID']] = new Application_Model_Category($row);
			}
			return $categories;
		} else {
			throw new Exception('Supplied category ID is invalid.');
		}
	}

}
