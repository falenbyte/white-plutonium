<?php

class Zend_View_Helper_SearchForm extends Zend_View_Helper_Abstract
	{
		public function searchForm()
			{
				$params = $_GET;
        
				foreach($params as $key => &$value)
				{	
					if(preg_match('/^[0-9]+$/', $key))
					{
						foreach($value as $akey => $aval)
						{
							if($aval == '')
								unset($value[$akey]);
						}
					}
					else if($value == '')
						unset($params[$key]);
				}
				
				$form = new Application_Form_Search($params);
				return $form->render($this->view->url(array('controller'=>'search', 'action'=>'index')));
			}
	}
