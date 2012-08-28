<?php

class Application_Model_User {

	private $_session;
	private $_messages;
	private $_db;

	public function __construct() {
		$this -> _db = Zend_Registry::get('db');
		$this -> _messages = Zend_Registry::get('messages') -> user;
		//Sesje!!
		$this -> _session = new Zend_Session_Namespace('UserData');
		if(!isset($this -> _session -> auth)) {
			$this -> _session -> auth = false;
		}
		$this -> updateLastSeen();
	}

	public function login($username, $password, $persistent = false) {
		if(!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
			throw new Exception($this -> _messages -> invalidUsername);
		}
		$userData = $this -> _db -> fetchRow('SELECT * FROM users WHERE username = ?', $username, Zend_Db::FETCH_ASSOC);
		//Czy taki użytkownik istnieje?
		if($userData === false) {
			throw new Exception($this -> _messages -> userDoesNotExists);
		}
		if($userData['activatedFlag'] === '0') {
			throw new Exception($this -> _messages -> notActivatedYet);
		}
		if($userData['password'] === $this -> makePasswordHash($password, $userData['salt'])) {
			//Czas sesji: 7 dni (zapamiętany) / 2 godziny (nie zapamiętany)
			Zend_Session::rememberMe(($persistent === '1') ? 604800 : 7200);
			$this -> _session -> auth = true;
			$this -> _session -> userID = $userData['ID'];
			$this -> _session -> username = $userData['username'];
			$this -> _session -> keepMeLoggedIn = ($persistent === '1');
			$this -> _session -> admin = (strtolower($username) === 'admin');
			$this -> updateLastSeen();
		} else {
			throw new Exception($this -> _messages -> invalidPassword);
		}
	}

	public function logout() {
		$this -> _session -> unsetAll();
	}

	public function updateLastSeen() {
		if($this -> _session -> auth) {
			//Czy sesja wygaśnie za mniej niz 5 dni (zapamiętany) / 1 godzinę (nie zapamiętany)
			if($_SESSION['__ZF']['UserData']['ENT'] - time() < ($this -> _session -> keepMeLoggedIn ? 450000 : 3600)) {
				Zend_Session::rememberMe($this -> _session -> keepMeLoggedIn ? 604800 : 7200);
			}
			$this -> _db -> update('users', array('last_seen' => time()), 'ID = ' . $this -> _session -> userID);
		}
	}

	private function makePasswordHash($password, $salt) {
		return md5($password . $salt);
	}

	private function generateSalt() {
		mt_srand(microtime(true) * 10000);
		return md5(mt_rand(0, mt_getrandmax()) * 51539607551); //Bardzo losowo wybrana przezemnie liczba pierwsza
	}

	public function register($username, $password, $passwordConfirm, $email, $url) {
		if(!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
			unset($_POST['username']);
			throw new Exception($this -> _messages -> invalidUsername);
		}
		if(!preg_match('/^[a-zA-Z0-9_\-\.\+]+@[a-zA-Z0-9\.]+\.[a-zA-Z]{2,}$/', $email)) {
			unset($_POST['email']);
			throw new Exception($this -> _messages -> invalidEmail);
		}
		//Sprawdz czy juz takie username jest zajete
		$result = $this -> _db -> fetchRow('SELECT ID FROM users WHERE username = ?', $username, Zend_Db::FETCH_ASSOC);
		if($result !== false) {
			unset($_POST['username']);
			throw new Exception($this -> _messages -> userAlreadyExists);
		}
		//Sprawdz czy takie email jest juz uzywany
		$result = $this -> _db -> fetchRow('SELECT ID FROM users WHERE email = ?', $email, Zend_Db::FETCH_ASSOC);
		if($result !== false) {
			unset($_POST['email']);
			throw new Exception($this -> _messages -> emailAlreadyInUse);
		}
		if($password != $passwordConfirm) { //Czy poprawne hasła?
			throw new Exception('Passwords do not match');
		}
		//Wszystko ok? to rejestrujemy
		$salt = $this -> generateSalt();
		$queryData = array($username, md5($password.$salt), $salt, $email, time(), time(), 0);
		$this -> _db -> query('INSERT INTO users VALUES(null, ?, ?, ?, ?, ?, ?, ?)', $queryData);
		$userID = $this -> _db -> lastInsertId();
		$activationKey = $this -> generateSalt();
		$queryData = array($userID, $activationKey, time()+(60*60*24));
		//send email
		try {
			$mail = new Zend_Mail();
			$mail->setBodyText('Aby aktywować konto przejdź na stronę:
					<a href="' . $url . '?key=' . $activationKey . '">' . $url . '?key=' . $activationKey . '</a>');
			$mail->addTo($email, 'Recipient');
			$mail->setSubject('Activation key');
			$mail->send();
		} catch(Exception $e) {
			throw new Exception($this -> _messages -> emailSendError);
		}
		$this -> _db -> query('INSERT INTO user_activation_keys VALUES(null, ?, ?, ?)', $queryData);

	}

	public function activateAccount($key) {
		if(!preg_match('/^[a-zA-Z0-9]{32}$/', $key)) {
			throw new Exception($this -> _messages -> invalidKey);
		}
		$keyData = $this -> _db -> fetchRow('SELECT * FROM user_activation_keys WHERE `key` = ?', $key, Zend_Db::FETCH_ASSOC);
		if($keyData === false) {
			throw new Exception($this -> _messages -> keyNotInDatabase);
		}
		if($keyData['expires'] < time()) {
			throw new Exception($this -> _messages -> keyExpired);
		}
		$this -> _db -> update('users', array('activatedFlag' => 1), 'ID = ' . $keyData['userID']);
		$this -> _db -> delete('user_activation_keys', 'ID = ' . $keyData['userID']);
	}

	public function changePassword($oldPassword, $newPassword) {
		if(!$this -> _session -> auth) {
			throw new Exception($this -> _messages -> notLoggedIn);
		}
		$userData = $this -> _db -> fetchRow('SELECT * FROM users WHERE ID = ?', $this -> _session -> userID, Zend_Db::FETCH_ASSOC);
		if($this -> makePasswordHash($oldPassword, $userData['salt']) == $userData['password']) {
			$this -> _db -> update('users', array('password' => $this -> makePasswordHash($newPassword, $userData['salt'])), 'ID = ' . $this -> _session -> userID);
		} else {
			throw new Exception($this -> _messages -> invalidPassword);
		}
	}

	public function changeLostPassword($key, $newPassword) {
		if(!preg_match('/^[a-zA-Z0-9]{32}$/', $key)) {
			throw new Exception($this -> _messages -> invalidKey);
		}
		$keyData = $this -> _db -> fetchRow('SELECT * FROM lost_password_keys WHERE `key` = ?', $key, Zend_Db::FETCH_ASSOC);
		if($keyData === false) {
			throw new Exception($this -> _messages -> keyNotInDatabase);
		}
		if($keyData['expires'] < time()) {
			throw new Exception($this -> _messages -> keyExpired);
		}
		$userData = $this -> _db -> fetchRow('SELECT * FROM users WHERE ID = ?', $keyData['userID'], Zend_Db::FETCH_ASSOC);
		$this -> _db -> update('users', array('password' => $this -> makePasswordHash($newPassword, $userData['salt'])), 'ID = ' . $keyData['userID']);
		$this -> _db -> delete('lost_password_keys', '`key` = "' . $key .'"');
	}

	public function requestLostPasswordKey($username) {
		if(!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
			throw new Exception($this -> _messages -> invalidUsername);
		}
		$userData = $this -> _db -> fetchRow('SELECT * FROM users WHERE username = ?', $username, Zend_Db::FETCH_ASSOC);
		if($userData === false) {
			throw new Exception($this -> _messages -> userDoesNotExists);
		}
		if($userData['activatedFlag'] === '0') {
			throw new Exception($this -> _messages -> notActivatedYet);
		}
		$key = $this -> generateSalt();
		$queryData = array($userData['ID'], $key, time() + (60 * 60 * 24));
		try {
			$mail = new Zend_Mail();
			$mail->setBodyText('To change password go to /account/change_lost_password?key=' . $key);
			$mail->addTo($userData['email'], 'Recipient');
			$mail->setSubject('Password reset key');
			$mail->send();
		} catch(Exception $e) {
			throw new Exception($this -> _messages -> emailSendError);
		}
		$this -> _db -> query('INSERT INTO lost_password_keys VALUES(null, ?, ?, ?)', $queryData);
	}

	public function isAdmin() {
		return $this -> _session -> admin;
	}

	public function isLoggedIn() {
		return $this -> _session -> auth;
	}

	public function getUserID() {
		return $this -> _session -> auth ? $this -> _session -> userID : false;
	}

	public function getUsername() {
		return $this -> _session -> auth ? $this -> _session -> username : false;
	}

}