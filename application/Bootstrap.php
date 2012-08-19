<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

	protected function _initDB() {
		//Globalny dost�p do bazy danych
		$dbConfig = new Zend_Config_Ini('local_database_config.ini');
		Zend_Registry::set('db', Zend_Db::factory($dbConfig -> database));
	}
	
}