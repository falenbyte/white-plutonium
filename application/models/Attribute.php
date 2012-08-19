<?php

class Application_Model_Attribute {

	private $attribute;
	private $variablesList;
	
	public function __construct($init = null) {
		$this -> attribute = array();
		$this -> variablesList = array('name', 'type', 'options', 'unit', 'min', 'max');
		if($init !== null && is_array($init)) {
			$this -> attribute = $init;
		}
	}
	
	public function __get($name) {
		if(in_array($name, $this -> variablesList)) {
			return $this -> attribute[$name];
		} else {
			throw new Exception('Trying to read wrong variable.');
		}
	}
	
	public function __set($name, $value) {
		if(in_array($name, $this -> variablesList)) {
			$this -> attribute[$name] = $value;
		} else {
			throw new Exception('Trying to write to wrong variable.');
		}
	}
	
	public function addOption($option) {
		$this -> attribute['options'][] = $option;
	}

}

class Application_Model_AttributesMapper {
	
	private $db;
	
	public function __construct() {
		$this -> db = Zend_Registry::get('db');
	}
	
	public function getByCategory($catID) {
		if(preg_match('/[0-9]+/', $catID)) {
			$attributesList = $this -> db -> fetchNum('SELECT attID FROM categories_attributes WHERE catID = ?', $catID);
			$result = $this -> db -> fetchAssoc('SELECT * FROM attributes WHERE ID IN (?)', implode(',', $attributesList));
			foreach($result as $row) {
				$attributes[$row['ID']] = new Application_Model_Attribute($row);
				if($row['type'] == 2) {
					$optionsList[] = $row['ID'];
				}
			}
			$options = $this -> db -> fetchAssoc('SELECT * FROM attributes_options WHERE ID IN (?)', implode(',', $optionsList));
			foreach($options as $option) {
				$attributes[$option['attID']] -> addOption($option['option']);
			}
			return $attributes;
		} else {
			throw new Exception('Supplied category ID is invalid.');
		}
	}
	
}