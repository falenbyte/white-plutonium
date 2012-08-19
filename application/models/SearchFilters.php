<?php

// TODO: konstruowanie na podstawie formularza wyszukiwania

class Application_Model_SearchFilters
	{
		private $_filters;
		
		public function __construct()
			{
				$_filters = array();
			}
		
		public function addFilter($type, $value)
			{
				if($type != 'catID'
					&& $type != 'userID'
					&& $type != 'attribute'
					&& $type != 'keywords')
					throw new Exception('Unknown filter type: ' . $type);
				
				$fun = '_add' . ucfirst($type);
				$this->$fun($value);
			}
		
		private function _addCatID($value)
			{
				if(preg_match('/[0-9]+/', $value))
					$this->_filters['catID'] = $value;
				else
					throw new Exception('Supplied category ID is invalid.');	
			}
		
		private function _addUserID($value)
			{
				if(preg_match('/[0-9]+/', $value))
					$this->_filters['userID'] = $value;
				else
					throw new Exception('Supplied user ID is invalid.');
			}
		
		private function _addAttribute($value)
			{
				if(!is_array($value))
					throw new Exception('The value for attribute filter must be an array.');
				
				if(!isset($value['ID']) || !preg_match('/[0-9]+/', $value['ID']))
					throw new Exception('Attribute ID not supplied or invalid.');
				
				if(!isset($value['values']) || !is_array($value['values']))
					throw new Exception('Attribute values not supplied or invalid.');
				
				$mapper = new Application_Model_AttributesMapper();
				$att = $mapper->getByID($value['ID']);
				
				foreach($value['values'] as $attValue)
				{
					if(!$att->validateValue($attValue))
						throw new Exception('Attribute value invalid: ' . $attValue);
				}
				
				$this->_filters['attributes'][$value['ID']] = $value['values'];
			}
			
		private function _addKeywords($value)
			{
				$this->filters['keywords'] = $value;
			}
	}

// addFilter( 'attribute', array('ID'=>'5', 'values'=>array('50', '80')) );
