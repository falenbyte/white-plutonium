<?php

class Application_Model_SearchFilters
	{
		private $_filters;
		private $_limit;
		private $_offset;
		private $_order;
		private $_db;
		
		public function __construct(array $params = NULL)
			{
				$options = Zend_Registry::get('options');
				$this->_filters = array();
				$this->_limit = $options['defaultAnnsPerPage'];
				$this->_offset = '0';
				$this->_order = array('value'=>'date', 'direction'=>'desc');
				$this->_db = Zend_Registry::get('db');
				
				if(!is_null($params))
				{
					foreach($params as $name => $value)
					{
						switch($name)
						{
							case 'cat':
								$this->_addCatID($value);
								break;
							
							case 'keywords':
								$this->_addKeywords($value);
								break;
							
							case 'order_by':
								$this->setOrder($value, (isset($params['dir']) ? $params['dir'] : 'desc'));
								break;
							
							case 'page':
								if(preg_match('/^[0-9]+$/', $value) && intval($value) > 0)
									$page = $value;
								break;
							
							case 'per_page':
								$this->setLimit($value);
								break;
							
							default:
								if(preg_match('/^[0-9]+$/', $name))
									$this->_addAttribute(array('ID'=>$name, 'values'=>$value));
								break;
						}
					}
				}
				
				if(isset($page))
					$this->_offset = strval((intval($page) - 1) * intval($this->_limit));
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
				if(!preg_match('/^[0-9]+$/', $limit))
					//throw new Exception('Supplied limit is invalid.');
					return;
				
				$this->_limit = $limit;
				return $this;
			}
		
		public function setOffset($offset)
			{
				if(!preg_match('/^[0-9]+$/', $offset))
					//throw new Exception('Supplied offset is invalid.');
					return;
				
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
				if(!in_array($value, array('date', 'expires')))
				{
					if(!preg_match('/^[0-9]+$/', $value))
						//throw new Exception('Invalid sorting value: ' . $value);
						return;
					else
					{
						$mapper = new Application_Model_AttributesMapper();
						$att = $mapper->getByID($value);
						
						if(is_null($att->ID))
							//throw new Exception('Attribute doesn\'t exist: ' . $value['ID']);
							return;
					}
				}
				
				if(!in_array($dir, array('asc', 'desc')))
					//throw new Exception('Invalid sorting direction: ' . $dir);
					return;
				
				$_order = array('value'=>$value, 'direction'=>$dir);
				return $this;
			}
		
		public function getOrder()
			{
				return $this->_order;
			}
		
		private function _addCatID($value)
			{
				if(preg_match('/^[0-9]+$/', $value))
					$this->_filters['catID'] = $value;
				else
					//throw new Exception('Supplied category ID is invalid.');	
					return;
			}
		
		private function _addUserID($value)
			{
				if(preg_match('/^[0-9]+$/', $value))
					$this->_filters['userID'] = $value;
				else
					//throw new Exception('Supplied user ID is invalid.');
					return;
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
					//throw new Exception('The value for attribute filter must be an array.');
					return;
				
				if(!isset($value['ID']) || !preg_match('/^[0-9]+$/', $value['ID']))
					//throw new Exception('Attribute ID not supplied or invalid.');
					return;
				
				if(!isset($value['values']) || !is_array($value['values']))
					//throw new Exception('Attribute values not supplied or invalid.');
					return;
				
				$mapper = new Application_Model_AttributesMapper();
				$att = $mapper->getByID($value['ID']);
				
				if(is_null($att->ID))
					//throw new Exception('Attribute doesn\'t exist: ' . $value['ID']);
					return;
				
				foreach($value['values'] as $key => $attValue)
				{
					if(!$att->validateValue($attValue))
						//throw new Exception('Attribute value invalid: ' . $attValue);
						return;
					
					if($att->type === '0' || $att->type === '4')
					{
						if($key !== 'min' && $key !== 'max')
							//throw new Exception('Invalid key in filter values array: ' . $key);
							return;
					}
					else if($att->type === '1')
						$attValue = $this->_db->quote($attValue);
				}
				
				$this->_filters['attributes'][$value['ID']] = $value['values'];
			}
			
		private function _addKeywords($value)
			{
				$kwords = explode(' ', $value);
				
				foreach($kwords as &$kword)
					$kword = $this->_db->quote('%' . $kword . '%');
				
				$this->_filters['keywords'] = $kwords;
			}
		
		public function getQueryString()
			{
				$catMapper = new Application_Model_CategoriesMapper();
				
				if(isset($this->_filters['catID']) && !is_null($catMapper->getByID($this->_filters['catID'])->parentID))
				{
					$attDefs = $catMapper->getByID($this->_filters['catID'])->getAttributes();	
					$attFlag = isset($this->_filters['attributes']);
					
					if(preg_match('/^[0-9]+$/', $this->_order['value']))
						$orderFlag = (array_key_exists($this->_order['value'], $attDefs) ? 2 : 0);
					else
						$orderFlag = 1;
				}
				else
				{
					$attFlag = false;
					$orderFlag = (preg_match('/^[0-9]+$/', $this->_order['value']) ? 0 : 1);
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
							case '2':
							case '3':
								$caseStr .= $attDefs[$id]->getTypeString() . 'Value IN(' . implode(', ', $val) . ')';
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
				
				if(isset($this->_filters['keywords']))
				{
					$kwordBegin = ($orderStr == 2 ? 'announcements.title' : 'title') . ' LIKE ';
					foreach($this->_filters['keywords'] as $kword)
						$kwords[] = $kwordBegin . $kword;
					$whereStrs[] = ' (' . implode(' OR ', $kwords) . ')';
				}
				
				if(is_array($whereStrs))
					$finalStr .= ($orderFlag == 2 ? ' AND' : ' WHERE') . implode(' AND', $whereStrs);
				
				$finalStr .= $orderStr . ' LIMIT ' . $this->_offset . ', ' . $this->_limit;
				
				return $finalStr;
			}
	}

