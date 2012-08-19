<?php

class Application_Model_User {

	private $userData;
	
	public function __construct() {
		
	}
	
	public function login($username, $password) {
		if(!preg_match('/[a-zA-Z0-9_]+/', $username)) {
			throw new Exception('Username contains forbidden characters');
		}
		$db = Zend_Registry::get('db');
		$userData = $db -> fetchRow('SELECT * FROM users WHERE username = ?', $username);
		if($userData === false || $userData['password'] != md5($password.$userData['salt'])) {
			return false;
		} else {
			$this -> userData = $userData;
			return true;
		}
	}
	
	public function logout() {
		
	}
	
	private function generateSalt() {
		mt_srand(microtime(true) * 10000);
		return md5(mt_rand(0, mt_getrandmax()) * 51539607551);
	}
	
	public function register($username, $password, $email) {
		if(!preg_match('/[a-zA-Z0-9_]+/', $username)) {
			throw new Exception('Username contains forbidden characters.');
		}
		// TODO: poprawiæ wyra¿enie regularne
		if(!preg_match('/[a-zA-Z0-9_]+@[a-zA-Z0-9-]+\.[a-zA-Z]{2,}/', $email)) {
			throw new Exception('Supplied email is invalid.');
		}
		$db = Zend_Registry::get('db');
		$salt = $this -> generateSalt();
		$queryData = array($username, md5($password.$salt), $salt, $email, time(), time());
		$db -> query('INSERT INTO users VALUES(null, ?, ?, ?, ?, ?, ?)', $queryData);
	}
	
	public function isLoggedIn() {
		return isset($this -> userData['ID']);
	}
	
	public function getUserID() {
		return isset($this -> userData['ID']) ? $this -> userData['ID'] : false;
	}

}