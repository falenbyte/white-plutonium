<?php

class Application_Model_AttributesMapper {
	
	private $db;
	
	public function __construct() {
		$this -> db = Zend_Registry::get('db');
	}
	
	public function getAll() {
		$result = $this -> db -> fetchAssoc('SELECT * FROM attributes');
		foreach($result as $row) {
			$attributes[$row['ID']] = new Application_Model_Attribute($row);
			if($row['type'] == '2') {
				$optionsList[] = $row['ID'];
			}
		}
		if(isset($optionsList)) {
			$options = $this -> db -> fetchAssoc('SELECT * FROM attributes_options WHERE attID IN (?)', implode(',', $optionsList));
			foreach($options as $option) {
				$attributes[$option['attID']] -> addOption($option['ID'], $option['name']);
			}
		}
		return $attributes;
	}
	
	public function getByCategoryID($catID) {
		if(preg_match('/[0-9]+/', $catID)) {
			$attributesList = $this -> db -> fetchCol('SELECT attID FROM categories_attributes WHERE catID = ?', $catID);
			$result = $this -> db -> fetchAssoc('SELECT * FROM attributes WHERE ID IN (?)', implode(',', $attributesList));
			foreach($result as $row) {
				$attributes[$row['ID']] = new Application_Model_Attribute($row);
				if($row['type'] == '2') {
					$optionsList[] = $row['ID'];
				}
			}
			if(isset($optionsList)) {
				$options = $this -> db -> fetchAssoc('SELECT * FROM attributes_options WHERE attID IN (?)', implode(',', $optionsList));
				foreach($options as $option) {
					$attributes[$option['attID']] -> addOption($option['ID'], $option['name']);
				}
			}
			return $attributes;
		} else {
			throw new Exception('Supplied category ID is invalid.');
		}
	}
	
	public function getByID($attID) {
		if(preg_match('/[0-9]+/', $attID)) {
			$row = $this -> db -> fetchRow('SELECT * FROM attributes WHERE ID = ?', $attID, Zend_Db::FETCH_ASSOC);
			$attribute = new Application_Model_Attribute($row);
			if($row['type'] == '2') {
					$options = $this -> db -> fetchAssoc('SELECT * FROM attributes_options WHERE attID = ?', $attID);
					foreach($options as $option) {
						$attribute -> addOption($option['ID'], $option['name']);
					}
			}
			return $attribute;
		} else {
			throw new Exception('Supplied attribute ID is invalid.');
		}
	}
	
}
