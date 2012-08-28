<?php

class Application_Model_CategoriesMapper {

	private $_db;
	private $_messages;

	public function __construct() {
		$this -> _db = Zend_Registry::get('db');
		$this -> _messages = Zend_Registry::get('messages') -> catMapper;
	}

	public function getAll() {
		$result = $this -> _db -> fetchAll('SELECT * FROM categories', null, Zend_Db::FETCH_ASSOC);
		return $this -> _convert($result);
	}

	public function getAllSubCategories() {
		$result = $this -> _db -> fetchAll('SELECT * FROM categories WHERE parentID IS NOT NULL', null, Zend_Db::FETCH_ASSOC);
		return $this -> _convert($result);
	}

	public function getMain() {
		$result = $this -> _db -> fetchAll('SELECT * FROM categories WHERE parentID IS NULL', null, Zend_Db::FETCH_ASSOC);
		return $this -> _convert($result);
	}

	public function countAll() {
		return $this -> _db -> fetchAssoc('SELECT catID, count(*) AS `count` FROM announcements GROUP BY catID');
	}

	public function getByID($catID) {
		if(!preg_match('/^[0-9]+$/', $catID)) {
			throw new Exception($this -> _messages -> invalidID);
		}
		$result = $this -> _db -> fetchRow('SELECT * FROM categories WHERE ID = ?', $catID, Zend_Db::FETCH_ASSOC);
		return new Application_Model_Category($result);
	}

	public function getChildren($catID) {
		if(!preg_match('/^[0-9]+$/', $catID)) {
			throw new Exception($this -> _messages -> invalidID);
		}
		$result = $this -> _db -> fetchAll('SELECT * FROM categories WHERE parentID = ?', $catID, Zend_Db::FETCH_ASSOC);
		return $this -> _convert($result);
	}

	private function _convert($result) {
		foreach($result as $row) {
			$categories[$row['ID']] = new Application_Model_Category($row);
		}
		return $categories;
	}

}
