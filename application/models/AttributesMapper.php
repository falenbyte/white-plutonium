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
			if($row['type'] == 2) {
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
			$attributesList = $this -> db -> fetchNum('SELECT attID FROM categories_attributes WHERE catID = ?', $catID);
			$result = $this -> db -> fetchAssoc('SELECT * FROM attributes WHERE ID IN (?)', implode(',', $attributesList));
			foreach($result as $row) {
				$attributes[$row['ID']] = new Application_Model_Attribute($row);
				if($row['type'] == 2) {
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
		if(preg_match('/[0-9]+/', $catID)) {
			$result = $this -> db -> fetchAssoc('SELECT * FROM attributes WHERE ID = ?', $attID);
			$attribute = new Application_Model_Attribute($result);
			if($row['type'] == 2) {
					$options = $this -> db -> fetchAssoc('SELECT * FROM attributes_options WHERE attID = ?', $attID);
					foreach($options as $option) {
						$attribute[$option['attID']] -> addOption($option['ID'], $option['name']);
					}
			}
			return $attribute;
		} else {
			throw new Exception('Supplied category ID is invalid.');
		}
	}
	
}