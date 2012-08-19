<?php

class Application_Model_AnnouncementsMapper
	{
		private $_db;

		public function __construct()
			{
				$this->_db = Zend_Registry::get('db');
			}
	}

