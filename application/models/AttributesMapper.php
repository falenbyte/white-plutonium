<?php

class Application_Model_AttributesMapper {

	private $_db;
	private $_messages;

	public function __construct() {
		$this -> _db = Zend_Registry::get('db');
		$this -> _messages = Zend_Registry::get('messages') -> attMapper;
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
			$options = $this -> _db -> fetchAll('SELECT * FROM attributes_options WHERE attID IN (' . implode(',', $optionsList) . ')', null, Zend_Db::FETCH_ASSOC);
			foreach($options as $option) {
				$attributes[$option['attID']] -> addOption($option['ID'], $option['option']);
			}
		}
		return $attributes;
	}

	public function getByCategoryID($catID) {
		if(!preg_match('/^[0-9]+$/', $catID)) {
			throw new Exception($this -> _messages -> invalidID);
		}
		$result = $this -> _db -> fetchAll('SELECT ID, name, type, unit, min, max, main FROM attributes AS att, categories_attributes AS cat WHERE att.ID = cat.attID AND cat.catID = ?', $catID, Zend_Db::FETCH_ASSOC);
		foreach($result as $row) {
			$attributes[$row['ID']] = new Application_Model_Attribute($row);
			if($row['type'] == '2') {
				$optionsList[] = $row['ID'];
			}
		}
		if(isset($optionsList)) {
			$options = $this -> _db -> fetchAll('SELECT * FROM attributes_options WHERE attID IN (' . implode(',', $optionsList) . ')', null, Zend_Db::FETCH_ASSOC);
			foreach($options as $option) {
				$attributes[$option['attID']] -> addOption($option['ID'], $option['option']);
			}
		}
		return $attributes;
	}

	public function getByID($attID) {
		if(!preg_match('/^[0-9]+$/', $attID)) {
			throw new Exception($this -> _messages -> invalidID);
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
