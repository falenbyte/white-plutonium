<?php
class Application_Model_Watchlist {
	
	private $_db;
	private $_userID;
	private $_userModel;
	
	public function __construct() {
		$this -> _userModel = Zend_Registry::get('userModel');
		if(!$this -> _userModel -> isLoggedIn()) {
			throw new Exception('User is not logged in.');
		}
		$this -> _userID = $this -> _userModel -> getUserID();
		$this -> _db = Zend_Registry::get('db');
	}
	
	public function fetch($offset, $limit) {
		if(!preg_match('/^[0-9]+$/', $offset) || !preg_match('/^[0-9]$/', $limit)) {
			throw new Exception('Wrong parameters.');
		}
		$queryData = array($this -> _userID, $offset, $limit);
		return $this -> _db() -> fetchCol('SELECT annID FROM watchlist WHERE userID = ? LIMIT ?, ?', $queryData);
	}
	
	public function fetchAll() {
		return $this -> _db() -> fetchCol('SELECT annID FROM watchlist WHERE userID = ?', $this -> _userID);
	}
	
	public function add($annID) {
		if(!preg_match('/^[0-9]+$/', $annID)) {
			throw new Exception('Wrong announcement ID.');
		}
		$result = $this -> _db -> fetchRow('SELECT annID FROM announcements WHERE ID = ?', $annID);
		if($result === false) {
			throw new Exception('Announcement with this ID does not exists.');
		}
		$this -> _db -> insert('watchlist', array('annID' => $annID, 'userID' => $this -> _userID));
	}
	
	public function remove($annID) {
		if(!preg_match('/^[0-9]+$/', $annID)) {
			throw new Exception('Wrong announcement ID.');
		}
		$this -> _db -> delete('watchlist', 'annID = ' . $annID . ' AND userID = ' . $this -> _userID);
	}
	
}