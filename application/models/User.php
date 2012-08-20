<?php

class Application_Model_User {

	private $session;
	private $db;
	
	public function __construct() {
		$this -> db = Zend_Registry::get('db');
		//Sesje!!
		$this -> session = new Zend_Session_Namespace('UserData');
		if(!isset($this -> session -> auth)) {
			$this -> session -> auth = false;	
		}
		if($this -> session -> auth) {
			$this -> updateLastSeen();
		}
	}
	
	public function login($username, $password) {
		if($this -> session -> auth) { //Je�eli juz zalogowany to po co znowu logowac?
			throw new Exception('User is already logged in');
		}
		if(!preg_match('/[a-zA-Z0-9_]+/', $username)) {
			throw new Exception('Username contains forbidden characters');
		}
		$userData = $this -> db -> fetchRow('SELECT * FROM users WHERE username = ?', $username, Zend_Db::FETCH_ASSOC);
		if($userData === false || $userData['password'] != md5($password.$userData['salt'])) {
			$this -> session -> auth = false;
			return false;
			
		} else {
			$this -> session -> auth = true;
			$this -> session -> userID = $userData['ID'];
			$this -> session -> username = $userData['username'];
			$this -> session -> setExpirationSeconds(60 * 60 * 24 * 10);
			$this -> updateLastSeen();
			return true;
		}
	}
	
	public function logout() {
		$this -> session -> auth = false;
	}
	
	public function updateLastSeen() {
		$this -> db -> update('users', array('last_seen' => time()), 'ID = ' . $this -> session -> userID);
	}
	
	private function generateSalt() {
		mt_srand(microtime(true) * 10000);
		return md5(mt_rand(0, mt_getrandmax()) * 51539607551); //Bardzo losowo wybrana przezemnie liczba pierwsza
	}
	
	public function register($username, $password, $email) {
		if($this -> session -> auth) { //Je�eli zalogowany nie tworzymy konta
			throw new Exception('User is logged in');
		}
		if(!preg_match('/[a-zA-Z0-9_]+/', $username)) {
			throw new Exception('Username contains forbidden characters.');
		}
		if(!preg_match('/[a-zA-Z0-9_\-\.\+]+@[a-zA-Z0-9\.]+\.[a-zA-Z]{2,}/', $email)) {
			throw new Exception('Supplied email is invalid.');
		}
		//Sprawdz czy juz takie username jest zajete
		$result = $this -> db -> fetchRow('SELECT ID FROM users WHERE username = ?', $username, Zend_Db::FETCH_ASSOC);
		if($result !== false) {
			throw new Exception('User with username '.$username.' already exists.');
		}
		//Sprawdz czy takie email jest juz uzywany
		$result = $this -> db -> fetchRow('SELECT ID FROM users WHERE email = ?', $email, Zend_Db::FETCH_ASSOC);
		if($result !== false) {
			throw new Exception('Email '.$email.' is already in use.');
		}
		//Wszystko ok? to rejestrujemy
		$salt = $this -> generateSalt();
		$queryData = array($username, md5($password.$salt), $salt, $email, time(), time());
		$this -> db -> query('INSERT INTO users VALUES(null, ?, ?, ?, ?, ?, ?)', $queryData);
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