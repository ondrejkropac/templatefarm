<?php

class UvodKontroler extends Kontroler
{
    public function zpracuj(array $parametry) : void
    {
		// Vytvoření instance modelu, který nám umožní pracovat s články
		$spravceClanku = new SpravceClanku();

			$clanek = $spravceClanku->vratClanek('uvod');
			// Pokud nebyl článek s danou URL nalezen, přesměrujeme na ChybaKontroler
			if (!$clanek)
				$this->presmeruj('chyba');
		
			// Hlavička stránky
			$this->hlavicka = array(
				'titulek' => $clanek['titulek'],
				'klicova_slova' => $clanek['klicova_slova'],
				'popis' => $clanek['popisek'],
			);
			
			// Naplnění proměnných pro šablonu		
			$this->data['titulek'] = $clanek['titulek'];
			$this->data['obsah'] = $clanek['obsah'];
			
			// Nastavení šablony
			$this->pohled = 'uvod';
    }
}