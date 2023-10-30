<?php

/**
 * Router je speciální typ controlleru, který podle URL adresy zavolá
 * správný controller a jím vytvořený pohled vloží do šablony stránky
 */
class SmerovacKontroler extends Kontroler
{
    /**
     * @var Kontroler Instance kontrolleru
     */
	protected Kontroler $kontroler;

    /**
     * Metoda převede pomlčkovou variantu controlleru na název třídy
     * @param string $text Řetězec v pomlckove-notaci
     * @return string Řetězec převedený do velbloudiNotace
     */
	private function pomlckyDoVelbloudiNotace(string $text) : string
	{
		$veta = str_replace('-', ' ', $text);
		$veta = ucwords($veta);
		$veta = str_replace(' ', '', $veta);
		return $veta;
	}

    /**
     * Naparsuje URL adresu podle lomítek a vrátí pole parametrů
     * @param string $url URL adresa k naparsování
     * @return array Pole URL parametrů
     */
	private function parsujURL(string $url) : array
	{
		// Naparsuje jednotlivé části URL adresy do asociativního pole
        $naparsovanaURL = parse_url($url);
		// Odstranění počátečního lomítka
		$naparsovanaURL["path"] = ltrim($naparsovanaURL["path"], "/");
		// Odstranění bílých znaků kolem adresy
		$naparsovanaURL["path"] = trim($naparsovanaURL["path"]);
		// Rozbití řetězce podle lomítek
		$rozdelenaCesta = explode("/", $naparsovanaURL["path"]);
		return $rozdelenaCesta;
	}

    /**
     * Naparsování URL adresy a vytvoření příslušného controlleru
     * @param array $parametry
     * @return void
     */
    public function zpracuj(array $parametry) : void
    {
		$naparsovanaURL = $this->parsujURL($parametry[0]);
		$spravceUzivatelu = new SpravceUzivatelu();
		$this->data['uzivatel'] = $uzivatel = $spravceUzivatelu->vratUzivatele(); 
		$this->data['userPrivatUrl'] = $this->data['privatUrlId'] = null;

		if (empty($naparsovanaURL[0]))		
			$this->presmeruj('all');
			
		// kontroler je 1. parametr URL
		$tridaKontroleru = $this->pomlckyDoVelbloudiNotace(array_shift($naparsovanaURL)) . 'Kontroler';

			if (file_exists('kontrolery/' . $tridaKontroleru . '.php')) 
				$this->kontroler = new $tridaKontroleru;
			else
				$this->presmeruj('template/all');
			// Volání controlleru
			$this->kontroler->zpracuj($naparsovanaURL);
			
			// Nastavení proměnných pro šablonu
			$this->data['zpravy'] = $this->vratZpravy();

			// Nastavení hlavní šablony
		if ($tridaKontroleru != 'TemplateKontroler'){
			$this->pohled = 'rozlozeni';
			$this->data['url'] = "";			
		}else{

			$id=$this->data['user_id'] = ''; 
			$this->data['privatUrlId'] = $naparsovanaURL[0];

			// pokud je v url za template all vypiš všechny uložené uživatelské profily - veřejné nebo "přihlášeného" uživatele...
			if (isset($naparsovanaURL[0])) {
				if (!(in_array($naparsovanaURL[0], $this->kontroler->customTemplatesUrls))){//customTemplatesUrls - pole všch url adres výsledného návrhu webdesignu - hotových webů
				$this->pohled = 'rozlozeni';
					if (($naparsovanaURL[0] != 'all')&($naparsovanaURL[0] != 'preview'))  {
						$users = $spravceUzivatelu->vratAllUsers();
		
						foreach ($users as $user) {
							$userNames[] = $user['jmeno'];
							$userPrivatUrl[] = $user['privat_url'];
						}

						if (in_array($naparsovanaURL[0], $userPrivatUrl)){
							$this->data['idUrl'] = $naparsovanaURL[0] = ($spravceUzivatelu->vratPrivatIdUzivatele($naparsovanaURL[0])['uzivatele_id']);//
						}
						$this->data['user_name'] = $spravceUzivatelu->vratJmenoUzivatele($naparsovanaURL[0])['jmeno'];
						$this->data['userPrivatUrl'] = $spravceUzivatelu->vratJmenoUzivatele($naparsovanaURL[0])['privat_url'];
						$this->data['user_id'] = $naparsovanaURL[0]; // pro button přihlášení na horní liště
					}
				}
				// zobraz výsledný návrh webu
				else $this->pohled = $this->kontroler->customTemplate['temp'];
				if (isset($naparsovanaURL[2])) $this->data['user_id'] = $naparsovanaURL[2];
			}
			else
				$this->presmeruj('chyba');
				
			$this->data['jmeno'] = $this->kontroler->customTemplate['jmeno'];
			$this->data['mail'] = $this->kontroler->customTemplate['mail'];
			$this->data['tel'] = $this->kontroler->customTemplate['tel'];
			$this->data['adresa'] = $this->kontroler->customTemplate['adresa'];
			$this->data['sekundarni'] = $this->kontroler->customTemplate['sekundarni_slova'];

			// promenna pouze pro oznaceni aktivniho profilu v url uzivatelského templatu
			$this->data['url'] = $url = $this->kontroler->customTemplate['url'];
			$this->data['images'] = glob("IMAGES" . $url . "/*.jpg");
		}
		$this->data['titulek'] = $this->kontroler->customTemplate['titulek'];
		$this->data['popis'] = $this->kontroler->customTemplate['popis'];
		$this->data['klicova_slova'] = $this->kontroler->customTemplate['klicova_slova'];

		$this->data['kategorie'] = $polekateg = explode(',', $this->kontroler->customTemplate['klicova_slova']);
		if (isset($this->kontroler->customTemplate['socky'])){
		$this->data['pole_socky'] = $polesoc =  explode(',', $this->kontroler->customTemplate['socky']);
		$this->data['facebook']  = (mb_substr($polesoc[0], 5, (strlen($polesoc[0])-5)));
		$this->data['instagram'] = $socinsta = (mb_substr($polesoc[1], 6, (strlen($polesoc[1])-6))); }
    }

}