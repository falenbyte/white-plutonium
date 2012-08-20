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
		if($this -> session -> auth) { //Je¿eli juz zalogowany to po co znowu logowac?
			throw new Exception('User is already logged in');
		}
		if(!preg_match('/[a-zA-Z0-9_]+/', $username)) {
			throw new Exception('Username contains forbidden characters');
		}
		$userData = $this -> db -> fetchRow('SELECT * FROM users WHERE username = ?', $username, Zend_Db::FETCH_ASSOC);
		if($userData === false || $userData['password'] != $this -> makePasswordHash($password, $userData['salt'])) {
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

	private function makePasswordHash($password, $salt) {
		return md5($password.$salt);
	}
	
	private function generateSalt() {
		mt_srand(microtime(true) * 10000);
		return md5(mt_rand(0, mt_getrandmax()) * 51539607551); //Bardzo losowo wybrana przezemnie liczba pierwsza
	}

	public function register($username, $password, $email) {
		if($this -> session -> auth) { //Je¿eli zalogowany nie tworzymy konta
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

	public function changePassword($oldPassword, $newPassword) {
		if(!$this -> session -> auth) {
			throw new Exception('User is not logged in.');
		}
		$userData = $this -> db -> fetchRow('SELECT * FROM users WHERE ID = ?', $this -> session -> userID, Zend_Db::FETCH_ASSOC);
		if($this -> makePasswordHash($oldPassword, $userData['salt']) == $userData['password']) {
			$this -> db -> update('users', array('password' => $this -> makePasswordHash($newPassword, $userData['salt'])), 'ID = ' . $this -> session -> userID);
		} else {
			throw new Exception('Supplied password does not match the one in database.');
		}
	}

	public function changeLostPassword($key, $newPassword) {
		if(!preg_match('/[a-zA-Z0-9]{32}/', $key)) {
			throw new Exception('Supplied key is invalid.');
		}
		$keyData = $this -> db -> fetchRow('SELECT * FROM lost_password_keys WHERE key = ?', $key, Zend_Db::FETCH_ASSOC);
		if($keyData === false) {
			throw new Exception('Supplied key is not in database');
		}
		if($keyData['expires'] < time()) {
			throw new Exception('Supplied key expired.');
		}
		$userData = $this -> db -> fetchRow('SELECT * FROM users WHERE ID = ?', $keyData['userID'], Zend_Db::FETCH_ASSOC);
		$this -> db -> update('users', array('password' => $this -> makePasswordHash($newPassword, $userData['salt'])), 'ID = ' . $keyData['userID']);
	}

	public function requestLostPasswordKey($username) {
		if(!preg_match('/[a-zA-Z0-9_]+/', $username)) {
			throw new Exception('Username contains forbidden characters.');
		}
		$userData = $this -> db -> fetchRow('SELECT * FROM users WHERE username = ?', $username, Zend_Db::FETCH_ASSOC);
		if($userData === false) {
			throw new Exception('No such username in database.');
		}
		$key = $this -> generateSalt();
		$queryData = array($userData['ID'], $key, time() + (60 * 60 * 24));
		$this -> db -> query('INSERT INTO lost_password_keys VALUES(null, ?, ?, ?)', $queryData);
		return $key;
	}

	public function isLoggedIn() {
		return $this -> session -> auth;
	}

	public function getUserID() {
		return $this -> session -> auth ? $this -> session -> userID : false;
	}

	public function getUsername() {
		return $this -> session -> auth ? $this -> session -> username : false;
	}

}