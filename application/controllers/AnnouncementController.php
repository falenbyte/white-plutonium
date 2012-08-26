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
						
						$imagesModel = new Application_Model_Images();
						
						if(isset($_POST['images_to_delete']))
						{
							$imagesModel->deleteImages($_POST['images_to_delete'], $this->view->baseUrl('imgs/'));
							
							foreach($images as $id => $name)
							{
								if(in_array($id, $_POST['images_to_delete']))
									unset($images[$id]);
							}
						}
						
						if(is_array($_FILES['uploaded']))
						{
							$uploaded = $imagesModel->saveImages($_FILES['uploaded']['tmp_name'], $_FILES['uploaded']['size'],
								$this->view->baseUrl('imgs/'));
						}
						
						$images = array_merge($images, $uploaded);
					}
					else
					{
						$this->view->stage = 3; // koniec, sukces, wyświetl link do ogłoszenia
						try
						{
							$annObj = new Application_Model_Announcement();
							$annObj->ID = 0;
							$annObj->userID = $user->getUserID();
							$annObj->catID = $_POST['catID'];
							$annObj->title = $_POST['title'];
							$annObj->content = $_POST['content'];
							$annObj->images = (isset($_POST['images']) ? $_POST['images'] : array());
							
							foreach($_POST as $key => $value)
							{
								if(preg_match('/^[0-9]+$/', $key))
									$atts[$key] = $value;
							}
							
							$annObj->attributes = $atts;
							
							$annMapper = new Application_Model_AnnouncementsMapper();
							$this->view->createdID = $annMapper->save($annObj);
						}
						catch(Exception $e)
						{
							$this->view->stage = 4; //nieudany zapis, zmieniamy stage
							$this->view->message = $e->getMessage();
						}
					}
				}
			}
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
