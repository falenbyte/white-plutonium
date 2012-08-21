<?php

class AccountController {

	public function init()
    {
        /* Initialize action controller here */
    }
	
	public function indexAction() {
		
	}

	public function loginAction() {
		Zend_Registry::get('userModel') -> login('user1', 'password1');
		$this -> _redirect('index');
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
