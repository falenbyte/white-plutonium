<?php

class DatabaseController extends Zend_Controller_Action
{

	public function init()
	{
		$time = time();

		//users
		/*$user = new Application_Model_User();
		 $user -> register('user1', 'password1', 'email1@someSrv.com');
		$user -> register('user2', 'password2', 'email2@someSrv.com');
		$user -> register('user3', 'password3', 'email3@someSrv.com');

		//lost_keys
		/*$user -> requestLostPasswordKey('user1');
		$user -> requestLostPasswordKey('user2');
		$user -> requestLostPasswordKey('user3');*/

		//categories
		$list[] = array('categories', array('null', 'null', '"Nieruchomości"'));
		$list[] = array('categories', array('null', 'null', '"Motoryzacja"'));
		$list[] = array('categories', array('null', 'null', '"Praca"'));

		//subcategories 1 - nieruchomości
		$list[] = array('categories', array('null', '1', '"Kupię"')); //4
		$list[] = array('categories', array('null', '1', '"Sprzedam"')); //5
		$list[] = array('categories', array('null', '1', '"Zamienię"')); //6

		//subcategories 2 - motoryzacja
		$list[] = array('categories', array('null', '2', '"Kupię"')); //7
		$list[] = array('categories', array('null', '2', '"Sprzedam"')); //8
		$list[] = array('categories', array('null', '2', '"Zamienię"')); //9

		//subcategories 3 - praca
		$list[] = array('categories', array('null', '3', '"Zatrudnię"')); //10
		$list[] = array('categories', array('null', '3', '"Szukam pracy"')); //11

		//attributes
		$list[] = array('attributes', array('null', '"Cena"', '4', '"zł"', 'null', 'null')); //1

		//attributes - nieruchomości
		$list[] = array('attributes', array('null', '"Rodzaj"', '2', '""', 'null', 'null')); //2 <-
		$list[] = array('attributes', array('null', '"Powierzchnia"', '4', '"m<sup>2</sup>"', 'null', 'null')); //3
		$list[] = array('attributes', array('null', '"Liczba pokoi"', '0', '""', 'null', 'null')); //4
		$list[] = array('attributes', array('null', '"Rok budowy"', '0', '"r."', 'null', 'null')); //5
		$list[] = array('attributes', array('null', '"Piętro"', '0', '""', 'null', 'null')); //6

		$list[] = array('attributes', array('null', '"Kuchnia"', '3', '""', 'null', 'null')); //7
		$list[] = array('attributes', array('null', '"Balkon"', '3', '""', 'null', 'null')); //8
		$list[] = array('attributes', array('null', '"Taras"', '3', '""', 'null', 'null')); //9
		$list[] = array('attributes', array('null', '"Dwu poziomowe"', '3', '""', 'null', 'null')); //10
		$list[] = array('attributes', array('null', '"Miejsce parkingowe"', '3', '""', 'null', 'null')); //11
		$list[] = array('attributes', array('null', '"Winda"', '3', '""', 'null', 'null')); //12

		//attributes - motoryzacja
		$list[] = array('attributes', array('null', '"Rok produkcji"', '0', '"r."', 'null', 'null')); //13
		$list[] = array('attributes', array('null', '"Pojemność"', '4', '"cm<sup>3</sup>"', 'null', 'null')); //14
		$list[] = array('attributes', array('null', '"Moc silnika"', '4', '"KM"', 'null', 'null')); //15
		$list[] = array('attributes', array('null', '"Przebieg"', '4', '"km"', 'null', 'null')); //16
		$list[] = array('attributes', array('null', '"Marka"', '2', '""', 'null', 'null')); //17 <-

		$list[] = array('attributes', array('null', '"ABS"', '3', '""', 'null', 'null')); //18
		$list[] = array('attributes', array('null', '"Wspomaganie kierownicy"', '3', '""', 'null', 'null')); //19
		$list[] = array('attributes', array('null', '"Komputer"', '3', '""', 'null', 'null')); //20
		$list[] = array('attributes', array('null', '"El. szyby"', '3', '""', 'null', 'null')); //21
		$list[] = array('attributes', array('null', '"Immobilizer"', '3', '""', 'null', 'null')); //22
		$list[] = array('attributes', array('null', '"Centralny zamek"', '3', '""', 'null', 'null')); //23
		$list[] = array('attributes', array('null', '"Radio"', '3', '""', 'null', 'null')); //24

		//attributes - praca
		$list[] = array('attributes', array('null', '"Branża"', '2', '""', 'null', 'null')); //25 <-
		$list[] = array('attributes', array('null', '"Stanowisko"', '2', '""', 'null', 'null')); //26 <-
		$list[] = array('attributes', array('null', '"Rodzaj pracy"', '2', '""', 'null', 'null')); //27 <-
		$list[] = array('attributes', array('null', '"Forma zatrudnienia"', '2', '""', 'null', 'null')); //28 <-

		//attributes_options - nieruchomosci - rodzaj
		$list[] = array('attributes_options', array('null', '2', '"Biuro"'));
		$list[] = array('attributes_options', array('null', '2', '"Dom"'));
		$list[] = array('attributes_options', array('null', '2', '"Działka"'));
		$list[] = array('attributes_options', array('null', '2', '"Lokal"'));
		$list[] = array('attributes_options', array('null', '2', '"Magazyn"'));
		$list[] = array('attributes_options', array('null', '2', '"Mieszkanie"'));
		$list[] = array('attributes_options', array('null', '2', '"Pokój"'));
		$list[] = array('attributes_options', array('null', '2', '"Warsztat"'));

		//options - motoryzacja - marka
		$list[] = array('attributes_options', array('null', '17', '"Audi"'));
		$list[] = array('attributes_options', array('null', '17', '"BMW"'));
		$list[] = array('attributes_options', array('null', '17', '"Chevrolet"'));
		$list[] = array('attributes_options', array('null', '17', '"Fiat"'));
		$list[] = array('attributes_options', array('null', '17', '"Ford"'));
		$list[] = array('attributes_options', array('null', '17', '"Hummer"'));
		$list[] = array('attributes_options', array('null', '17', '"Jeep"'));
		$list[] = array('attributes_options', array('null', '17', '"Kia"'));
		$list[] = array('attributes_options', array('null', '17', '"Mazda"'));
		$list[] = array('attributes_options', array('null', '17', '"Peugeot"'));
		$list[] = array('attributes_options', array('null', '17', '"Suzuki"'));
		$list[] = array('attributes_options', array('null', '17', '"Toyota"'));

		//options - praca - branża
		$list[] = array('attributes_options', array('null', '25', '"Gastronomia"'));
		$list[] = array('attributes_options', array('null', '25', '"Księgowość"'));
		$list[] = array('attributes_options', array('null', '25', '"Marketing"'));
		$list[] = array('attributes_options', array('null', '25', '"Architekci"'));
		$list[] = array('attributes_options', array('null', '25', '"Kierowcy"'));
		$list[] = array('attributes_options', array('null', '25', '"Nauczyciele"'));
		$list[] = array('attributes_options', array('null', '25', '"Prawo"'));
		$list[] = array('attributes_options', array('null', '25', '"Informatyka"'));

		//options - praca - stanowisko
		$list[] = array('attributes_options', array('null', '26', '"Asystent"'));
		$list[] = array('attributes_options', array('null', '26', '"Kierownik"'));
		$list[] = array('attributes_options', array('null', '26', '"Pracownik biurowy"'));
		$list[] = array('attributes_options', array('null', '26', '"Pracownik fizyczny"'));
		$list[] = array('attributes_options', array('null', '26', '"Praktykant"'));
		$list[] = array('attributes_options', array('null', '26', '"Specjalista"'));
		$list[] = array('attributes_options', array('null', '26', '"Inne"'));

		//options - praca - rodzaj pracy
		$list[] = array('attributes_options', array('null', '27', '"Stała"'));
		$list[] = array('attributes_options', array('null', '27', '"Tymczasowa"'));
		$list[] = array('attributes_options', array('null', '27', '"Dodatkowa"'));

		//options - praca - forma zatrudnienia
		$list[] = array('attributes_options', array('null', '28', '"Praktyka"'));
		$list[] = array('attributes_options', array('null', '28', '"Umowa o działo"'));
		$list[] = array('attributes_options', array('null', '28', '"Umowa o pracę"'));
		$list[] = array('attributes_options', array('null', '28', '"Umowa zlecenie"'));
		$list[] = array('attributes_options', array('null', '28', '"Inna"'));

		//categories_attributes - nieruchomosci 4-6
		$list[] = array('categories_attributes', array('4', '1', 'true'));
		$list[] = array('categories_attributes', array('4', '2', 'false'));
		$list[] = array('categories_attributes', array('4', '3', 'true'));
		$list[] = array('categories_attributes', array('4', '4', 'true'));
		$list[] = array('categories_attributes', array('4', '5', 'false'));
		$list[] = array('categories_attributes', array('4', '6', 'true'));
		$list[] = array('categories_attributes', array('4', '7', 'false'));
		$list[] = array('categories_attributes', array('4', '8', 'false'));
		$list[] = array('categories_attributes', array('4', '9', 'false'));
		$list[] = array('categories_attributes', array('4', '10', 'false'));
		$list[] = array('categories_attributes', array('4', '11', 'false'));
		$list[] = array('categories_attributes', array('4', '12', 'false'));

		$list[] = array('categories_attributes', array('5', '1', 'true'));
		$list[] = array('categories_attributes', array('5', '2', 'false'));
		$list[] = array('categories_attributes', array('5', '3', 'true'));
		$list[] = array('categories_attributes', array('5', '4', 'true'));
		$list[] = array('categories_attributes', array('5', '5', 'false'));
		$list[] = array('categories_attributes', array('5', '6', 'true'));
		$list[] = array('categories_attributes', array('5', '7', 'false'));
		$list[] = array('categories_attributes', array('5', '8', 'false'));
		$list[] = array('categories_attributes', array('5', '9', 'false'));
		$list[] = array('categories_attributes', array('5', '10', 'false'));
		$list[] = array('categories_attributes', array('5', '11', 'false'));
		$list[] = array('categories_attributes', array('5', '12', 'false'));

		$list[] = array('categories_attributes', array('6', '1', 'true'));
		$list[] = array('categories_attributes', array('6', '2', 'false'));
		$list[] = array('categories_attributes', array('6', '3', 'true'));
		$list[] = array('categories_attributes', array('6', '4', 'true'));
		$list[] = array('categories_attributes', array('6', '5', 'false'));
		$list[] = array('categories_attributes', array('6', '6', 'true'));
		$list[] = array('categories_attributes', array('6', '7', 'false'));
		$list[] = array('categories_attributes', array('6', '8', 'false'));
		$list[] = array('categories_attributes', array('6', '9', 'false'));
		$list[] = array('categories_attributes', array('6', '10', 'false'));
		$list[] = array('categories_attributes', array('6', '11', 'false'));
		$list[] = array('categories_attributes', array('6', '12', 'false'));

		//cat_att motoryzacja 7-9
		$list[] = array('categories_attributes', array('7', '1', 'true'));
		$list[] = array('categories_attributes', array('7', '13', 'true'));
		$list[] = array('categories_attributes', array('7', '14', 'true'));
		$list[] = array('categories_attributes', array('7', '15', 'false'));
		$list[] = array('categories_attributes', array('7', '16', 'true'));
		$list[] = array('categories_attributes', array('7', '17', 'true'));
		$list[] = array('categories_attributes', array('7', '18', 'false'));
		$list[] = array('categories_attributes', array('7', '19', 'false'));
		$list[] = array('categories_attributes', array('7', '20', 'false'));
		$list[] = array('categories_attributes', array('7', '21', 'false'));
		$list[] = array('categories_attributes', array('7', '22', 'false'));
		$list[] = array('categories_attributes', array('7', '23', 'false'));
		$list[] = array('categories_attributes', array('7', '24', 'false'));

		$list[] = array('categories_attributes', array('8', '1', 'true'));
		$list[] = array('categories_attributes', array('8', '13', 'true'));
		$list[] = array('categories_attributes', array('8', '14', 'true'));
		$list[] = array('categories_attributes', array('8', '15', 'false'));
		$list[] = array('categories_attributes', array('8', '16', 'true'));
		$list[] = array('categories_attributes', array('8', '17', 'true'));
		$list[] = array('categories_attributes', array('8', '18', 'false'));
		$list[] = array('categories_attributes', array('8', '19', 'false'));
		$list[] = array('categories_attributes', array('8', '20', 'false'));
		$list[] = array('categories_attributes', array('8', '21', 'false'));
		$list[] = array('categories_attributes', array('8', '22', 'false'));
		$list[] = array('categories_attributes', array('8', '23', 'false'));
		$list[] = array('categories_attributes', array('8', '24', 'false'));

		$list[] = array('categories_attributes', array('9', '1', 'true'));
		$list[] = array('categories_attributes', array('9', '13', 'true'));
		$list[] = array('categories_attributes', array('9', '14', 'true'));
		$list[] = array('categories_attributes', array('9', '15', 'false'));
		$list[] = array('categories_attributes', array('9', '16', 'true'));
		$list[] = array('categories_attributes', array('9', '17', 'true'));
		$list[] = array('categories_attributes', array('9', '18', 'false'));
		$list[] = array('categories_attributes', array('9', '19', 'false'));
		$list[] = array('categories_attributes', array('9', '20', 'false'));
		$list[] = array('categories_attributes', array('9', '21', 'false'));
		$list[] = array('categories_attributes', array('9', '22', 'false'));
		$list[] = array('categories_attributes', array('9', '23', 'false'));
		$list[] = array('categories_attributes', array('9', '24', 'false'));

		//cat_att praca 10-11
		$list[] = array('categories_attributes', array('10', '25', 'false'));
		$list[] = array('categories_attributes', array('10', '26', 'false'));
		$list[] = array('categories_attributes', array('10', '27', 'false'));
		$list[] = array('categories_attributes', array('10', '28', 'false'));

		$list[] = array('categories_attributes', array('11', '25', 'false'));
		$list[] = array('categories_attributes', array('11', '26', 'false'));
		$list[] = array('categories_attributes', array('11', '27', 'false'));
		$list[] = array('categories_attributes', array('11', '28', 'false'));

		//images
		/*$list[] = array('images', array('null', '"someImage1.jpg"')); //1
		 $list[] = array('images', array('null', '"someImage2.jpg"')); //2
		$list[] = array('images', array('null', '"someImage3.jpg"')); //3

		//announcements
		/*$list[] = array('announcements', array('null', '1', '1', '"AnnTitle1"', '"Content1"', $time, $time + (60 * 60 * 24 *7)));
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
		$list[] = array('attributes_values', array('4', '4', '1', 'null', 'null')); //bool*/

		$db = Zend_Registry::get('db');

		foreach($list as $item) {
			$db -> query('INSERT INTO '.$item[0].' VALUES('.implode(',', $item[1]).')');
		}

		$this -> _redirect('index');
	}

	public function indexAction() {
	}

}

