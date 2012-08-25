<?php

class Zend_View_Helper_PageSwitcher extends Zend_View_Helper_Abstract
	{
		public function pageSwitcher()
			{	
				$page = isset($_GET['page']) && intval($_GET['page']) > 0 ? intval($_GET['page']) : 1;
				$prevParams[] = 'page=' . strval($page - 1);
				$nextParams[] = 'page=' . strval($page + 1);
				
				foreach($_GET as $name => $p)
				{
					if($name == 'page')
						continue;
					else
					{
						$prevParams[] = $name . '=' . $p;
						$nextParams[] = $name . '=' . $p;
					}
						
				}
				
				
				$result = sprintf('<div class="page_switcher"><a href="%s">&lt;</a><a href="%s">&gt;</a></div>',
					$this->view->url(array('controller'=>'search', 'action'=>'index')) . '?' . implode('&', $prevParams),
					$this->view->url(array('controller'=>'search', 'action'=>'index')) . '?' . implode('&', $nextParams));
				
				return $result;
			}
	}
