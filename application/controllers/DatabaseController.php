<?php

class DatabaseController extends Zend_Controller_Action
{

    public function init()
    {
    	$time = time();
    	
    	//users
    	$user = new Application_Model_User();
    	$user -> register('user1', 'password1', 'email1@gmail.com');
    	$user -> register('user2', 'password2', 'email2@gmail.com');
    	$user -> register('user3', 'password3', 'email3@gmail.com');
    	
    	//lost_keys
    	$user -> requestLostPasswordKey('user1');
    	$user -> requestLostPasswordKey('user2');
    	$user -> requestLostPasswordKey('user3');
    	
    	//categories
    	$list[] = array('categories', array('null', 'null', '"testCategory1"'));
    	$list[] = array('categories', array('null', 'null', '"testCategory2"'));
    	$list[] = array('categories', array('null', '1', '"testCategory3"'));
    	$list[] = array('categories', array('null', '1', '"testCategory4"'));
    	
    	//attributes
    	$list[] = array('attributes', array('null', '"Sztuk"', '0', '"szt."', '0', '100')); //1
    	$list[] = array('attributes', array('null', '"Kolor"', '1', '""', 'null', 'null')); //2
    	$list[] = array('attributes', array('null', '"Model"', '2', '""', 'null', 'null')); //3
    	$list[] = array('attributes', array('null', '"MaSuperHiperFunkcje?"', '3', '""', 'null', 'null')); //4
    	$list[] = array('attributes', array('null', '"Cena"', '4', '"z³"', '0', '100')); //5
    	
    	//categories_attributes
    	$list[] = array('categories_attributes', array('1', '1'));
    	$list[] = array('categories_attributes', array('2', '2'));
    	$list[] = array('categories_attributes', array('3', '3'));
    	$list[] = array('categories_attributes', array('4', '4'));
    	$list[] = array('categories_attributes', array('1', '5'));
    	$list[] = array('categories_attributes', array('2', '5'));
    	$list[] = array('categories_attributes', array('3', '5'));
    	$list[] = array('categories_attributes', array('4', '5'));
    	
    	//attributes_options
    	$list[] = array('attributes_options', array('null', '3', '"Model1"')); //1
    	$list[] = array('attributes_options', array('null', '3', '"Model2"')); //2
    	$list[] = array('attributes_options', array('null', '3', '"Model3"')); //3
    	
    	//images
    	$list[] = array('images', array('null', '"someImage1.jpg"')); //1
    	$list[] = array('images', array('null', '"someImage2.jpg"')); //2
    	$list[] = array('images', array('null', '"someImage3.jpg"')); //3
    	
    	//announcements
    	$list[] = array('announcements', array('null', '1', '1', '"AnnTitle1"', '"Content1"', $time, $time + (60 * 60 * 24 *7)));
    	$list[] = array('announcements', array('null', '2', '2', '"AnnTitle2"', '"Content2"', $time, $time + (60 * 60 * 24 *7)));
    	$list[] = array('announcements', array('null', '3', '3', '"AnnTitle3"', '"Content3"', $time, $time + (60 * 60 * 24 *7)));
    	$list[] = array('announcements', array('null', '4', '1', '"AnnTitle4"', '"Content4"', $time, $time + (60 * 60 * 24 *7)));
    	
    	//announcement_images
    	$list[] = array('announcement_images', array('1', '1'));
    	$list[] = array('announcement_images', array('1', '2'));
    	$list[] = array('announcement_images', array('2', '3'));
    	
    	//attributes_values
    	$list[] = array('attributes_values', array('1', '5', 'null', 'null', '10.0')); //float
    	$list[] = array('attributes_values', array('2', '5', 'null', 'null', '25.5'));
    	$list[] = array('attributes_values', array('3', '5', 'null', 'null', '99.99'));
    	$list[] = array('attributes_values', array('4', '5', 'null', 'null', '3.98'));
    	$list[] = array('attributes_values', array('1', '1', '5', 'null', 'null')); //int
    	$list[] = array('attributes_values', array('2', '2', 'null', '"Czarny"', 'null')); //text
    	$list[] = array('attributes_values', array('3', '3', '2', 'null', 'null')); //options
    	$list[] = array('attributes_values', array('4', '4', '1', 'null', 'null')); //bool
    	
    	$db = Zend_Registry::get('db');
    	
    	foreach($list as $item) {
    		$db -> query('INSERT INTO '.$item[0].' VALUES('.implode(',', $item[1]).')');
    	}
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    }


}

