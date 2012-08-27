<?php

class WatchlistController extends Zend_Controller_Action {

	private $_watchlistModel;

	public function init() {
		try {
			$this -> _watchlistModel = new Application_Model_Watchlist();
		} catch(Exception $e) {
			$this -> _redirect('index');
		}
	}

	public function indexAction() {
		$watchedList = $this -> _watchlistModel -> fetchAll();
		$annMapper = new Application_Model_AnnouncementsMapper();
		$ann = $annMapper -> getListByIDs($watchedList);
		if($ann === null) {
			$this -> view -> message = 'Brak obserwowanych ogłoszeń.';
		} else {
			$this -> view -> announcements = $ann;
		}
	}

	public function addAction() {
		try {
			$this -> _watchlistModel -> add($_GET['id']);
			$this -> _redirect('watchlist');
		} catch(Exception $e) {
			$this -> view -> message = $e -> getMessage();
		}
	}

	public function removeAction() {
		$this -> _watchlistModel -> remove($_GET['id']);
		$this -> _redirect('watchlist');
	}

}