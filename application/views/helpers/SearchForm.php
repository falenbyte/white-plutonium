<?php

class Zend_View_Helper_SearchForm extends Zend_View_Helper_Abstract
{
	public function searchForm()
	{
		$params = $_GET;

		foreach($params as $key => &$value)
		{
			if(preg_match('/^[0-9]+$/', $key))
			{
				foreach($value as $akey => $aval)
				{
					if($aval == '')
						unset($value[$akey]);
				}
			}
			else if($value == '')
				unset($params[$key]);
		}

		$catMapper = new Application_Model_CategoriesMapper();
		$categories = $catMapper -> getAll();
		$special = false;
		if(isset($params['cat']) && preg_match('/^[0-9]+$/', $params['cat'])) {
			if(is_null($catMapper->getByID($params['cat'])->parentID)) {
				$children = $catMapper -> getChildren($params['cat']);
				$params['cat'] = reset($children) -> ID;
			}
			$special = true;
		}
		$result = '<div id="search_form"><form action="' . $this -> view -> url(array('controller' => 'search', 'action' => 'index')) . '" method="GET">
		<div class="search"><div class="search_label">Kategoria:</div><div class="search_input"><select name="cat"><option value="all"' . ($params['cat'] == 'all' ? ' selected' : '') . '>Wszystkie</option>';
		foreach($categories as $ckey => $cat) {
			if(is_null($cat->parentID)) {
				$result .= sprintf('<optgroup label="%s">', $cat -> name);
				foreach($categories as $chckey => $chcat) {
					if($chcat->parentID == $ckey) {
						$result .= sprintf('<option value="%s"%s>%s</option>',
								$chckey,
								($chckey == $params['cat'] ? ' selected' : ''),
								$chcat->name);
					}
				}
				$result .= '</optgroup>';
			}
		}
		$result .= '</select></div></div><div class="search"><input name="send" type="submit" value="Zmień"></div></form>';

		$form = new Application_Form_Search($params, (!$special));
		$result .= $form->render($this->view->url(array('controller'=>'search', 'action'=>'index')));
		$result .= '</div>';
		return $result;
	}
}
