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
			
		public function save(Application_Model_Announcement $ann)
			{
				$data = array(
						'ID' => is_numeric($ann -> ID) ? $ann -> ID : null,
						'catID' => $ann -> catID,
						'userID' => $ann -> userID === false? null : $ann -> userID,
						'title' => $ann -> title,
						'content' =>$ann -> content,
						'date' => time(),
						'expires' => time()+(60*60*24*7));
				$this -> _db -> insert('announcements', $data);
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
					$this->_db->delete('images', 'ID IN(' . implode(', ', $));
				}
				//$out = $this -> _db -> delete('announcements', 'ID = ' . $id . ' AND userID = ' . $userModel -> getUserID());
			}
	}

