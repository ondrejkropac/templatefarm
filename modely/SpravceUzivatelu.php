<?php

// Správce uživatelů redakčního systému
class SpravceUzivatelu
{	
	
	/**
	 * Vrátí otisk hesla
	 * @param string $heslo Heslo pro vypočítání otisku
	 * @return string Otisk hesla
	 */
	public function vratOtisk(string $heslo) : string
	{
		return password_hash($heslo, PASSWORD_DEFAULT);
	}

	/**
	 * Registruje nového uživatele do systému
	 * @param string $jmeno Přihlašovací jméno
	 * @param string $heslo Přihlašovací heslo
	 * @param string $hesloZnovu Zopakované heslo
	 * @param string $rok Zadaný rok do antispamového pole
	 * @return void
	 */
	public function registruj(string $jmeno, string $heslo, string $hesloZnovu, string $rok) : void
	{
		if ($rok != date('Y'))
			throw new ChybaUzivatele('Chybně vyplněný antispam.');
		if ($heslo != $hesloZnovu)
			throw new ChybaUzivatele('Hesla nesouhlasí.');
		$uzivatel = array(
			'jmeno' => $jmeno,
			'heslo' => $this->vratOtisk($heslo),
		);
		try
		{
			Db::vloz('uzivatele', $uzivatel);
		}
		catch (PDOException $chyba)
		{
			throw new ChybaUzivatele('Uživatel s tímto jménem je již zaregistrovaný.');
		}
	}

	/**
	 * Přihlásí uživatele do systému
	 * @param string $jmeno Přihlašovací jméno
	 * @param string $heslo Přihlašovací heslo
	 * @return void
	 */
	public function prihlas(string $jmeno, string $heslo) : void
	{
		$uzivatel = Db::dotazJeden('
			SELECT uzivatele_id, jmeno, admin, heslo, role
			FROM uzivatele
			WHERE jmeno = ?
		', array($jmeno));
		if (!$uzivatel || !password_verify($heslo, $uzivatel['heslo']))
			throw new ChybaUzivatele('Neplatné jméno nebo heslo.');
		$_SESSION['uzivatel'] = $uzivatel;
	}
	
	/**
	 * Odhlásí uživatele
	 * @return void
	 */
	public function odhlas() : void
	{
		unset($_SESSION['uzivatel']);
	}
	
	/**
	 * Vrátí aktuálně přihlášeného uživatele
	 * @return array|null Pole s informacemi o přihlášeném uživateli nebo NULL, pokud není žádný uživatel přihlášen
	 */
	public function vratUzivatele() : array|null
	{
		if (isset($_SESSION['uzivatel']))
			return $_SESSION['uzivatel'];
		return null;
	}
	
	/**
	 * Vrátí jm0no už z databáze podle jeho ID
	 * @param string $user_id ID uživ k zobrazení
	 * @return array Jméno už. z databáze
	 */
	public function vratJmenoUzivatele(mixed $user_id) : array
	{
		return Db::dotazJeden('
			SELECT `jmeno`, `role`, `admin`, `privat_url`
			FROM `uzivatele` 
			WHERE `uzivatele_id` = ?
		', array($user_id));
	}

	
	/**
    * Vrátí ID už z databáze podle jeho jména
	 * @param string Jméno uživ k zobrazení
	 * @return array $user_id ID už. z databáze
	 */
	public function vratIdUzivatele(string $jmeno) : array
	{
		return Db::dotazJeden('
			SELECT `uzivatele_id`
			FROM `uzivatele` 
			WHERE `jmeno` = ?
		', array($jmeno));
	}
		
	/**
    * Vrátí ID už z databáze podle jeho jména
	 * @param string Jméno uživ k zobrazení
	 * @return array $user_id ID už. z databáze
	 */
	public function vratPrivatIdUzivatele(string $privat_url) : array
	{
		return Db::dotazJeden('
			SELECT `uzivatele_id`
			FROM `uzivatele` 
			WHERE `privat_url` = ?
		', array($privat_url));
	}
	/**
	 * Upraví soukromí uživatele
	 * @param int $id uzivatel_id templatku ke zobrazení
	 * @return void
	 */
	public function setUserPrivacy(int|bool $id, array $uzivatel) : void
	{
        Db::zmen('uzivatele', $uzivatel, 'WHERE uzivatele_id = ?', array($id));
	}

	/**
	 * Vrátí seznam u6ivatelů v databázi
	 * @return array Základní informace o všech users jako numerické pole asociativních polí
	 */
	public function vratAllUsers() : array
	{
		return Db::dotazVsechny('
			SELECT `uzivatele_id`, `jmeno`, `admin`, `role`, `privat_url`
			FROM `uzivatele` 
			ORDER BY `uzivatele_id` DESC
		');
	}
}
