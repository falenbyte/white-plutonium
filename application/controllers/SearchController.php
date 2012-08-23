<?php

class SearchController extends Zend_Controller_Action
{

    public function init()
    {
        
    }

    public function indexAction()
    {
        
        $params = ($this->getRequest()->isGet() ? $this->getRequest()->getQuery() : array());
        
        foreach($params as $key => $value)
        {	
			if(preg_match('/^[0-9]+$/', $key))
			{
				foreach($value as $akey => $aval)
				{
					if($aval === '')
						unset($value[$akey]);
				}
			}
			else if($value === '')
				unset($params[$key]);
		}
		
		try
		{
			$filters = new Application_Model_SearchFilters($params);
				$this->view->message = print_r($filters->getQueryString(), true) . "\n\n";
			
			//$annMapper = new Application_Model_AnnouncementsMapper();
			
			//$this->view->anns = $annMapper->getByFilters($filters);
				//$this->view->message .= print_r($this->view->anns, true) . "\n\n";
			
			//$this->view->searchForm = new Application_Form_Search($params);
		}
		catch (Exception $e)
		{
			$this->view->message = $e->getMessage();
		}
    }


}

