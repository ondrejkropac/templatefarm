<?php

/*
*	TemplateKontroler je hlavní třída obsluhující vyhreslení uložených uživatelských dat v sekci PŘEHLED menu webu
*	uživatelsky definovaný obsah určený k zobrazení v templatu-návrhu webu je uložený v tabulce temps databáze
*	v dokumentaci používám pro nastavená a uložená data pojem profil - proměnná $temp.
*
*	implementace: třída používá tři vrstvy 
*			1. uživatele z DB /v tabulce temps databáze volitelně uložené pod 'user_id'/ 
*			2. autory uložených nastavení-profilů /sloupec 'jméno' v DB tabulce temps/
*			3. jednotlivé uložené nastavení dat pro vykreslení v návrhu vebu (profily) z tabulky temps
*
*	web zobrazuje v části přehled nalevo autory a uživatele /podle nastaveného soukromí/ v levé části uložená data jednotlivých profilů
*/

// Template kontroler načte data z tabulky temps do pole customTemplate, které se poté čte ve SmerovacKontroleru a načítá do samostatné šablony jednotlivých templatů/návrhů webů

class TemplateKontroler extends Kontroler
{
	public function zpracuj(array $parametry): void
	{
		// Vytvoření instance modelu, který nám umožní pracovat s články
		$spravceTemps = new SpravceTemps();
		$spravceUzivatelu = new SpravceUzivatelu();

		$this->data['uzivatel'] = $uzivatel = $spravceUzivatelu->vratUzivatele();
		if (!($uzivatel)) $this->data['uzivatel']['jmeno'] = null; // pro porovnávání vybraného a přihlášeného oživatele - případ, že není zalogovaný 

		//nastavení výchozí profilu(dat pro template) PR pokud by vznikla třída settings parametr fce vratTemp() by byl nastavitelný pro každého uživatele...
		$temp = $spravceTemps->vratTemp('example');

		//nastavení aktivovaného profilu
		$this->data['temp_url'] = "";

		if (isset($parametry[1]))
			if ((!empty($parametry[1])) & ($parametry[1] != 'setprivacy')) {
				// nastavení profilu
				$temp = $spravceTemps->vratTemp($parametry[1]);
				//proměnná pro active profil(do náhledů a odkazů na jednotlivé templaty)
				$this->data['temp_url'] = "/" . $temp['url'];
			}
		// Pokud nebyl článek s danou URL nalezen(nanajde ani výchozí profil), přesměrujeme na aktuální preview(veřejné profily) /*ChybaKontroler*/
		if (!$temp)
			$this->presmeruj('preview');

		// Nastavení parametrů pro přenost dat k vykreslení uživatelských informací ve finálním návrhu webu
		$this->customTemplate = array(
			'titulek' => $temp['titulek'],
			'klicova_slova' => $temp['klicova_slova'],
			'jmeno' => $temp['jmeno'],
			'mail' => $temp['mail'],
			'tel' => $temp['tel'],
			'adresa' => $temp['adresa'],
			'popis' => $temp['popisek'],
			'obsah' => $temp['obsah'],
			'socky' => $temp['socky'],
			'sekundarni_slova' => $temp['doplnkova_slova'],
			'url' => $this->data['temp_url'],
		);

		/** 
		 * v url se pod parametrem [0] zadává url adresa konkrétního profilu (uživatelských dat) nebo url all pro výpis všech dostupných
		 * preview slouží pro zjednodušený výpis bez aditace obsahu a zveřejňování
		 * 
		 */

		if (!empty($parametry[0])) {
			$this->customTemplate['temp'] = $parametry[0];
			$this->data['user_url'] = $parametry[0]; 

			$this->data['admin'] = $admin = $uzivatel && $uzivatel['admin'];
			$this->data['users'] = $users = $spravceUzivatelu->vratAllUsers();

			//PRrefactoring pole users potřebuju jak v all tak při načtení uživatelů...

			//načtení jmen, soukromých url, zveřejněných uživatelů z DB
			$pubUser = array();
			foreach ($users as $user) {
				$userNames[] = $user['jmeno'];
				$userPrivatUrl[] = $user['privat_url'];
				if ($user['role'] == 'public') $pubUser[] = $user['jmeno']; // doplnění veřejných uživatelských profilů
			}

			// pole všech url adres finálních webdesignů
			$this->customTemplatesUrls = array('most', 'gray', 'pers', 'about_most', 'service_most', 'gallery_most', 'contact_most');

			if (($parametry[0] == 'all') || ($parametry[0] == 'preview')) {

				// nulování proměných pro šablonu bez vybraného uživatele
				$this->data['user_name'] = null; 
				$this->data['userPublicy']['role'] = null;
				$publicUsersIds = $publicUsers = null; // pokud není zádný profil
				//nutné pro aktualizaci role-publicity po její úpravě!! 
				if (($uzivatel)) $this->data['logedinUserPublicy'] = $spravceUzivatelu->vratJmenoUzivatele($uzivatel['uzivatele_id'])['role'];
				else $this->data['logedinUserPublicy'] = null;

				// načtení veřejně dostupných profilů-nastavení
				$publicTemps = $spravceTemps->vratPublicUserTemps('public');

				// doplnění pole veřejných profilů o (veřejné autory) profily nastavené jak uživatel veřejný - privacy = 'user_public' !
				$publicUserTemps = $spravceTemps->vratPublicUserTemps('public_user');

				if (($publicUserTemps) || ($publicTemps)) {
					$publicTemps = array_merge($publicTemps, $publicUserTemps);
					foreach ($publicTemps as $publicTemp) {
						$publicNames[] = $publicTemp['jmeno'];
					}

					//pole jmen všech autorů s veřejnými profily
					$publicNames = array_merge($publicNames, $pubUser); 
					$publicUsers = array_unique($publicNames);
					
					foreach ($publicUsers as $userName) {
						if (in_array($userName, $userNames)) $link = ($spravceUzivatelu->vratIdUzivatele($userName)['uzivatele_id']);
						else $link = 'preview';
						$publicUsersIds[$userName][] = $link;
					}
				} elseif (isset($pubUser)){
						$publicUsers = array_unique($pubUser);
						foreach ($publicUsers as $userName) {
							$publicUsersIds[$userName][] = ($spravceUzivatelu->vratIdUzivatele($userName)['uzivatele_id']);
						}
				}

				$this->data['publicTemps'] = false;
				//nastavení všech profilů do šablony. Pro přihlášené adminy všechny. Pro ostatní pouze veřejné.
				if ($admin == 1) {
					$temps = $spravceTemps->vratTemps();
					$this->data['temps'] = $temps;
				} else {
					$this->data['temps'] = $publicTemps;
					$this->data['publicTemps'] = true;
				}

				if (($publicTemps) || ($pubUser)) {
					$this->data['usersIds'] = ($publicUsersIds);
				} else {$publicUsers = null; $publicUsersIds = '';}

				$this->data['publicUsers'] = $publicUsers;
			}

			// pokud není v url zadaná adresa pro vykreslení finálního návrhu customTemplate zobrazí výpis profilů/uložených dat konkrétního uživatele
			elseif (!(in_array($parametry[0], $this->customTemplatesUrls))){
			
				if (in_array($parametry[0], $userPrivatUrl)) {
					$this->data['idUrl'] = $parametry[0] = ($spravceUzivatelu->vratPrivatIdUzivatele($parametry[0])['uzivatele_id']);
				}

				// parametry pro zvoleného uživatele
				$this->data['user_name'] = $spravceUzivatelu->vratJmenoUzivatele($parametry[0])['jmeno'];
				$this->data['usePrivatUrl'] = $spravceUzivatelu->vratJmenoUzivatele($parametry[0])['privat_url'];
				$this->data['actualUser'] = $actualUser = $spravceUzivatelu->vratJmenoUzivatele($parametry[0]);
				$this->data['userPublicy'] = $actualUser['role'];
				$temps = $spravceTemps->vratUserTemps($parametry[0]);
				$this->data['temps'] = $temps;
				
			}

			$this->data['temp'] = $temp;

			if (!empty($parametry[2]) && $parametry[2] == 'setprivacy') {
				//nastavení soukromí/viditelnosti pro všechny u profilu/temp/
				if ($temp['privacy'] == 'public_user')
					$spravceTemps->setTempPrivacy($temp['temp_id'], (array('privacy' => " ")));
				else
					$spravceTemps->setTempPrivacy($temp['temp_id'], (array('privacy' => "public_user")));

				$this->pridejZpravu('Soukromí upraveno.');
				$this->presmeruj('template/all');
			}

			//BLOK PRO NASTAVENÍ SOUKROMÍ CELÉHO USERA
			if (!empty($parametry[1]) && $parametry[1] == 'setprivacy') {
				if ($actualUser['role'] == 'public')
					$spravceUzivatelu->setUserPrivacy($parametry[0], (array('role' => "")));
				else
					$spravceUzivatelu->setUserPrivacy($parametry[0], (array('role' => "public")));
				$this->presmeruj('template/' . $parametry[0]);
			}

			if (!empty($parametry[1]))
				$this->data['active_url'] = $temp['url'];
			$this->pohled = 'summary';

			if ((!empty($parametry[0])) & ($parametry[0] == 'preview')) {
				$this->pohled = 'preview';
			}

		} else

		// Není zadáno URL temp, vypíšeme všechny
		{
			$temps = $spravceTemps->vratTemps();
			$this->data['temps'] = $temps;
			$this->pohled = 'temps';
		}
	}
}