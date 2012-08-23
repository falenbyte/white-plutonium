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
    	$this -> view -> selectCategory = (!isset($_GET['category_id']));	
    	if(isset($_POST['title'], $_POST['content'])) {
    		try {
    			$ann = new Application_Model_Announcement();
    			$ann -> ID = null;
    			$ann -> title = $_POST['title'];
    			$ann -> content = $_POST['content'];
    			$ann -> catID = $_POST['category_id'];
    			$ann -> userID = Zend_Registry::get('userModel') -> getUserID();
    			$mapper = new Application_Model_AnnouncementsMapper();
    			$mapper -> save($ann);
    			$this -> view -> message = "Announcement created.";
    			$this -> view -> onlyMessage = true;
    		} catch(Exception $e) {
    			$this -> view -> message = $e -> getMessage();
    		}
    	}
    	$mapper = new Application_Model_CategoriesMapper();
    	$this -> view -> categories = $mapper -> getAllSubCategories();
    }
    
    public function editAction() {
    	
    }
    
    public function deleteAction() {
    	
    }

}