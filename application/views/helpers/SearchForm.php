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

		$catMapper = new Application_Model_CategoriesMapper();
		$special = (isset($params['cat'])
				&& preg_match('/^[0-9]+$/', $params['cat'])
				&& !is_null($catMapper->getByID($params['cat'])->parentID));
		$result = '<div id="search_form">';
		$form = new Application_Form_Search($params, (!$special));
		$result .= $form->render($this->view->url(array('controller'=>'search', 'action'=>'index')));
		$result .= '</div>';
		return $result;
	}
}
