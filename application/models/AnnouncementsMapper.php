<?php

class Application_Model_AnnouncementsMapper
	{
		private $_db;

		public function __construct()
			{
				$this->_db = Zend_Registry::get('db');
			}
		
		public function getByID($id)
			{
				if(!preg_match('/^[0-9]+$/', $id))
					throw new Exception('Invalid announcement ID.');
				
				$ann = new Application_Model_Announcement($this->_db->fetchRow('SELECT * FROM announcements WHERE ID = ?', $id, Zend_Db::FETCH_ASSOC));
				
				$attDefs = $ann->getCategory()->getAttributes();
				$attValues = $this->_db->fetchAll('SELECT attID, intValue, textValue, floatValue FROM attributes_values WHERE annID = ?',
					$ann->ID, Zend_Db::FETCH_ASSOC);
				
				foreach($attValues as $att)
					$attributes[$att['attID']] = $att[$attDefs[$att['attID']]->getTypeString() . 'Value'];
				
				$ann->attributes = $attributes;
				
				$ann->images = $this->_db->fetchPairs('SELECT images.ID, images.name FROM images ' .
					'JOIN announcement_images ON (images.ID = announcement_images.imgID) WHERE announcement_images.annID = ?',
					$ann->ID);
				
				return $ann;
			}
		
		public function getByFilters(Application_Model_SearchFilters $filters)
			{
				$result = $this->_db->fetchAll($filters->getQueryString(), Zend_Db::FETCH_ASSOC);
				
				$attMapper = new Application_Model_AttributesMapper();
				$attDefs = $attMapper->getAll();
				
				foreach($result as $row)
				{	
					$ann = new Application_Model_Announcement($row);
					
					$attValues = $this->_db->fetchAll('SELECT attID, intValue, textValue, floatValue FROM attributes_values WHERE annID = ?',
						$ann->ID, Zend_Db::FETCH_ASSOC);
					
					foreach($attValues as $att)
						$attributes[$att['attID']] = $att[$attDefs[$att['attID']]->getTypeString() . 'Value'];
					
					$ann->attributes = $attributes;
					
					$ann->images = $this->_db->fetchPairs('SELECT images.ID, images.name FROM images ' .
						'JOIN announcement_images ON (images.ID = announcement_images.imgID) WHERE announcement_images.annID = ?',
						$ann->ID);
					
					$anns[] = $ann;
				}
				
				return $anns;
			}
			
		public function getListByIDs($list) {
			if(!is_array($list)) {
				throw new Exception('Wrong parameter.');
			}
			if(empty($list)) {
				return null;
			}
			foreach($list as $id) {
				if(!preg_match('/^[0-9]+$/', $id)) {
					throw new Exception('One of supplied ID\'s is wrong');
				}
			}
			$result = $this -> _db -> fetchAll('SELECT * FROM announcements WHERE ID IN (' . implode(', ', $list) . ')', null, Zend_Db::FETCH_ASSOC);
			if($result === false) {
				return null;
			}
			foreach($result as $row) {
				$return[$row['ID']] = new Application_Model_Announcement($row);
			}
			return $return;
		}
			
		public function save(Application_Model_Announcement $ann)
			{
				if(!preg_match('/^[0-9]+$/', $ann->ID)
					|| $ann->ID == 0
					|| $this->_db->fetchOne('SELECT COUNT(*) FROM announcements WHERE ID = ?', $ann->ID) == 0)
					$createNew = true;
				else
					$createNew = false;
				
				$user = Zend_Registry::get('userModel');
				$attMapper = new Application_Model_AttributesMapper();
				
				if($createNew)
				{
					$this->_db->insert('announcements',
						array(
							'catID' => $ann -> catID,
							'userID' => $ann->userID,
							'title' => $ann -> title,
							'content' => $ann -> content,
							'date' => time(),
							'expires' => time()+(60*60*24*7),
						));
					
					$newID = $this->_db->lastInsertId('announcements', 'ID');
					
					foreach($ann->attributes as $key => $att)
						$this->_db->insert('attributes_values', array('annID' => $newID, ($attMapper->getByID($key)->getTypeString() . 'Value') => $att));
					
					foreach($ann->images as $key => $img)
						$this->_db->insert('announcement_images', array('annID' => $newID, 'imgID' => $key));
				}
				else
				{
					$ownerID = $this->_db->fetchOne('SELECT userID FROM announcements WHERE ID = ?', $ann->ID);
					
					if($user->getUserID() != $ownerID)
						throw new Exception('You cannot edit this announcement.');
					
					$this->_db->update('announcements',
						array(
							'title' => $ann -> title,
							'content' => $ann -> content),
						'ID = ' . $ann->ID);
					
					$this->_db->delete('attributes_values', 'annID = ' . $ann->ID);
					foreach($ann->attributes as $key => $att)
						$this->_db->insert('attributes_values', array('annID' => $ann->ID, ($attMapper->getByID($key)->getTypeString() . 'Value') => $att));
					
					$this->_db->delete('announcement_images', 'annID = ' . $ann->ID);
					foreach($ann->images as $key => $img)
						$this->_db->insert('announcement_images', array('annID' => $ann->ID, 'imgID' => $key));
				}
			}
		
		public function delete($id)
			{
				if(!preg_match('/^[0-9]+$/', $id)) {
					throw new Exception('Invalid Announcement ID.');
				}
				
				$userModel = Zend_Registry::get('userModel');
				$owner = $this->_db->fetchOne('SELECT userID FROM announcements WHERE ID = ?', $id);

				if(!$userModel -> isLoggedIn() || $owner != $userModel->getUserID()) {
					throw new Exception('You don\'t have permission to delete that announcement.');
				}

				$images = $this->_db->fetchCol('SELECT imgID from announcement_images WHERE annID = ?', $id);
				if(count($images) > 0)
				{
					$this->_db->delete('images', 'ID IN(' . implode(', ', $images) . ')');
				}
				
				$this -> _db -> delete('announcements', 'ID = ' . $id);
			}
	}
