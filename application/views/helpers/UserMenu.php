<?php

class Zend_View_Helper_UserMenu extends Zend_View_Helper_Abstract
	{
		public function userMenu()
			{
				if(Zend_Registry::get('userModel')->isLoggedIn())
					return sprintf('<li class="f_right"> | [<a href="%s">Wyloguj</a>]</li><li class="f_right"><a href="%s"> | Panel użytkownika</a></li><li class="f_right">Zalogowany jako: %s</li>',
						$this->view->url(array('controller'=>'account', 'action'=>'logout')),
						$this->view->url(array('controller'=>'account', 'action'=>'index')),
						Zend_Registry::get('userModel')->getUsername());
				else
					return sprintf('<li class="f_right"> | <a href="%s">Logowanie</a></li> | <li class="f_right"><a href="%s">Rejestracja</a></li>',
						$this->view->url(array('controller'=>'account', 'action'=>'login')),
						$this->view->url(array('controller'=>'account', 'action'=>'register')));
			}
	}
