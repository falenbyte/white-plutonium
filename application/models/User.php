<?php

class Application_Model_User {

	private $userData;
	private $session;
	
	public function __construct() {
		$this -> session = new Zend_Session_Namespace('UserData');
		if(!isset($this -> session -> authenticated)) {
			$this -> session -> authenticated = false;	
		}
	}
	
	public function login($username, $password) {
		if($this -> session -> auth) { //Je¿eli zalogowany nie tworzymy konta
			throw new Exception('User is already logged in');
		}
		if(!preg_match('/[a-zA-Z0-9_]+/', $username)) {
			throw new Exception('Username contains forbidden characters');
		}
		$db = Zend_Registry::get('db');
		$userData = $db -> fetchRow('SELECT * FROM users WHERE username = ?', $username);
		if($userData === false || $userData['password'] != md5($password.$userData['salt'])) {
			$this -> session -> auth = false;
			return false;
			
		} else {
			$this -> session -> auth = true;
			$this -> session -> userID = $userData['ID'];
			$this -> session -> username = $userData['username'];
			$this -> session -> setExpirationSeconds(60 * 60 * 24 * 10);
			return true;
		}
	}
	
	public function logout() {
		$this -> session -> auth = false;
	}
	
	private function generateSalt() {
		mt_srand(microtime(true) * 10000);
		return md5(mt_rand(0, mt_getrandmax()) * 51539607551);
	}
	
	public function register($username, $password, $email) {
		if($this -> session -> auth) { //Je¿eli zalogowany nie tworzymy konta
			throw new Exception('User is logged in');
		}
		if(!preg_match('/[a-zA-Z0-9_]+/', $username)) {
			throw new Exception('Username contains forbidden characters.');
		}
		// TODO: poprawiæ wyra¿enie regularne
		if(!preg_match('/[a-zA-Z0-9_-\.\+]+@[a-zA-Z0-9\.]+\.[a-zA-Z]{2,}/', $email)) {
			throw new Exception('Supplied email is invalid.');
		}
		$db = Zend_Registry::get('db');
		$salt = $this -> generateSalt();
		$queryData = array($username, md5($password.$salt), $salt, $email, time(), time());
		$db -> query('INSERT INTO users VALUES(null, ?, ?, ?, ?, ?, ?)', $queryData);
	}
	
	public function isLoggedIn() {
		return $this -> session -> auth;
	}
	
	public function getUserID() {
		return $this -> session -> auth ? $this -> session -> userID : false;
	}
	
	public function getUserName() {
		return $this -> session -> auth ? $this -> session -> username : false;
	}
}