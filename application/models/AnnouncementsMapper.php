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
				if(!preg_match('/[0-9]+/', $id))
					throw new Exception('Invalid announcement ID.');
				
				$ann = new Application_Model_Announcement($this->_db->fetchRow('SELECT * FROM announcements WHERE ID = ?', $id, Zend_Db::FETCH_ASSOC));
				
				$attDefs = $ann->getCategory()->getAttributes();
				$attValues = $this->_db->fetchAll('SELECT attID, intValue, textValue, floatValue FROM attributes_values WHERE annID = ?',
					$ann->ID, Zend_Db::FETCH_ASSOC);
				
				foreach($attValues as $att)
					$ann->attributes[$att['attID']] = $att[$attDefs[$att['attID']]->getTypeString() . 'Value'];
				
				$ann->images = $this->_db->fetchPairs('SELECT images.ID, images.name FROM images ' .
					'JOIN announcement_images ON (images.ID = announcement_images.imgID) WHERE announcement_images.annID = ?',
					$ann->ID);
				
				return $ann;
			}
	}

