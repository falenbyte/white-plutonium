<?php

class Application_Model_AttributesMapper {

	private $_db;

	public function __construct() {
		$this -> _db = Zend_Registry::get('db');
	}

	public function getAll() {
		$result = $this -> _db -> fetchAll('SELECT * FROM attributes', null, Zend_Db::FETCH_ASSOC);
		foreach($result as $row) {
			$attributes[$row['ID']] = new Application_Model_Attribute($row);
			if($row['type'] == '2') {
				$optionsList[] = $row['ID'];
			}
		}
		if(isset($optionsList)) {
			$options = $this -> _db -> fetchAll('SELECT * FROM attributes_options WHERE attID IN (?)', implode(',', $optionsList), Zend_Db::FETCH_ASSOC);
			foreach($options as $option) {
				$attributes[$option['attID']] -> addOption($option['ID'], $option['option']);
			}
		}
		return $attributes;
	}

	public function getByCategoryID($catID) {
		if(!preg_match('/^[0-9]+$/', $catID)) {
			throw new Exception('Supplied category ID is invalid.');
		}
		$attributesList = $this -> _db -> fetchCol('SELECT attID FROM categories_attributes WHERE catID = ?', $catID);
		$result = $this -> _db -> fetchAll('SELECT * FROM attributes WHERE ID IN (' . implode(',', $attributesList) . ')', null, Zend_Db::FETCH_ASSOC);
		foreach($result as $row) {
			$attributes[$row['ID']] = new Application_Model_Attribute($row);
			if($row['type'] == '2') {
				$optionsList[] = $row['ID'];
			}
		}
		if(isset($optionsList)) {
			$options = $this -> _db -> fetchAll('SELECT * FROM attributes_options WHERE attID IN (?)', implode(',', $optionsList), Zend_Db::FETCH_ASSOC);
			foreach($options as $option) {
				$attributes[$option['attID']] -> addOption($option['ID'], $option['option']);
			}
		}
		return $attributes;
	}

	public function getByID($attID) {
		if(!preg_match('/^[0-9]+$/', $attID)) {
			throw new Exception('Supplied attribute ID is invalid.');
		}
		$row = $this -> _db -> fetchRow('SELECT * FROM attributes WHERE ID = ?', $attID, Zend_Db::FETCH_ASSOC);
		$attribute = new Application_Model_Attribute($row);
		if($row['type'] == '2') {
			$options = $this -> _db -> fetchAll('SELECT * FROM attributes_options WHERE attID = ?', $attID, Zend_Db::FETCH_ASSOC);
			foreach($options as $option) {
				$attribute -> addOption($option['ID'], $option['option']);
			}
		}
		return $attribute;
	}

}
