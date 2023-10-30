<?php

class TemplatorKontroler extends Kontroler
{	
	private function resizeme($image_id, $new_width, $new_height, int $width, int $height): mixed
	{
		$layer = imagecreatetruecolor($new_width, $new_height);
		imagecopyresampled($layer, $image_id, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
		return $layer;
	} 
	
    public function zpracuj(array $parametry) : void
    {
		// Hlavička stránky
		$this->hlavicka['titulek'] = 'Editor Templatů';			
		// Vytvoření instance modelu
		$spravceTemps = new SpravceTemps();
		$spravceUzivatelu = new SpravceUzivatelu();
		// Příprava prázdného článku
		$temp = array(
			'temp_id' => '',
            'jmeno' => '',
            'mail' => '',
            'tel' => '',
            'adresa' => '',
			'titulek' => '',
			'obsah' => '',
			'url' => '',
			'popisek' => '',
			'klicova_slova' => '',
			'doplnkova_slova' => '',
			'socky' => 'Fcb: , Inst: , ...',
			'user_name' => '',
		);

		// sekce k nastavení user id při založení nového profilu
		$this->data['users'] = $users = $spravceUzivatelu->vratAllUsers();
		foreach ($users as $user) {
			$userNames[] = $user['jmeno'];
			$userPrivatUrl[] = $user['privat_url'];
			if ($user['role'] == 'public') $pubUser[] = $user['jmeno']; // doplnění veřejných uživatelských profilů
		}
		if (!empty($parametry[1])){		
			if (in_array($parametry[1], $userPrivatUrl))							
				$this->data['idUrl'] = $parametry[1] = ($spravceUzivatelu->vratPrivatIdUzivatele($parametry[0])['uzivatele_id']);
				$temp['user_name'] = $parametry[1];
			}

		// Je odeslán formulář
		if ($_POST)
		{
			if (!empty($_FILES["upload"]["name"][0])) {
			$file = $_FILES['upload'];
			$total = count($_FILES['upload']['name']);
			if (($_POST['temp_id']) == '') {
				mkdir("images/" . $_POST['url']);
			}
			for ($i = 0; $i < $total; $i++) { 
				
				$tmpFilePath = $_FILES['upload']['tmp_name'][$i];
				$fileName = $file["name"][$i];
				$fileSize = $file["size"][$i];
				$fileError = $file["error"][$i];
				$fileType = $file["type"][$i];

				$fileExt = explode('.', ($fileName)); 
				$fileActualExt = strtolower(end($fileExt)); 
				$this->pridejZpravu($_FILES["upload"]["name"][0]);
				$allowed = array('jpg');//, 'jpeg', 'png', 'pdf'
				if ($fileError === 0) {
					if (in_array($fileActualExt, $allowed)) {
						$newFilePath = "images/".$_POST['url']."/img_" . ($i) . "." . $fileActualExt;
						if ($fileSize < 900000) {
							if ($tmpFilePath != "") {
								if (move_uploaded_file($tmpFilePath, $newFilePath)) {
									$this->pridejZpravu($icon = 'fas fa-save', $text = 'Obrázek uložen.');
								} else {
									$this->pridejZpravu($icon = 'fas fa-0', $text = 'Obrázek nevybrán.');
								}
							} else {
								$this->pridejZpravu($icon = 'fas fa-clipboard', $text = 'Pokud se pokoušíte obrázek vložit, není dostatečně specifikován.');
							}
						} else {
							$this->pridejZpravu('Soubor je příliš velký a bude zmenšený na povolenou velikost.');
							$scale = (1200000 / $fileSize) * 2.75;
							list($width, $height) = getimagesize($tmpFilePath);
							$new_width = $width * $scale;
							$new_height = $height * $scale;

							if ($fileActualExt == 'jpg') {
								$image_id = imagecreatefromjpeg($tmpFilePath);
								$layer = $this->resizeme($image_id, $new_width, $new_height, $width, $height);
								imagejpeg($layer, $newFilePath);
							}
							$this->pridejZpravu('Obrázek zmenšen a uložen.');
						}
					} else {
						$this->pridejZpravu($icon = 'fas fa-folder-minus', $text = 'Nepovolený typ souboru.');
					}
				} else {
					echo '<b>Error</b>';
				}
				//konec cyklu
			}
		}
			// Získání článku z $_POST
			$klice = array('titulek', 'obsah', 'url', 'popisek', 'jmeno', 'mail', 'tel', 'adresa', 'klicova_slova', 'doplnkova_slova', 'socky', 'user_name');
			$temp = array_intersect_key($_POST, array_flip($klice));
			// Uložení článku do DB
			$spravceTemps->ulozTemp($_POST['temp_id'], $temp);
			$this->pridejZpravu('Uživatelský template byl úspěšně uložen.');
			$this->presmeruj('template/all/' . $parametry[0]);
		}
		
		// Je zadané URL článku k editaci
		else if (!empty($parametry[0]))
		{
			$nactenyTemp = $spravceTemps->vratTemp($parametry[0]);			
			if ($nactenyTemp)
				$temp = $nactenyTemp;
			else
				$this->pridejZpravu('Uživatelský template nebyl nalezen');
		}
			
		
		//$temp['socky'] = 'Fcb: , Inst: , ...';
		$temp['proj'] = 'Přehled projektů či služeb' ;
		$temp['exp'] = 'Dovednosti...' ;
		$this->data['temp'] = $temp;
		$this->pohled = 'templator';
    }
}