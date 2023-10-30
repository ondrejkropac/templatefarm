<?php

/**
 * Výchozí kontroler pro ITnetworkMVC
 */
abstract class Kontroler
{

    /**
     * @var array Pole, jehož indexy jsou poté viditelné v šabloně jako běžné proměnné
     */
    protected array $data = array();
    /**
     * @var string Název šablony bez přípony
     */
    protected string $pohled = "";
    /**
     * @var array|string[] Hlavička HTML stránky
     */
	protected array $hlavicka = array('titulek' => '', 'klicova_slova' => '', 'popis' => '');
	
    /**
     * @var array|string[] Data návrhu HTML stránky podle zvoleného templatu
     */
	protected array $customTemplate = array('titulek' => '', 'klicova_slova' => '', 'popis' => '');

	/**
	 * Ošetří proměnnou pro výpis do HTML stránky
	 * @param mixed $x Proměnná k ošetření
	 * @return mixed Proměnná ošetřená proti XSS útoku
	 */
	private function osetri($x = null)
	{
		if (!isset($x))
			return null;
		elseif (is_string($x))
			return htmlspecialchars($x, ENT_QUOTES);
		elseif (is_array($x))
		{
			foreach($x as $k => $v)
			{
				$x[$k] = $this->osetri($v);
			}
			return $x;
		}
		else 
			return $x;
	}

    /**
     * Vyrenderuje pohled
     * @return void
     */
    public function vypisPohled() : void
    {
        if ($this->pohled)
        {
            extract($this->osetri($this->data));
			extract($this->data, EXTR_PREFIX_ALL, "");
            require("pohledy/" . $this->pohled . ".phtml");
        }
    }
	
	/**
	 * Přidá zprávu pro uživatele
	 * @param string $zprava Hláška k zobrazení
	 * @return void
	 */
	public function pridejZpravu(string $zprava) : void
	{
		if (isset($_SESSION['zpravy']))
			$_SESSION['zpravy'][] = $zprava;
		else
			$_SESSION['zpravy'] = array($zprava);
	}
	
	/**
	 * Vrátí zprávy pro uživatele
	 * @return array Všechny uložené hlášky k zobrazení
	 */
	public function vratZpravy() : array
	{
		if (isset($_SESSION['zpravy']))
		{
			$zpravy = $_SESSION['zpravy'];
			unset($_SESSION['zpravy']);
			return $zpravy;
		}
		else
			return array();
	}

    /**
     * Přesměruje na dané URL
     * @param string $url URL adresa, na kterou přesměrovat
     * @return never
     */
	public function presmeruj(string $url) : never
	{
		header("Location: /$url");
		header("Connection: close");
        exit;
	}

	/**
	 * Ověří, zda je přihlášený uživatel, případně přesměruje na login
	 * @param bool $admin TRUE, pokud musí být přihlášený uživatel i administrátorem
	 * @return void
	 */
	public function overUzivatele(bool $admin = false) : void
	{
		$spravceUzivatelu = new SpravceUzivatelu();
		$uzivatel = $spravceUzivatelu->vratUzivatele();
		if (!$uzivatel || ($admin && !$uzivatel['admin']))
		{
			$this->pridejZpravu('Nedostatečná oprávnění.');
			$this->presmeruj('prihlaseni');
		}
	}

    /**
     * Hlavní metoda kontroleru
     * @param array $parametry Pole parametrů pro využití kontrolerem
     * @return void
     */
    abstract function zpracuj(array $parametry) : void;

}