<?php

class AccountController  extends Zend_Controller_Action{

	private $user;
	
	public function init() {
		$this -> user = Zend_Registry::get('userModel');
    }
	
	public function indexAction() {
		if(!$this -> user -> isLoggedIn()) {
			$this -> _redirect('account/login');
		}
		$this -> view -> userID = $this -> user -> getUserID();
	}

	public function loginAction() {
		if($this -> user -> isLoggedIn()) {
			$this -> _redirect('index');
		}
		if(isset($_POST['username'], $_POST['password'], $_POST['keepMeLoggedIn'])) {
			try {
				$this -> user -> login($_POST['username'], $_POST['password'], $_POST['keepMeLoggedIn']);
				$this -> _redirect('index');
			} catch(Exception $e) {
				$this -> view -> message = $e -> getMessage();
			}
		}
	}

	public function logoutAction() {
		$this -> user -> logout();
		$this -> _redirect('index');
	}

	public function registerAction() {
		if($this -> user -> isLoggedIn()) {
			$this -> _redirect('index');
		}
		$this -> view -> onlyMessage = false;
		if(isset($_POST['username'], $_POST['email'], $_POST['password'], $_POST['password_confirm'])) {
			try {
				if($_POST['password'] != $_POST['password_confirm']) {
					throw new Exception('Passwords do not match');;
				}
				$this -> view -> onlyMessage = true;
				$this -> user -> register($_POST['username'], $_POST['password'], $_POST['email']);
				throw new Exception('Account created');
			} catch(Exception $e) {
				$this -> view -> message = $e -> getMessage();
			}
		}
	}
	
	public function activateAction() {
		try {
			if(!isset($_GET['key'])) {
				throw new Exception('Missing activation key!');
			}
			$this -> user -> activateAccount($_GET['key']);
			throw new Exception('Account activated');
		} catch(Exception $e) {
			$this -> view -> message = $e -> getMessage();
		}
	}

	public function changepasswordAction() { //change_password
		if(!$this -> user -> isLoggedIn()) {
			$this -> _redirect('account/login');
		}
		if(isset($_POST['old_password'], $_POST['new_password'], $_POST['new_password_confirm'])) {
			try {
				if($_POST['new_password'] != $_POST['new_password_confirm']) {
					throw new Exception('Passwords do not match.');
				}
				$this -> user -> changePassword($_POST['old_password'], $_POST['new_password']);
			} catch(Exception $e) {
				$this -> view -> message = $e -> getMessage();
			}
		}
	}

	public function changelostpasswordAction() { //change_lost_password
		if($this -> user -> isLoggedIn()) {
			$this -> _redirect('index');
		}
		$this -> view -> onlyMessage = false;
		if(isset($_POST['newPassword'], $_POST['newPasswordConfirm'])) {
			try {
				if($_POST['newPassword'] != $_POST['newPasswordConfirm']) {
					throw new Exception('Passwords do not match.');
				}
				$this -> view -> onlyMessage = true;
				$this -> user -> changeLostPassword($_GET['key'], $_POST['newPassword']);
				throw new Exception('Password has been changed.');
			} catch(Exception $e) {
				$this -> view -> message = $e -> getMessage();
			}
		}
	}

	public function lostpasswordAction() { //lost_password
		if($this -> user -> isLoggedIn()) {
			$this -> _redirect('index');
		}
		if(isset($_POST['username'])) {
			try {
				$this -> user -> requestLostPasswordKey($_POST['username']);
				throw new Exception('Klucz został wysłany.');
			} catch(Exception $e) {
				$this -> view -> message = $e -> getMessage();
			}
		}
	}

}
