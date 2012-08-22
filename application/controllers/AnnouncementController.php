<?php

class AnnouncementController extends Zend_Controller_Action {

    public function init() {
		
	}

	public function indexAction() {
    	if(!isset($_GET['id']) || !preg_match('/^[0-9]+$/', $_GET['id']) ) {
    		$this -> message = "Missing or wrong ID.";
    		$this -> onlyMessage = true;
    	}
    	$this -> onlyMessage = false;
    	$announcementMapper = new Application_Model_AnnouncementsMapper();
    	$this -> view -> announcement = $announcementMapper -> getByID($_GET['id']); 
	}
    
    public function createAction() {
    	
    }
    
    public function editAction() {
    	
    }
    
    public function deleteAction() {
    	
    }

}