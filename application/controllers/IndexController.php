<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $categoriesMapper = new Application_Model_CategoriesMapper();
        $mainCategories = $categoriesMapper -> getMain();
        foreach($mainCategories as $category) {
        	$subCategories[$category -> ID] = $categoriesMapper -> getChildren($category -> ID);
        }
        $this -> view -> mainCategories = $mainCategories;
        $this -> view -> subCategories = $subCategories;
    }


}

