<?php

/*  _____ _______         _                      _
 * |_   _|__   __|       | |                    | |
 *   | |    | |_ __   ___| |___      _____  _ __| | __  ___ ____
 *   | |    | | '_ \ / _ \ __\ \ /\ / / _ \| '__| |/ / / __|_  /
 *  _| |_   | | | | |  __/ |_ \ V  V / (_) | |  |   < | (__ / /
 * |_____|  |_|_| |_|\___|\__| \_/\_/ \___/|_|  |_|\_(_)___/___|
 *                                _
 *              ___ ___ ___ _____|_|_ _ _____
 *             | . |  _| -_|     | | | |     |  LICENCE
 *             |  _|_| |___|_|_|_|_|___|_|_|_|
 *             |_|
 *
 * IT ZPRAVODAJSTVÍ  <>  PROGRAMOVÁNÍ  <>  HW A SW  <>  KOMUNITA
 *
 * Tento zdrojový kód je součástí výukových seriálů na
 * IT sociální síti WWW.ITNETWORK.CZ
 *
 * Kód spadá pod licenci prémiového obsahu a vznikl díky podpoře
 * našich členů. Je určen pouze pro osobní užití a nesmí být šířen.
 * Více informací na http://www.itnetwork.cz/licence
 */

/**
 * Pomocná třída, poskytující metody pro odeslání emailu
 */
class OdesilacEmailu
{
	
	/**
	 * Odešle email jako HTML, lze tedy používat základní HTML tagy a nové
	 * řádky je třeba psát jako <br /> nebo používat odstavce. Kódování je
	 * odladěno pro UTF-8.
	 * @param string $komu Adresa příjemce
	 * @param string $predmet Předmět e-mailu
	 * @param string $zprava Obsah e-mailu
	 * @param string $od Adresa odesílatele
	 * @return void
	 * @throws ChybaUzivatele Pokud se nepodaří e-mail odeslat
	 */
	public function odesli(string $komu, string $predmet, string $zprava, string $od) : void
	{
		$hlavicka = "From: " . $od;
		$hlavicka .= "\nMIME-Version: 1.0\n";
		$hlavicka .= "Content-Type: text/html; charset=\"utf-8\"\n";
		if (!mb_send_mail($komu, $predmet, $zprava, $hlavicka))
			throw new ChybaUzivatele('Email se nepodařilo odeslat.');
	}
	
	/**
	 * Zkontroluje zda byl zadán aktuální rok jako antispam a odešle email
	 * @param string $rok Současný rok vyplněný uživatelem
	 * @param string $komu Adresa příjemce
	 * @param string $predmet Předmět e-mailu
	 * @param string $zprava Obsah e-mailu
	 * @param string $od Adresa odesílatele
	 * @return void
	 * @throws ChybaUzivatele Pokud se nepodaří e-mail odeslat
	 */
	public function odesliSAntispamem(string $rok, string $komu, string $predmet, string $zprava, string $od) : void
	{
		if ($rok != date("Y"))
			throw new ChybaUzivatele('Chybně vyplněný antispam.');
		$this->odesli($komu, $predmet, $zprava, $od);
	}
	
}