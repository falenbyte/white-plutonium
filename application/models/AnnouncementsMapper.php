<?php

class Application_Model_AnnouncementsMapper
{
	private $_db;
	private $_messages;

	public function __construct()
	{
		$this -> _db = Zend_Registry::get('db');
		$this -> _messages = Zend_Registry::get('messages') -> annMapper;
	}

	public function getByID($id)
	{
		if(!preg_match('/^[0-9]+$/', $id))
			throw new Exception($this -> _messages -> invalidID);

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

			$attributes = array();
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
			throw new Exception($this -> _messages -> wrongParameter);
		}
		if(empty($list)) {
			return null;
		}
		foreach($list as $id) {
			if(!preg_match('/^[0-9]+$/', $id)) {
				throw new Exception($this -> _messages -> invalidID);
			}
		}
		$result = $this -> _db -> fetchAll('SELECT * FROM announcements WHERE ID IN (' . implode(', ', $list) . ')', null, Zend_Db::FETCH_ASSOC);
		if($result === false) {
			return null;
		}
		$attMapper = new Application_Model_AttributesMapper();
		$attDefs = $attMapper->getAll();
		foreach($result as $row) {
			$ann = new Application_Model_Announcement($row);
			$attValues = $this->_db->fetchAll('SELECT attID, intValue, textValue, floatValue FROM attributes_values WHERE annID = ?',
					$ann->ID, Zend_Db::FETCH_ASSOC);
			$attributes = array();
			foreach($attValues as $att)
				$attributes[$att['attID']] = $att[$attDefs[$att['attID']]->getTypeString() . 'Value'];
			$ann->attributes = $attributes;

			$ann -> images = $this->_db->fetchPairs('SELECT images.ID, images.name FROM images ' .
					'JOIN announcement_images ON (images.ID = announcement_images.imgID) WHERE announcement_images.annID = ?',
					$row['ID']);
			$anns[] = $ann;
		}
		return $anns;
	}

	public function save(Application_Model_Announcement $ann)
	{
		$user = Zend_Registry::get('userModel');
		if(!$user->isLoggedIn())
			throw new Exception($this -> _messages -> notLoggedIn);

		if(!preg_match('/^[0-9]+$/', $ann->ID)
				|| $ann->ID == 0
				|| $this->_db->fetchOne('SELECT COUNT(*) FROM announcements WHERE ID = ?', $ann->ID) == 0)
			$createNew = true;
		else
			$createNew = false;

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
							'expires' => time()+(60*60*24*7)
					));

			$newID = $this->_db->lastInsertId('announcements', 'ID');
			if(is_array($ann -> attributes) && !empty($ann -> attributes)) {
				foreach($ann->attributes as $key => $att) {
					$this->_db->insert('attributes_values', array('annID' => $newID, 'attID'=>$key, ($attMapper->getByID($key)->getTypeString() . 'Value') => $att));
				}
			}

			if(is_array($ann -> images) && !empty($ann -> images)) {
				foreach($ann->images as $key => $img) {
					$this->_db->insert('announcement_images', array('annID' => $newID, 'imgID' => $key));
				}
			}
			return $newID;
		}
		else
		{
			$ownerID = $this->_db->fetchOne('SELECT userID FROM announcements WHERE ID = ?', $ann->ID);
			if($user->getUserID() != $ownerID)
				throw new Exception($this -> _messages -> cannotEdit);

			$this->_db->update('announcements',
					array(
							'title' => $ann -> title,
							'content' => $ann -> content),
					'ID = ' . $ann->ID);

			$this->_db->delete('attributes_values', 'annID = ' . $ann->ID);
			if(is_array($ann -> attributes) && !empty($ann -> attributes)) {
				foreach($ann->attributes as $key => $att) {
					$this->_db->insert('attributes_values', array('annID' => $ann->ID, 'attID'=>$key, ($attMapper->getByID($key)->getTypeString() . 'Value') => $att));
				}
			}

			$this->_db->delete('announcement_images', 'annID = ' . $ann->ID);
			if(is_array($ann -> images) && !empty($ann -> images)) {
				foreach($ann->images as $key => $img) {
					$this->_db->insert('announcement_images', array('annID' => $ann->ID, 'imgID' => $key));
				}
			}
		}
	}

	public function delete($id)
	{
		if(!preg_match('/^[0-9]+$/', $id)) {
			throw new Exception($this -> _messages -> invalidID);
		}

		$userModel = Zend_Registry::get('userModel');
		$owner = $this->_db->fetchOne('SELECT userID FROM announcements WHERE ID = ?', $id);
		if($owner === false) {
			throw new Exception($this -> _messages -> annDoesNotExists);
		}
		if(!$userModel -> isAdmin() && (!$userModel -> isLoggedIn() || $owner != $userModel->getUserID())) {
			throw new Exception($this -> _messages -> cannotDelete);
		}

		$images = $this->_db->fetchCol('SELECT imgID from announcement_images WHERE annID = ?', $id);
		if(count($images) > 0)
		{
			$this->_db->delete('images', 'ID IN(' . implode(', ', $images) . ')');
		}

		$this -> _db -> delete('announcements', 'ID = ' . $id);
	}
}
