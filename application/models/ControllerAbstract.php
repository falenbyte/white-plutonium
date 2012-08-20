<?php
abstract class Application_Model_ControllerAbstract extends Zend_Controller_Action {
	
	public function init() {
		$this -> view -> userModel = Zend_Registry::get('userModel');
	}
	
}