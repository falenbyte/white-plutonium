<?php

class AnnouncementController extends Zend_Controller_Action {

    public function init() {
		
	}

	public function indexAction() {
    	if(!isset($_GET['id']) || !preg_match('/^[0-9]+$/', $_GET['id']) ) {
    		$this -> message = "Missing or wrong ID.";
    		$this -> onlyMessage = true;
    		return;
    	}
    	$this -> onlyMessage = false;
    	$announcementMapper = new Application_Model_AnnouncementsMapper();
    	$this -> view -> announcement = $announcementMapper -> getByID($_GET['id']);
    	
    	$attMapper = new Application_Model_AttributesMapper();
    	$this->view->attributes = $attMapper->getByCategoryID($this -> view -> announcement->catID);
	}
    
    public function createAction()
		{
			$catMapper = new Application_Model_CategoriesMapper();
			$attMapper = new Application_Model_AttributesMapper();
			$user = Zend_Registry::get('userModel');
			
			if(!$user->isLoggedIn())
				$this->_redirect('account');
			else if(!isset($_POST['catID'])
				|| !preg_match('/^[0-9]+$/', $_POST['stage'])
				|| !in_array($_POST['catID'], (is_array($subCats = $catMapper->getAllSubCategories()) ? $subCats : array()))
				)
			{
				$this->view->stage = 0; // wybór kategorii
				$this->view->categories = $catMapper->getAll();
			}
			else
			{
				$messages = array();
				
				$valid = (isset($_POST['title']) && isset($_POST['content']));
				
				if($valid)
				{
					foreach($_POST as $key => $value)
					{
						switch($key)
						{
							case 'title':
								if($value == '')
								{
									$valid = false;
									$messages[] = 'Musisz podać tytuł ogłoszenia.';
								}
								break;
							
							case 'content':
								if($value == '')
								{
									$valid = false;
									$messages[] = 'Musisz podać treść ogłoszenia.';
								}
								break;
							
							default:
								if(preg_match('/^[0-9]+$/', $key))
								{
									$att = $attMapper->getByID($key);
									if(($valid = $att->validateValue($value)) == false)
										messages[] = 'Niepoprawna wartość atrybutu "' . strtolower($att->name) . '".';
								}
								break;
						}
					}
				}
				
				if(!$valid)
				{
					$this->view->stage = 1; // ustawianie treści ogłoszenia
				}
				else
				{
					$this->view->stage = 2; // dodawanie obrazków
				}
			}
			
			
			/*$this -> view -> selectCategory = (!isset($_GET['category_id']));
			if(isset($_POST['title'], $_POST['content'], $_GET['category_id'])) {
				try {
					$ann = new Application_Model_Announcement();
					$ann -> ID = null;
					$ann -> title = $_POST['title'];
					$ann -> content = $_POST['content'];
					$ann -> catID = $_GET['category_id'];
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
			$this -> view -> categories = $mapper -> getAllSubCategories();*/
		}
    
    public function editAction() {
    	
    }
    
    public function deleteAction() {
    	try {
    		if(!isset($_GET['id'])) {
    			throw new Exception('Missing announcemnet ID');
    		}
    		$mapper = new Application_Model_AnnouncementsMapper();
    		$mapper -> delete($_GET['id']);
    	} catch(Exception $e) {
    		$this -> view -> message = $e -> getMessage();
    	}
    }

}
