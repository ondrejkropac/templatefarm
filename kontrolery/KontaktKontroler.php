<?php

/**
 * Kontroler pro zpracování stránky s kontaktním formulářem
 */
class KontaktKontroler extends Kontroler
{
	public function zpracuj(array $parametry) : void
	{
		$this->hlavicka = array(
			'titulek' => 'Kontaktní formulář',
			'klicova_slova' => 'kontakt, email, formulář',
			'popis' => 'Kontaktní formulář našeho webu.'
		);
		
		if ($_POST)
		{
			try
			{			
				$odesilacEmailu = new OdesilacEmailu();
				$odesilacEmailu->odesliSAntispamem($_POST['rok'], "admin@adresa.cz", "Email z webu", $_POST['zprava'], $_POST['email']);
				$this->pridejZpravu('Email byl úspěšně odeslán.');
				$this->presmeruj('kontakt');
			}
			catch (ChybaUzivatele $chyba)
			{
				$this->pridejZpravu($chyba->getMessage());
			}
		}
		
		$this->pohled = 'kontakt';
    }
}