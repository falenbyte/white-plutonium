<?php

// TODO: konstruowanie na podstawie formularza wyszukiwania

class Application_Model_SearchFilters
	{
		private $_filters;
		private $_limit;
		private $_offset;
		private $_order;
		
		public function __construct()
			{
				$this->_filters = array();
			}
		
		public function addFilter($type, $value)
			{
				if(!in_array($type,  array('catID', 'userID', 'attribute', 'keywords')))
					throw new Exception('Unknown filter type: ' . $type);
				
				$fun = '_add' . ucfirst($type);
				$this->$fun($value);
				
				return $this;
			}
		
		public function getFiltersArray()
			{
				return $this->filters;
			}
		
		public function setLimit($limit)
			{
				if(!preg_match('/[0-9]+/', $limit))
					throw new Exception('Supplied limit is invalid.');
				
				$this->_limit = $limit;
				return $this;
			}
		
		public function setOffset($offset)
			{
				if(!preg_match('/[0-9]+/', $offset))
					throw new Exception('Supplied offset is invalid.');
				
				$this->_offset = $offset;
				return $this;
			}
		
		public function getLimit()
			{
				return $this->_limit;
			}
		
		public function getOffset()
			{
				return $this->_offset;
			}
		
		public function setOrder($value, $dir)
			{
				if($value !== 'date' && $value !== 'expires' && !preg_match('/[0-9]+/', $value))
					throw new Exception('Invalid sorting value: ' . $value);
				
				if($dir !== 'asc' && $dir != 'desc')
					throw new Exception('Invalid sorting direction: ' . $dir);
				
				$_order = array('value'=>$value, 'direction'=>$dir);
				return $this;
			}
		
		public function getOrder()
			{
				return $this->_order;
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
				$this->filters['keywords'] = explode(' ', $value);
			}
	}

// addFilter( 'attribute', array('ID'=>'5', 'values'=>array('50', '80')) );
