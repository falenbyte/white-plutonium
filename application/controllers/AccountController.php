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
	}

	public function loginAction() {
		if($this -> user -> isLoggedIn()) {
			$this -> _redirect('index');
		}
		if(isset($_POST['username']) && isset($_POST['password'])) {
			try {
				$this -> user -> login($_POST['username'], $_POST['password'], $_POST['keepMeLoggedIn']);
				$this -> _redirect('index');
			} catch(Exception $e) {
				$this -> view -> message = $e -> getMessage();
			}
		}
	}

	public function logoutAction() {
		Zend_Registry::get('userModel') -> logout();
		$this -> _redirect('index');
	}

	public function registerAction() {
		if($this -> user -> isLoggedIn()) {
			$this -> _redirect('index');
		}
		if(isset($_POST['username']) && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['password_confirm'])) {
			if($_POST['password'] != $_POST['password_confirm']) {
				$this -> view -> message = 'Passwords do not match';
				return;
			}
			try {
				$this -> user -> register($_POST['username'], $_POST['password'], $_POST['email']);
				$this -> view -> message = 'Account created';
				$this -> view -> showForm = false;
			} catch(Exception $e) {
				$this -> view -> message = $e -> getMessage();
			}
		}
		$this -> view -> showForm = true;
	}

	public function changepasswordAction() { //change_password
		if(!$this -> user -> isLoggedIn()) {
			$this -> _redirect('account/login');
		}
		if(isset($_POST['oldPassword']) && isset($_POST['newPassword'])) {
			try {
				$this -> user -> changePassword($_POST['oldPassword'], $_POST['newPassword']);
			} catch(Exception $e) {
				$this -> view -> message = $e -> getMessage();
			}
		}
	}

	public function changelostpasswordAction() { //change_lost_password
		if($this -> user -> isLoggedIn()) {
			$this -> _redirect('index');
		}
		$this -> view -> displayForm = false;
		if(!preg_match('/^[a-zA-Z0-9]{32}$/', $_GET['key'])) {
			$this -> view -> message = 'Invalid key string';
			return;
		}
		if(isset($_POST['newPassword']) && $_POST['newPasswordConfirm']) {
			if($_POST['newPassword'] != $_POST['newPasswordConfirm']) {
				$this -> view -> displayForm = true;
				$this -> view -> message = 'Passwords do not match';
				return;
			}
			try {
				$this -> user -> changeLostPassword($_GET['key'], $_POST['newPassword']);
				$this -> view -> message = "Password has been changed";
			} catch(Exception $e) {
				$this -> view -> message = $e -> getMessage();
			}
			return;
		}
		$this -> view -> displayForm = true;
	}

	public function lostpasswordAction() { //lost_password
		if($this -> user -> isLoggedIn()) {
			$this -> _redirect('index');
		}
		if(isset($_POST['username'])) {
			try {
				echo $this -> user -> requestLostPasswordKey($_POST['username']);
				//TODO: send it via email
			} catch(Exception $e) {
				$this -> view -> message = $e -> getMessage();
			}
		}
	}

}
