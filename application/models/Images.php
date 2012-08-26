<?php

class Application_Model_Images
	{
		private $_db;
		
		public function __construct()
			{
				$this->_db = Zend_Registry::get('db');
			}
		
		public function deleteImages(array $ids, $dir)
			{
				$names = $this->_db->fetchCol('SELECT name FROM images WHERE ID IN(' . implode(', ', $ids) . ')');
				
				foreach($names as $name)
					unlink($dir . $name);
				
				$this->_db->delete('images', 'ID IN(' . implode(', ', $ids) . ')');
			}
		
		public function saveImages(array $names, array $sizes, $dir)
			{
				$uploaded = array();
				
				foreach($names as $key => $name)
				{
					if($name != '' && $sizes['$key'] <= 500000 && getimagesize($name) !== false)
					{
						$newName = time() . md5(basename($name)) . '.jpg';
						if(move_uploaded_file($name, $dir . $newName))
						{
							$this->_db->insert('images', array('name'=>$newName));
							$uploaded[$this->_db->lastInsertId('images', 'ID')] = $newName;
						}
					}
				}
				
				return $uploaded;
			}
	}
