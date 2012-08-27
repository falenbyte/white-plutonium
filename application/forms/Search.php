<?php

class Application_Form_Search
	{
		protected $_elements;
		protected $_catMapper;
		protected $_attMapper;
		
		public function __construct(array $params, $basic = false)
			{
				$this->_catMapper = new Application_Model_CategoriesMapper();
				$this->_attMapper = new Application_Model_AttributesMapper();
				
				$this->_addElement('Słowa kluczowe', 'keywords', 'text', (isset($params['keywords']) ? $params['keywords'] : ''));
				
				$properChildCategory = (isset($params['cat'])
					&& preg_match('/^[0-9]+$/', $params['cat'])
					&& !is_null($this->_catMapper->getByID($params['cat'])->parentID));
				
				if(!$properChildCategory || $basic)
					$this->_addElement('Kategoria', 'cat', 'category', (isset($params['cat']) ? $params['cat'] : 'all'), $this->_catMapper->getAll());
				else
				{
					$atts = $this->_attMapper->getByCategoryID($params['cat']);
					
					$this->_addElement('', 'cat', 'hidden', $params['cat']);
					
					foreach($atts as $id => $att)
					{
						switch($att->type)
						{
							case '0':
								$this->_addElement($att->name, $id, 'minmax', (isset($params[$id]) && is_array($params[$id]) ? $params[$id] : array()));
								break;
							
							case '1':
								$this->_addElement($att->name, $id . '[]', 'text', (isset($params[$id]) && is_array($params[$id]) ? $params[$id][0] : ''));
								break;
							
							case '2':
								$this->_addElement($att->name, $id, 'multiselect', (isset($params[$id]) && is_array($params[$id]) ? $params[$id] : array('0')), array_merge(array('0'=>'Brak'), $att->options));
								break;
							
							case '3':
								$this->_addElement($att->name, $id . '[]', 'checkbox', (isset($params[$id]) ? $params[$id][0] : '0'));
								break;
							
							case '4':
								$this->_addElement($att->name, $id, 'minmax', (isset($params[$id]) && is_array($params[$id]) ? $params[$id] : array()));
								break;
							
							default:
								break;
						}
					}
				}
				
				$orderOptions = array();
				
				if($properChildCategory)
				{
					if(!empty($atts)) {
						foreach($atts as $id => $att)
						{
							switch($att->type)
							{
								case '0':
								case '4':
									$orderOptions[$id] = $att->name;
									break;
								default:
									break;
							}
						}
					}
				}
				
				$options = Zend_Registry::get('options');
				//$this -> _addElement('Ogłoszeń na stronę', 'per_page', 'select', (isset($params['per_page']) ? $params['per_page'] : $options['defaultAnnsPerPage']), array('25'=>'25', '50'=>'50', '100'=>'100'));
				//$this -> _addElement('Sortuj według', 'order_by', 'select', (isset($params['order_by']) ? $params['order_by'] : 'date'), array_merge(array('date'=>'Data', 'expires'=>'Koniec'), $orderOptions));
				//$this -> _addElement('Jak', 'dir', 'select', (isset($params['dir']) ? $params['dir'] : 'desc'), array('asc'=>'Rosnąco', 'desc'=>'Malejąco'));
				$this -> _addElement('', 'send', 'submit', 'Szukaj');
				$this -> _addElement('', 'form_type', 'hidden', ($basic ? 'basic' : 'spec'));
				
				if(isset($params['user_id']))
					$this->_addElement('', 'user_id', 'hidden', $params['user_id']);
			}
		
		public function render($action)
			{
				return '<form action="' . $action . '" method="GET"><div>' .
					implode('</div><div>', $this->_elements) .
					'</div></form>';
			}
		
		// type: category, text, minmax(tablica), select, multiselect (tablica), checkbox, hidden, submit
		// tablica - do name automatycznie dodawane '[]', $value jest tablicą
		protected function _addElement($label, $name, $type, $value, array $options = NULL)
			{
				$elStr = '';
				
				switch($type)
				{
					case 'text':
						$elStr = sprintf('<div class="search"><div class="search_label">%s:</div><div class="search_input"><input name="%s" type="text" value="%s" /></div></div>',
							$label,
							$name,
							$value);
						break;
					
					case 'category':
						$elStr = sprintf('<div class="search"><div class="search_label">%s:</div><div class="search_input"><select name="%s">', $label, $name);
						$elStr .= '<option value="all"' . ($value == 'all' ? ' selected' : '') . '>Wszystkie</option>';
						foreach($options as $ckey => $cat)
						{
							if(is_null($cat->parentID))
							{
								$elStr .= sprintf('<optgroup label="%s">', $cat -> name);
								
								foreach($options as $chckey => $chcat)
								{
									if($chcat->parentID == $ckey)
									{
										$elStr .= sprintf('<option value="%s"%s>%s</option>',
											$chckey,
											($chckey == $value ? ' selected' : ''),
											$chcat->name);
									}
								}
								
								$elStr .= '</optgroup>';
							}
						}
						$elStr .= '</select></div></div>';
						break;
					
					case 'hidden':
						$elStr = sprintf('<input name="%s" type="hidden" value="%s" />',
							$name,
							$value);
						break;
					
					case 'minmax':
						$elStr = sprintf('<div class="search"><div class="search_label">%s:</div><div class="search_input"><input name="%s[min]" size="3" type="text" value="%s"> do: <input name="%s[max]" size="3" type="text" value="%s"></div></div>',
							$label,
							$name,
							(isset($value['min']) ? $value['min'] : ''),
							$name,
							(isset($value['max']) ? $value['max'] : ''));
						break;
					
					case 'multiselect':
						$elStr = sprintf('<div class="search"><div class="search_label">%s:</div><div class="search_input"><select name="%s[]" multiple>', $label, $name);
						foreach($options as $oid => $oname)
						{
							$elStr .= sprintf('<option value="%s"%s>%s</option>',
								$oid,
								(in_array($oid, $value) ? ' selected' : ''),
								$oname);
						}
						$elStr .= '</select></div></div>';
						break;
					
					case 'checkbox':
						$elStr = sprintf('<div class="search"><input name="%s" type="checkbox" value="1" id="%s"%s /><label for="%s">&nbsp;%s</label></div>',
							$name,
							'check' . $name,
							($value == '1' ? ' checked' : ''),
							'check' . $name,
							$label);
						break;
					
					case 'select':
						$elStr = sprintf('<div class="search"><div class="search_label">%s:</div><div class="search_input"><select name="%s"></div></div>', $label, $name);
						foreach($options as $okey => $oname)
						{
							$elStr .= sprintf('<option value="%s"%s>%s</option>',
								$okey,
								($okey == $value ? ' selected' : ''),
								$oname);
						}
						$elStr .= '</select>';
						break;
					
					case 'submit':
						$elStr = sprintf('<input name="%s" type="submit" value="%s" />',
							$name,
							$value);
						break;
				}
				
				$this->_elements[] = $elStr;
				return $this;
			}
	};
