<?php

/**
 * Kontroler pro zpracování článku
 */
class RegistraceKontroler extends Kontroler
{
    public function zpracuj(array $parametry) : void
    {		
		// Hlavička stránky
		$this->hlavicka['titulek'] = 'Registrace';
		if ($_POST)
		{
			try
			{
				$spravceUzivatelu = new SpravceUzivatelu();
				$spravceUzivatelu->registruj($_POST['jmeno'], $_POST['heslo'], $_POST['heslo_znovu'], $_POST['rok']);
				$spravceUzivatelu->prihlas($_POST['jmeno'], $_POST['heslo']);				
				$this->pridejZpravu('Byl jste úspěšně zaregistrován.');
				$this->presmeruj('administrace');
			}
			catch (ChybaUzivatele $chyba)
			{
				$this->pridejZpravu($chyba->getMessage());
			}
		}
		// Nastavení šablony
		$this->pohled = 'registrace';
    }
}