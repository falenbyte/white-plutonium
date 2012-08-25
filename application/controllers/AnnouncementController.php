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
			$subCats = $catMapper->getAllSubCategories();
			
			if(!$user->isLoggedIn())
				$this->_redirect('account');
			else if(!isset($_POST['catID'])
				|| !preg_match('/^[0-9]+$/', $_POST['catID'])
				|| is_null($catMapper->getByID($_POST['catID'])->parentID))
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
									if($value != '' && !$att->validateValue($value))
									{
										$valid = false;
										$messages[] = 'Niepoprawna wartość atrybutu "' . strtolower($att->name) . '".';
									}
								}
								break;
						}
					}
				}
				
				if(!$valid)
				{
					$this->view->stage = 1; // ustawianie treści ogłoszenia
					$this->view->category = $catMapper->getByID($_POST['catID']);
					$this->view->attributes = $attMapper->getByCategoryID($_POST['catID']);
					$this->view->messages = $messages;
				}
				else
				{
					if(!isset($_POST['finish']))
					{
						$this->view->stage = 2; // dodawanie obrazków
						$images = (isset($_POST['images']) ? $_POST['images'] : array());
						
						//przekaż modelowi obrazków tablice images_to_delete z id obrazkow do usuniecia
						
						if(isset($_POST['images_to_delete']))
						{
							foreach($images as $id => $name)
							{
								if(in_array($id, $_POST['images_to_delete']))
									unset($images[$id]);
							}
						}
						
						// wrzuć nowe obrazki i dodaj do images
					}
					else
					{
						$this->view->stage = 3; // koniec, sukces, wyświetl link do ogłoszenia
						try
						{
							// zapis
						}
						catch(Exception $e)
						{
							$this->view->stage = 4; //nieudany zapis, zmieniamy stage
							$this->view->message = $e->getMessage();
						}
					}
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
