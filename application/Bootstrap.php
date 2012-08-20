<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

	protected function _initConfiguration() {
		error_reporting(E_ALL ^ E_NOTICE);
	}
	
	protected function _initSession() {
		Zend_Session::setOptions(array('strict' => true));
		Zend_Session::start();
	}
	
	protected function _initDB() {
		//Globalny dost�p do bazy danych
		$dbConfig = new Zend_Config_Ini('../application/local_db_config.ini', 'database_config');
		Zend_Registry::set('db', Zend_Db::factory($dbConfig -> database));
	}

}
