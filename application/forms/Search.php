<?php

class Application_Form_Search
	{
		protected $_elements;
		protected $_catMapper;
		
		public function __construct(array $params)
			{
				$this->_catMapper = new Application_Model_CategoriesMapper();
				
				$this->_addElement('Słowa kluczowe', 'keywords', 'text', (isset($params['keywords']) ? $params['keywords'] : ''));
				
				if(!isset($params['cat'])
					|| !preg_match('/^[0-9]+$/', $params['cat'])
					|| is_null($this->_catMapper->getByID($params['cat'])->parentID))
					$this->_addElement('Kategoria', 'cat', 'category', (isset($params['cat']) ? $params['cat'] : 'all'), $this->_catMapper->getAll());
				else
				{
					$atts = $this->_catMapper->getByID($params['cat'])->getAttributes();
					
					$this->_addElement('Kategoria', 'cat', 'hidden', $params['cat']);
					
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
								$this->_addElement($att->name, $id, 'multiselect', (isset($params[$id]) && is_array($params[$id]) ? $params[$id] : array()), $att->options);
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
				$options = Zend_Registry::get('options');
				$this->_addElement('Ogłoszeń na stronę', 'per_page', 'select', (isset($params['per_page']) ? $params['per_page'] : $options['defaultAnnsPerPage']), array('25'=>'25', '50'=>'50', '100'=>'100'))
						->_addElement('', 'send', 'submit', 'Szukaj');
			}
		
		public function render($action)
			{
				return '<form action="' . $action . '" method="GET">' .
					implode('<br />', $this->_elements) .
					'</form>';
			}
		
		// type: category, text, minmax(tablica), select, multiselect (tablica), checkbox, hidden, submit
		// tablica - do name automatycznie dodawane '[]', $value jest tablicą
		protected function _addElement($label, $name, $type, $value, array $options = NULL)
			{
				$elStr = '';
				
				switch($type)
				{
					case 'text':
						$elStr = sprintf('%s: <input name="%s" type="text" value="%s" />',
							$label,
							$name,
							$value);
						break;
					
					case 'category':
						echo sprintf('dodaje category. <br />label: %s <br />name: %s <br />type: %s <br />value: %s <br />options:<pre> %s</pre>',
							print_r($label, true), print_r($name, true),
							print_r($type, true), print_r($value, true), print_r($options, true));
						$elStr = sprintf('%s: <select name="%s">', $label, $name);
						$elStr .= '<option value="all"' . ($value == 'all' ? ' selected' : '') . '>Wszystkie</option>';
						foreach($options as $ckey => $cat)
						{
							if(is_null($cat->parentID))
							{
								$elStr .= sprintf('<option value="%s"%s>%s</option>',
									$ckey,
									($ckey == $value ? ' selected' : ''),
									$cat->name);
								
								foreach($options as $chckey => $chcat)
								{
									if($chcat->parentID == $ckey)
									{
										$elStr .= sprintf('<option value="%s"%s>%s</option>',
											$chckey,
											($chckey == $value ? ' selected' : ''),
											'&gt&gt' . $chcat->name);
									}
								}
							}
						}
						$elStr .= '</select>';
						break;
					
					case 'hidden':
						$elStr = sprintf('<input name="%s" type="hidden" value="%s" />',
							$name,
							$value);
						break;
					
					case 'minmax':
						$elStr = sprintf('%s: od: <input name="%s[min]" type="text" value="%s"> do: <input name="%s[max]" type="text" value="%s">',
							$label,
							$name,
							(isset($value['min']) ? $value['min'] : ''),
							$name,
							(isset($value['max']) ? $value['max'] : ''));
						break;
					
					case 'multiselect':
						$elStr = sprintf('%s: <select name="%s[]" multiple>', $label, $name);
						foreach($options as $oid => $oname)
						{
							$elStr .= sprintf('<option value="%s"%s>%s</option>',
								$oid,
								(in_array($oid, $value) ? ' selected' : ''),
								$oname);
						}
						$elStr .= '</select>';
						break;
					
					case 'checkbox':
						$elStr = sprintf('<input name="%s" type="checkbox" value="1" id="%s"%s /><label for="%s">%s</label>',
							$name,
							'check' . $name,
							($value == '1' ? ' checked' : ''),
							'check' . $name,
							$label);
						break;
					
					case 'select':
						$elStr = sprintf('%s: <select name="%s">', $label, $name);
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
