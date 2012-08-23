<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

	protected function _initConfiguration() {
		error_reporting(E_ALL ^ E_NOTICE);
		date_default_timezone_set('Europe/Paris');
	}
	
	protected function _initOptions() {
		Zend_Registry::set('options', $this->getOption('my'));
	}
	
	protected function _initSession() {
		Zend_Session::start();
	}
	
	protected function _initDB() {
		//Globalny dostęp do bazy danych
		$dbConfig = new Zend_Config_Ini('../application/local_db_config.ini', 'database_config');
		$db = Zend_Db::factory($dbConfig -> database);
		$db -> query('SET NAMES utf8');
		$db -> query('SET CHARACTER SET utf8');
		Zend_Registry::set('db', $db);
	}
	
	protected function _initUserModel() {
		//Globalny dostęp do obiektu z danymi zalogowanego użytkownika
		Zend_Registry::set('userModel', new Application_Model_User());
	}
	
	protected function _initEmail() {
		$emailConfig = new Zend_Config_Ini('../application/local_email_config.ini', 'email_config');
		$mailTransport = new Zend_Mail_Transport_Sendmail($emailConfig -> server, $emailConfig -> config);
		Zend_Mail::setDefaultTransport($mailTransport);
		Zend_Registry::set('email_sender', $emailConfig -> sender);
	}

}
