<?php

#______           _    _____           _                  
#|  _  \         | |  /  ___|         | |                 
#| | | |__ _ _ __| | _\ `--. _   _ ___| |_ ___ _ __ ___   
#| | | / _` | '__| |/ /`--. \ | | / __| __/ _ \ '_ ` _ \  
#| |/ / (_| | |  |   </\__/ / |_| \__ \ ||  __/ | | | | | 
#|___/ \__,_|_|  |_|\_\____/ \__, |___/\__\___|_| |_| |_| 
#                             __/ |                       
#                            |___/

namespace darksystem;

use darksystem\language\Language;
use darksystem\language\StringContainer;
use pocketmine\event\TextContainer;

//Based on Language.php

class StringTranslator{
	
	protected $langName;

	protected $lang = [];
	protected $fallbackLang = [];

	public function __construct($lang, $path = null, $fallback = Language::FALLBACK_LANGUAGE){
		$this->langName = strtolower($lang);

		if($path === null){
			$path = \pocketmine\PATH . "src/darksystem/strings/";
		}

		$this->loadStringData($path . $this->langName . ".ini", $this->lang);
		$this->loadStringData($path . $fallback . ".ini", $this->fallbackLang);
	}
	
	protected function loadStringData($path, array &$d){
		if(file_exists($path) && strlen($content = file_get_contents($path)) > 0){
			foreach(explode("\n", $content) as $line){
				$line = trim($line);
				if($line === "" || $line{0} === "#"){
					continue;
				}

				$t = explode("=", $line);
				if(count($t) < 2){
					continue;
				}

				$key = trim(array_shift($t));
				$value = trim(implode("=", $t));

				if($value === ""){
					continue;
				}

				$d[$key] = $value;
			}
		}
	}
	
	public function translateStringSecond($str, array $params = [], $onlyPrefix = null){
		$baseText = $this->get($str);
		$baseText = $this->parseString(($baseText !== null && ($onlyPrefix === null || strpos($str, $onlyPrefix) === 0)) ? $baseText : $str, $onlyPrefix);

		foreach($params as $i => $p){
			$baseText = str_replace("{%$i}", $this->parseString((string) $p), $baseText, $onlyPrefix);
		}

		return str_replace("%0", "", $baseText);
	}

	public function translateString(TextContainer $c){
		if($c instanceof StringContainer){
			$baseText = $this->internalGet($c->getText());
			$baseText = $this->parseString($baseText !== null ? $baseText : $c->getText());

			foreach($c->getParameters() as $i => $p){
				$baseText = str_replace("{%$i}", $this->parseString($p), $baseText);
			}
		}else{
			$baseText = $this->parseString($c->getText());
		}

		return $baseText;
	}

	public function internalGet($id){
		if(isset($this->lang[$id])){
			return $this->lang[$id];
		}elseif(isset($this->fallbackLang[$id])){
			return $this->fallbackLang[$id];
		}

		return null;
	}

	public function get($id){
		if(isset($this->lang[$id])){
			return $this->lang[$id];
		}elseif(isset($this->fallbackLang[$id])){
			return $this->fallbackLang[$id];
		}

		return $id;
	}

	protected function parseString($text, $onlyPrefix = null){
		$newString = "";

		$replaceString = null;

		$len = strlen($text);
		for($i = 0; $i < $len; ++$i){
			$c = $text{$i};
			if($replaceString !== null){
				$ord = ord($c);
				if(
					($ord >= 0x30 && $ord <= 0x39)
					|| ($ord >= 0x41 && $ord <= 0x5a)
					|| ($ord >= 0x61 && $ord <= 0x7a) ||
					$c === "." || $c === "-"
				){
					$replaceString .= $c;
				}else{
					if(($t = $this->internalGet(substr($replaceString, 1))) !== null && ($onlyPrefix === null || strpos($replaceString, $onlyPrefix) === 1)){
						$newString .= $t;
					}else{
						$newString .= $replaceString;
					}
					
					$replaceString = null;

					if($c === "%"){
						$replaceString = $c;
					}else{
						$newString .= $c;
					}
				}
			}elseif($c === "%"){
				$replaceString = $c;
			}else{
				$newString .= $c;
			}
		}

		if($replaceString !== null){
			if(($t = $this->internalGet(substr($replaceString, 1))) !== null && ($onlyPrefix === null || strpos($replaceString, $onlyPrefix) === 1)){
				$newString .= $t;
			}else{
				$newString .= $replaceString;
			}
		}

		return $newString;
	}
	
}
