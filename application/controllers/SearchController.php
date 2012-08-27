<?php

class SearchController extends Zend_Controller_Action
{

	public function init()
	{
		$this -> view -> searchPage = true;
	}

	public function indexAction()
	{
		$this->view->message = '';

		$params = ($this->getRequest()->isGet() ? $this->getRequest()->getQuery() : array());
			
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
			
		$filters = new Application_Model_SearchFilters($params);
		$annMapper = new Application_Model_AnnouncementsMapper();
		$this->view->anns = $annMapper->getByFilters($filters);
			
		//$this->view->message .= $filters->getQueryString();
	}


}

