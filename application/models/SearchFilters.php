<?php

// TODO: konstruowanie na podstawie formularza wyszukiwania

class Application_Model_SearchFilters
	{
		private $_filters;
		private $_limit;
		private $_offset;
		private $_order;
		private $_db;
		
		public function __construct()
			{
				$this->_filters = array();
				$this->_limit = 0;
				$this->_offset = 0;
				$this->_order = NULL;
				$this->_db = Zend_Registry::get('db');
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
				if(!in_array($value, array('date', 'expires')) && !preg_match('/[0-9]+/', $value))
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
		
		// $value['values']:
		// dla 0: array z indeksami min i max (lub jednym z nich)
		// dla 1: array ze stringami
		// dla 2: array z intami (dopuszczalne opcje)
		// dla 3: array z pojedynczą wartością - 0 lub 1
		// dla 4: jak dla 0
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
				
				foreach($value['values'] as $key => $attValue)
				{
					if(!$att->validateValue($attValue))
						throw new Exception('Attribute value invalid: ' . $attValue);
					
					if($att->type === '0' || $att->type === '4')
					{
						if($key !== 'min' && $key !== 'max')
							throw new Exception('Invalid key in filter values array: ' . $key);
					}
					else if($att->type === '1')
						$attValue = $this->_db->quote($attValue);
				}
				
				$this->_filters['attributes'][$value['ID']] = $value['values'];
			}
			
		private function _addKeywords($value)
			{
				$kwords = explode(' ', $value);
				
				foreach($kwords as $kword)
					$kword = $this->_db->quote('%' . $kword . '%');
				
				$this->_filters['keywords'] = $kwords;
			}
		
		public function getQueryString()
			{
				if(isset($this->_filters['catID']))
				{
					$catMapper = new Application_Model_CategoriesMapper();
					$attDefs = $catMapper->getByID($this->_filters['catID'])->getAttributes();
					
					$attFlag = isset($this->_filters['attributes']);
					
					if(is_array($this->_order))
					{
						if(preg_match('/[0-9]+/', $this->_order['value']))
							$orderFlag = (array_key_exists($this->_order['value'], $attDefs) ? 2 : 0);
						else
							$orderFlag = 1;
					}
					else
						$orderFlag = 0;
				}
				else
				{
					$attFlag = false;
					$orderFlag = (is_array($this->_order) && !preg_match('/[0-9]+/', $this->_order['value']) ? 1 : 0);
				}
				
				if($attFlag)
				{
					$caseStr = 'CASE ';
					
					foreach($this->_filters['attributes'] as $id => $val)
					{
						if(!array_key_exists($id, $attDefs))
							continue;
						
						$caseStr .= 'WHEN (attID = ' . $id . ') THEN IF(';
						
						switch($attDefs[$id]->type)
						{
							case '0':
								if(isset($val['min']) && isset($val['max']))
									$caseStr .= 'intValue BETWEEN ' . $val['min'] . ' AND ' . $val['max'];
								else if(isset($val['min']))
									$caseStr .= 'intValue >= ' . $val['min'];
								else
									$caseStr .= 'intValue <= ' . $val['max'];
								break;
							
							case '1':
								$caseStr .= 'textValue IN(' . implode(', ', $val) . ')';
								break;
							
							case '2':
							case '3':
								$caseStr .= 'intValue IN(' . implode(', ', $val) . ')';
								break;
							
							case '4':
								if(isset($val['min']) && isset($val['max']))
									$caseStr .= 'floatValue BETWEEN ' . $val['min'] . ' AND ' . $val['max'];
								else if(isset($val['min']))
									$caseStr .= 'floatValue >= ' . $val['min'];
								else
									$caseStr .= 'floatValue <= ' . $val['max'];
								break;
						}
						
						$caseStr .= ', 1, 0) ';
					}
					
					$caseStr .= 'END';
					
					$selectStr = 'SELECT announcements.ID AS annID, announcements.catID AS catID, ' .
						(isset($this->_filters['userID']) ? 'announcements.userID AS userID, ' : '') .
						'attributes_values.attID AS attID, attributes_values.intValue AS intValue, ' .
						'attributes_values.textValue AS textValue, attributes_values.floatValue AS floatValue,' .
						' (' . $caseStr . ') AS fulfilled ' .
						'FROM attributes_values JOIN announcements ON (attributes_values.annID = announcements.ID) ' .
						'WHERE catID = ' . $this->_filters['catID'] .
						(isset($this->_filters['userID']) ? ' AND userID = ' . $this->_filters['userID'] : '');
					
					$selectStr = 'SELECT annID, SUM(fulfilled) FROM (' . $selectStr . ') GROUP BY annID ' .
						'HAVING SUM(fulfilled) = ' . count($this->_filters['attributes']);
					
					$selectStr = 'SELECT annID FROM (' . $selectStr . ')';
				}
				
				if($orderFlag == 0 || $orderFlag == 1)
				{
					$finalStr = 'SELECT * FROM announcements';
					
					$orderStr = ' ORDER BY ' . ($orderFlag == 0 ?
						'date DESC' :
						$this->_order['value'] . ' ' . strtoupper($this->_order['direction']));
				}
				else
				{
					$finalStr = 'SELECT announcements.ID, announcements.catID, announcements.userID, ' .
						'announcements.title, announcements.content, announcements.date, announcements.expires, ' .
						'attributes_values.' . $attDefs[$this->_order['value']]->getTypeString() . 'Value AS ordVal ' .
						'FROM announcements JOIN attributes_values ON (announcements.ID = attributes_values.annID) ' .
						'WHERE attributes_values.attID = ' . $this->_order['value'];
					
					$orderStr = ' ORDER BY ordVal ' . strtoupper($this->_order['direction']);
				}
				
				if($attFlag)
					$whereStrs[] = ' ID IN(' . $selectStr . ')';
				else
				{
					if(isset($this->_filters['catID']))
						$whereStrs[] = ' catID = ' . $this->_filters['catID'];
					if(isset($this->_filters['userID']))
						$whereStrs[] = ' userID = ' . $this->_filters['userID'];
				}
				
				$kwordBegin = ($orderStr == 2 ? 'announcements.title' : 'title') . ' LIKE ';
				foreach($this->_filters['keywords'] as $kword)
					$kwords[] = $kwordBegin . $kword;
				$whereStrs[] = ' (' . implode(' OR ', $kwords) . ')';
				
				$finalStr .= ($orderFlag == 2 ? ' AND' : ' WHERE') . implode(' AND', $whereStrs);
				$finalStr .= $orderStr . ' LIMIT ' . $this->_offset . ', ' . $this->_limit;
				
				return $finalStr;
			}
	}

