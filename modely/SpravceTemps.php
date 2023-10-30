<?php

class SpravceTemps
{
	
	/**
	 * Vrátí templateek z databáze podle jeho URL
	 * @param string $url URL templatku k zobrazení
	 * @return array Data templatku z databáze jako asociativní pole
	 */
	public function vratTemp(string $url) : array
	{
		return Db::dotazJeden('
			SELECT `temp_id`, `titulek`, `obsah`, `url`, `popisek`, `klicova_slova`, `doplnkova_slova`, `jmeno`, `mail`, `tel`, `adresa`, `socky`, `privacy`, `user_name`
			FROM `temps` 
			WHERE `url` = ?
		', array($url));
	}
	
	/**
	 * Uloží templatek do systému. Pokud je ID false, vloží nový, jinak provede editaci.
	 * @param int|bool $id ID templatku k editaci, FALSE pro vložení nového templatku
	 * @param array $temp Asociativní pole s informacemi o templatku
	 * @return void
	 */
	public function ulozTemp(int|bool $id, array $temp) : void
	{
		if (!$id)
			Db::vloz('temps', $temp);
		else
			Db::zmen('temps', $temp, 'WHERE temp_id = ?', array($id));
	}
	
	/**
	 * Vrátí seznam templatků v databázi
	 * @return array Základní informace o všech templatcích jako numerické pole asociativních polí
	 */
	public function vratTemps() : array
	{
		return Db::dotazVsechny('
			SELECT `temp_id`, `titulek`, `url`, `popisek`, `jmeno`, `privacy`
			FROM `temps` 
			ORDER BY `temp_id` DESC
		');
	}
		
	/**
	 * Vrátí seznam templatků v databázi
	 * @return array Základní informace o všech templatcích jako numerické pole asociativních polí
	 */
	public function vratUserTemps(int $user_name) : array
	{
		return Db::dotazVsechny('
			SELECT `temp_id`, `titulek`, `url`, `popisek`, `jmeno`, `privacy`
			FROM `temps` 
			WHERE `user_name` = ?
			ORDER BY `temp_id` DESC
		', array($user_name));
	}

	/**
	 * Vrátí seznam templatků v databázi
	 * @return array Základní informace o všech templatcích jako numerické pole asociativních polí
	 */
	public function vratPublicUserTemps(string $privacy) : array // pouzr pro veřejné ale může vyhledat i privatní atd..
	{//`temp_id` , `url`, , `titulek`, `popisek`
		return Db::dotazVsechny('
			SELECT `temp_id`, `jmeno`, `titulek`, `url`, `popisek`, `jmeno`, `privacy`
			FROM `temps` 
			WHERE `privacy` = ?
			ORDER BY `temp_id` DESC
		', array($privacy));
	}

	/**
	 * Vrátí seznam templatků v databázi
	 * @return array Základní informace o všech templatcích jako numerické pole asociativních polí
	 */
	public function vratAutorTemps(string $jmeno) : array // pouzr pro veřejné ale může vyhledat i privatní atd..
	{
		return Db::dotazVsechny('
			SELECT `temp_id`, `jmeno`, `titulek`, `url`, `popisek`, `jmeno`, `privacy`
			FROM `temps` 
			WHERE `jmeno` = ?
			ORDER BY `temp_id` DESC
		', array($jmeno));
	}

	/**
	 * Odstraní templatek
	 * @param string $url URL templatku k odstranění
	 * @return void
	 */
	public function odstranTemp(string $url) : void
	{
		Db::dotaz('
			DELETE FROM temps
			WHERE url = ?
		', array($url));
	}
			
	/** NEPOUŽITO!
	 * Vrátí soukromí templatu
	 * @param int $id temp_id templatku ke zobrazení
	 * @return  array
	 */
	public function getTempPrivacy(int $user_id) : array
	{
        Db::zmen('temps', $temp, 'WHERE temp_id = ?', array($id));
	}

	public function vratJmenoUzivatele(int $user_id) : array
	{
		return Db::dotazJeden('
			SELECT `privacy`
			FROM `temps` 
			WHERE `temp_id` = ?
		', array($temp_id));
	}

	/**
	 * Upraví soukromí templatu
	 * @param int $id temp_id templatku ke zobrazení
	 * @return void
	 */
	public function setTempPrivacy(int|bool $id, array $temp) : void
	{
        Db::zmen('temps', $temp, 'WHERE temp_id = ?', array($id));
	}
}