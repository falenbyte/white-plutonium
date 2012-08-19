<?php

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