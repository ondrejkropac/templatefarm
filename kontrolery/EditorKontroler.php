<?php

/**
 * Kontroler pro výpis článků
 */
class EditorKontroler extends Kontroler
{
	
    public function zpracuj(array $parametry) : void
    {
		// Hlavička stránky
		$this->hlavicka['titulek'] = 'Editor článků';			
		// Vytvoření instance modelu
		$spravceClanku = new SpravceClanku();
		// Příprava prázdného článku
		$clanek = array(
			'clanky_id' => '',
			'titulek' => '',
			'obsah' => '',
			'url' => '',
			'popisek' => '',
			'klicova_slova' => '',
		);
		// Je odeslán formulář
		if ($_POST)
		{
			// Získání článku z $_POST
			$klice = array('titulek', 'obsah', 'url', 'popisek', 'klicova_slova');
			$clanek = array_intersect_key($_POST, array_flip($klice));
			// Uložení článku do DB
			$spravceClanku->ulozClanek($_POST['clanky_id'], $clanek);
			$this->pridejZpravu('Článek byl úspěšně uložen.');
			$this->presmeruj('clanek/' . $clanek['url']);
		}
		// Je zadané URL článku k editaci
		else if (!empty($parametry[0]))
		{
			$nactenyClanek = $spravceClanku->vratClanek($parametry[0]);			
			if ($nactenyClanek)
				$clanek = $nactenyClanek;
			else
				$this->pridejZpravu('Článek nebyl nalezen');
		}
			
		$this->data['clanek'] = $clanek;
		$this->pohled = 'editor';
    }
}