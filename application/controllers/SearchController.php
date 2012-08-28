<?php

class SearchController extends Zend_Controller_Action
{

	public function init()
	{
		$this -> view -> searchForm = true;
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
		if(isset($params['cat']) && preg_match('/^[0-9]+$/', $params['cat'])) {
			$attMapper = new Application_Model_AttributesMapper();
			$this -> view -> atts = $attMapper -> getByCategoryID($params['cat']);
		}

		//$this->view->message .= $filters->getQueryString();
	}


}

