<?php

class AccountController  extends Zend_Controller_Action{

	private $_user;
	private $_messages;

	public function init() {
		$this -> _user = Zend_Registry::get('userModel');
		$this -> _messages = Zend_Registry::get('messages') -> account;
	}

	public function indexAction() {
		if(!$this -> _user -> isLoggedIn()) {
			$this -> _redirect('account/login');
		}
		$this -> view -> userID = $this -> _user -> getUserID();
	}

	public function loginAction() {
		if($this -> _user -> isLoggedIn()) {
			$this -> _redirect('index');
		}
		if(isset($_POST['username'], $_POST['password'], $_POST['keepMeLoggedIn'])) {
			try {
				$this -> _user -> login($_POST['username'], $_POST['password'], $_POST['keepMeLoggedIn']);
				$this -> _redirect('index');
			} catch(Exception $e) {
				$this -> view -> message = $e -> getMessage();
			}
		}
	}

	public function logoutAction() {
		$this -> _user -> logout();
		$this -> _redirect('index');
	}

	public function registerAction() {
		if($this -> _user -> isLoggedIn()) {
			$this -> _redirect('index');
		}
		$this -> view -> onlyMessage = false;
		if(isset($_POST['username'], $_POST['email'], $_POST['password'], $_POST['password_confirm'])) {
			try {
				$this -> _user -> register($_POST['username'], $_POST['password'], $_POST['password_confirm'], $_POST['email'],
						$this -> view -> serverUrl() . $this -> view -> url(array('controller' => 'account', 'action' => 'activate')));
				$this -> view -> onlyMessage = true;
				throw new Exception($this -> _messages -> created);
			} catch(Exception $e) {
				$this -> view -> message = $e -> getMessage();
			}
		}
	}

	public function activateAction() {
		try {
			if(!isset($_GET['key'])) {
				throw new Exception($this -> _messages -> activationKeyMissing);
			}
			$this -> _user -> activateAccount($_GET['key']);
			throw new Exception($this -> _messages -> activated);
		} catch(Exception $e) {
			$this -> view -> message = $e -> getMessage();
		}
	}

	public function changepasswordAction() { //change_password
		if(!$this -> _user -> isLoggedIn()) {
			$this -> _redirect('account/login');
		}
		if(isset($_POST['old_password'], $_POST['new_password'], $_POST['new_password_confirm'])) {
			try {
				if($_POST['new_password'] != $_POST['new_password_confirm']) {
					throw new Exception($this -> _messages -> passwordsDoNotMatch);
				}
				$this -> _user -> changePassword($_POST['old_password'], $_POST['new_password']);
				$this -> _user -> logout();
				$this -> _redirect('account/login');
			} catch(Exception $e) {
				$this -> view -> message = $e -> getMessage();
			}
		}
	}

	public function changelostpasswordAction() { //change_lost_password
		if($this -> _user -> isLoggedIn()) {
			$this -> _redirect('index');
		}
		$this -> view -> onlyMessage = false;
		if(isset($_POST['new_password'], $_POST['new_password_confirm'])) {
			try {
				if($_POST['new_password'] != $_POST['new_password_confirm']) {
					throw new Exception($this -> _messages -> passwordsDoNotMatch);
				}
				$this -> view -> onlyMessage = true;
				$this -> _user -> changeLostPassword($_GET['key'], $_POST['new_password']);
				throw new Exception($this -> _messages -> passwordChanged);
			} catch(Exception $e) {
				$this -> view -> message = $e -> getMessage();
			}
		}
	}

	public function lostpasswordAction() { //lost_password
		if($this -> _user -> isLoggedIn()) {
			$this -> _redirect('index');
		}
		if(isset($_POST['username'])) {
			try {
				$this -> _user -> requestLostPasswordKey($_POST['username']);
				throw new Exception($this -> _messages -> sentKey);
			} catch(Exception $e) {
				$this -> view -> message = $e -> getMessage();
			}
		}
	}

}
