<?php

class AnnouncementController extends Zend_Controller_Action {

	private $_messages;

	public function init() {
		$this -> _messages = Zend_Registry::get('messages') -> announcement;
	}

	public function indexAction() {
		$this -> view -> searchForm = true;
		if(!isset($_GET['id']) || !preg_match('/^[0-9]+$/', $_GET['id']) ) {
			$this -> message = "Missing or wrong ID.";
			$this -> onlyMessage = true;
			return;
		}
		$this -> onlyMessage = false;
		$announcementMapper = new Application_Model_AnnouncementsMapper();
		$this -> view -> announcement = $announcementMapper -> getByID($_GET['id']);
		$_GET['cat'] = $this -> view -> announcement -> catID;
		$attMapper = new Application_Model_AttributesMapper();
		$this->view->attributes = $attMapper->getByCategoryID($this -> view -> announcement->catID);
	}

	public function createAction()
	{
		$this -> view -> scripts = array('/ckeditor/ckeditor.js');
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
						$imagesModel->deleteImages($_POST['images_to_delete'], '../public/imgs/');

						foreach($images as $id => $name)
						{
							if(in_array($id, $_POST['images_to_delete']))
								unset($images[$id]);
						}
					}
					if(is_array($_FILES['uploaded']))
					{
						$uploaded = $imagesModel->saveImages($_FILES['uploaded']['tmp_name'], $_FILES['uploaded']['size'],
								'../public/imgs/');

						$images = $images + $uploaded;
					}

					$this->view->images = $images;
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

	public function editAction()
	{
		$this -> view -> scripts = array('/ckeditor/ckeditor.js');
		$catMapper = new Application_Model_CategoriesMapper();
		$attMapper = new Application_Model_AttributesMapper();
		$annMapper = new Application_Model_AnnouncementsMapper();
		$user = Zend_Registry::get('userModel');

		if(isset($_GET['id']) && preg_match('/^[0-9]+$/', $_GET['id']))
			$ann = $annMapper->getByID($_GET['id']);
		else if(isset($_POST['id']) && preg_match('/^[0-9]+$/', $_POST['id']))
			$ann = $annMapper->getByID($_POST['id']);
		else
			$this->_redirect('index');

		if(!$user->isLoggedIn())
			$this->_redirect('account');
		else if(isset($ann) && $user->getUserID() == $ann->userID)
		{
			$messages = array();
			$attDefs = $attMapper->getByCategoryID($ann->catID);

			$valid = true;

			$title = isset($_POST['title']) ? $_POST['title'] : $ann->title;
			if($title == '')
			{
				$valid = false;
				$messages[] = 'Musisz podać tytuł ogłoszenia.';
			}

			$content = isset($_POST['content']) ? $_POST['content'] : $ann->content;
			if($content == '')
			{
				$valid = false;
				$messages[] = 'Musisz podać treść ogłoszenia.';
			}

			foreach($attDefs as $aid => $aval)
			{
				if(isset($_POST[$aid]))
					$attributes[$aid] = $_POST[$aid];
				else if(isset($ann->attributes[$aid]) && !isset($_POST['done']))
					$attributes[$aid] = $ann->attributes[$aid];

				if(isset($attributes[$aid]) && $attributes[$aid] != '' && !$attDefs[$aid]->validateValue($attributes[$aid]))
				{
					$valid = false;
					$messages[] = 'Niepoprawna wartość atrybutu "' . strtolower($attDefs[$aid]->name) . '".';
				}
			}

			if(!$valid || !isset($_POST['done']))
			{
				$this->view->stage = 1; // ustawianie treści ogłoszenia
				$this->view->category = $catMapper->getByID($ann->catID);
				$this->view->attributes = $attDefs;
				$this->view->messages = $messages;
				$this->view->annID = $ann->ID;
				$this->view->annTitle = $title;
				$this->view->annContent = $content;
				$this->view->annAttributes = $attributes;
			}
			else
			{
				if(!isset($_POST['finish']))
				{
					$this->view->stage = 2; // dodawanie obrazków
					$this->view->annID = $ann->ID;

					$images = (isset($_POST['images']) ? $_POST['images'] : $ann->images);

					$imagesModel = new Application_Model_Images();

					if(isset($_POST['images_to_delete']))
					{
						$imagesModel->deleteImages($_POST['images_to_delete'], '../public/imgs/');

						foreach($images as $id => $name)
						{
							if(in_array($id, $_POST['images_to_delete']))
								unset($images[$id]);
						}
					}

					if(is_array($_FILES['uploaded']))
					{
						$uploaded = $imagesModel->saveImages($_FILES['uploaded']['tmp_name'], $_FILES['uploaded']['size'],
								'../public/imgs/');

						$images = $images + $uploaded;
					}

					$this->view->images = $images;
				}
				else
				{
					$this->view->stage = 3; // koniec, sukces, wyświetl link do ogłoszenia
					$this->view->annID = $ann->ID;
					try
					{
						$ann->catID = $_POST['catID'];
						$ann->title = $_POST['title'];
						$ann->content = $_POST['content'];
						$ann->images = (isset($_POST['images']) ? $_POST['images'] : array());

						foreach($_POST as $key => $value)
						{
							if(preg_match('/^[0-9]+$/', $key))
								$atts[$key] = $value;
						}

						$ann->attributes = $atts;
						$annMapper->save($ann);
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

	public function deleteAction() {
		try {
			if(!isset($_GET['id'])) {
				throw new Exception($this -> _messages -> missingID);
			}
			$mapper = new Application_Model_AnnouncementsMapper();
			$mapper -> delete($_GET['id']);
			throw new Exception($this -> _messages -> deleted);
		} catch(Exception $e) {
			$this -> view -> message = $e -> getMessage();
		}
	}

}
