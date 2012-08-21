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
		if(isset($_POST['username']) && isset($_POST['password'])) {
			try {
				$this -> user -> login($_POST['username'], $_POST['password']);
				$this -> _redirect('index');
			} catch(Exception $e) {
				$this -> view -> authMessage = $e -> getMessage();
			}
		}
	}

	public function logoutAction() {
		Zend_Registry::get('userModel') -> logout();
		$this -> _redirect('index');
	}

	public function registerAction() {
		
	}

	public function change_passwordAction() {
		
	}

	public function change_lost_passwordAction() {
		
	}

	public function recover_passwordAction() {
		
	}

}
