<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

	protected function _initSystem() {
		error_reporting(E_ALL ^ E_NOTICE);
	}
	
	protected function _initDB() {
		//Globalny dostêp do bazy danych
		$dbConfig = new Zend_Config_Ini('../application/local_database_config.ini', 'database_config');
		Zend_Registry::set('db', Zend_Db::factory($dbConfig -> database));
	}

}