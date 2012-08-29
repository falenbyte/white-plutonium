<?php
class Application_Model_Watchlist {

	private $_db;
	private $_userID;
	private $_userModel;
	private $_messages;

	public function __construct() {
		$this -> _userModel = Zend_Registry::get('userModel');
		$this -> _messages = Zend_Registry::get('messages') -> watchlist;
		if(!$this -> _userModel -> isLoggedIn()) {
			throw new Exception($this -> _messages -> notLoggedIn);
		}
		$this -> _userID = $this -> _userModel -> getUserID();
		$this -> _db = Zend_Registry::get('db');
	}

	public function fetch($offset, $limit) {
		if(!preg_match('/^[0-9]+$/', $offset) || !preg_match('/^[0-9]$/', $limit)) {
			throw new Exception($this -> _messages -> wrongParameters);
		}
		$queryData = array($this -> _userID, $offset, $limit);
		return $this -> _db -> fetchCol('SELECT annID FROM watched WHERE userID = ? LIMIT ?, ?', $queryData);
	}

	public function fetchAll() {
		return $this -> _db -> fetchCol('SELECT annID FROM watched WHERE userID = ?', $this -> _userID);
	}

	public function add($annID) {
		if(!preg_match('/^[0-9]+$/', $annID)) {
			throw new Exception($this -> _messages -> invalidAnnID);
		}
		$result = $this -> _db -> fetchRow('SELECT ID FROM announcements WHERE ID = ?', $annID);
		if($result === false) {
			throw new Exception($this -> _messages -> annDoesNotExists);
		}
		$result = $this -> _db -> fetchRow('SELECT userID FROM watched WHERE annID = ? AND userID = ?', array($annID, $this -> _userID));
		if($result !== false) {
			throw new Exception($this -> _messages -> alreadyAdded);
		}
		$this -> _db -> insert('watched', array('annID' => $annID, 'userID' => $this -> _userID));
	}

	public function remove($annID) {
		if(!preg_match('/^[0-9]+$/', $annID)) {
			throw new Exception($this -> _messages -> invalidAnnID);
		}
		$this -> _db -> delete('watched', 'annID = ' . $annID . ' AND userID = ' . $this -> _userID);
	}

}