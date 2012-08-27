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
		$showSpecial = ($special && isset($params['form_type']) && $params['form_type'] == 'spec');

		$basicForm = new Application_Form_Search($params, true);
		$result = '<div id="basic_search_form"' . ($showSpecial ? ' style="display: none;"' : '') . '>' .
				$basicForm->render($this->view->url(array('controller'=>'search', 'action'=>'index')));

		if($special)
		{
			$result .= '<a href="#" onclick="specialSearch()">Zaawansowane wyszukiwanie</a></div>';
				
			$specForm = new Application_Form_Search($params);
			$result .= '<div id="special_search_form"' . ($showSpecial ? '' : ' style="display: none;"') . '>' .
					$specForm->render($this->view->url(array('controller'=>'search', 'action'=>'index'))) .
					'<a href="#" onclick="basicSearch()">Proste wyszukiwanie</a></div>';
		}
		else
			$result .= '</div>';

		return $result;
	}
}
