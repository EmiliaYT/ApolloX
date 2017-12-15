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

use pocketmine\Server;

class ThemeManager{
	
	public $availableThemes = [
		"classic",
		"dark",
		"light",
		"metal",
		"energy",
		"uranium"
	];
	
	const DEFAULT_THEME = "classic";
	
	public function __construct(Server $server){
		$this->server = $server;
	}
	
	public function getTheme(){
		$configTheme = $this->server->getConfigString("theme", ThemeManager::DEFAULT_THEME);
		
		if($this->server->getConfigInt("colorful-theme", "false")){
		    return $this->availableThemes[array_rand($this->availableThemes)];
		}
		
		if($configTheme === null){
			return false;
		}
		
		return $configTheme;
    }
    
    public function setTheme($value){
    	if(!in_array($value, $this->availableThemes)){
    	    return false;
    	}
    
    	if($value == $this->getTheme()){
    	    return false;
    	}
    
		$this->server->setConfigString("theme", $value);
    }
    
    public function getDefaultTheme(){
    	return ThemeManager::DEFAULT_THEME;
    }
    
    //ASCII Text Font: Doom
    public function getLogoTheme($version, $mcpe, $protocol, $codename, $splash){
    	$name = $this->server->getSoftwareName();
    	if(mt_rand(1, 100) == 1){ //%1 chance
    	    $random = substr(base64_encode(random_bytes(20)), 3, 10);
    	    return "
    
    §f______      _         _____       _             
    §f|  _  \    | |       /  ___|     | |            
    §f| | | |__ _| | ___ __\ `--. _   _| |_ ___ _ __  
    §f| | | / _` | |/ / '__|`--. \ | | | __/ _ \ '_ \ 
    §f| |/ / (_| |   <| |  /\__/ / |_| | ||  __/ | | |
    §f|___/ \__,_|_|\_\_|  \____/ \__, |\__\___|_| |_|
                                 §f__/  |              
                                 §f|___/               
                             
      §bDakrSyten 9.9.9  *$random*
      
			";
		}
    	switch($this->getTheme()){
    	    case "darkness":
    	    $this->setTheme(ThemeManager::DEFAULT_THEME);
			return "
			
	§dYOU §aFOUND §eAN §cEASTER §6EGG §3LOL :D
	
			";
			break;
			case "classic":
			return "
			
    §f______           _    _____           _                  
    §7|  _  \         | |  /  ___|         | |                  
    §f| | | |__ _ _ __| | _\ `--. _   _ ___| |_ ___ _ __ ___   
    §7| | | / _` | '__| |/ /`--. \ | | / __| __/ _ \ '_ ` _ \  
    §f| |/ / (_| | |  |   </\__/ / |_| \__ \ ||  __/ | | | | | 
    §7|___/ \__,_|_|  |_|\_\____/ \__, |___/\__\___|_| |_| |_| 
                                 §f__/  |      
                                 §7|___/         §6MCPE: $mcpe §e($protocol)
      $splash
                                      
      §a$name $version  *$codename*
      
			";
			break;
			case "dark":
			return "
			
    §7______           _    _____           _                  
    §8|  _  \         | |  /  ___|         | |                  
    §6| | | |__ _ _ __| | _\ `--. _   _ ___| |_ ___ _ __ ___   
    §7| | | / _` | '__| |/ /`--. \ | | / __| __/ _ \ '_ ` _ \  
    §8| |/ / (_| | |  |   </\__/ / |_| \__ \ ||  __/ | | | | | 
    §3|___/ \__,_|_|  |_|\_\____/ \__, |___/\__\___|_| |_| |_| 
                                 §7__/  |      
                                 §8|___/         §6MCPE: $mcpe §2($protocol)
      $splash
                                      
      §9$name $version  *$codename*
      
			";
			break;
			case "light":
			return "
			
    §f______           _    _____           _                  
    §f|  _  \         | |  /  ___|         | |                  
    §f| | | |__ _ _ __| | _\ `--. _   _ ___| |_ ___ _ __ ___   
    §f| | | / _` | '__| |/ /`--. \ | | / __| __/ _ \ '_ ` _ \  
    §f| |/ / (_| | |  |   </\__/ / |_| \__ \ ||  __/ | | | | | 
    §f|___/ \__,_|_|  |_|\_\____/ \__, |___/\__\___|_| |_| |_| 
                                 §f__/  |      
                                 §f|___/          §bMCPE: $mcpe §e($protocol)
      $splash
                                      
      §f$name $version  *$codename*
      
			";
			break;
			case "metal":
			return "
			
    §f______           _    _____           _                  
    §7|  _  \         | |  /  ___|         | |                  
    §f| | | |__ _ _ __| | _\ `--. _   _ ___| |_ ___ _ __ ___   
    §f| | | / _` | '__| |/ /`--. \ | | / __| __/ _ \ '_ ` _ \  
    §7| |/ / (_| | |  |   </\__/ / |_| \__ \ ||  __/ | | | | | 
    §f|___/ \__,_|_|  |_|\_\____/ \__, |___/\__\___|_| |_| |_| 
                                 §f__/  |      
                                 §f|___/          §bMCPE: $mcpe §e($protocol)
      $splash
                                      
      §d$name $version  *$codename*
      
			";
			break;
			case "energy":
			return "
			
    §f______           _    _____           _                  
    §e|  _  \         | |  /  ___|         | |                  
    §f| | | |__ _ _ __| | _\ `--. _   _ ___| |_ ___ _ __ ___   
    §e| | | / _` | '__| |/ /`--. \ | | / __| __/ _ \ '_ ` _ \  
    §f| |/ / (_| | |  |   </\__/ / |_| \__ \ ||  __/ | | | | | 
    §e|___/ \__,_|_|  |_|\_\____/ \__, |___/\__\___|_| |_| |_| 
                                 §f__/  |      
                                 §e|___/         §aMCPE: $mcpe §b($protocol)
      $splash
                                      
      §e$name $version  *$codename*
      
			";
			break;
			case "uranium":
			return "
			
    §f______           _    _____           _                  
    §7|  _  \         | |  /  ___|         | |                  
    §a| | | |__ _ _ __| | _\ `--. _   _ ___| |_ ___ _ __ ___   
    §f| | | / _` | '__| |/ /`--. \ | | / __| __/ _ \ '_ ` _ \  
    §7| |/ / (_| | |  |   </\__/ / |_| \__ \ ||  __/ | | | | | 
    §a|___/ \__,_|_|  |_|\_\____/ \__, |___/\__\___|_| |_| |_| 
                                 §f__/  |      
                                 §7|___/         §eMCPE: $mcpe §b($protocol)
      $splash
                                      
      §a$name $version  *$codename*
      
			";
			break;
			default;
			return "
			
    §f______           _    _____           _                  
    §7|  _  \         | |  /  ___|         | |                  
    §f| | | |__ _ _ __| | _\ `--. _   _ ___| |_ ___ _ __ ___   
    §7| | | / _` | '__| |/ /`--. \ | | / __| __/ _ \ '_ ` _ \  
    §f| |/ / (_| | |  |   </\__/ / |_| \__ \ ||  __/ | | | | | 
    §7|___/ \__,_|_|  |_|\_\____/ \__, |___/\__\___|_| |_| |_| 
                                 §f__/  |      
                                 §7|___/         §6MCPE: $mcpe §e($protocol)
      $splash
                                      
      §a$name $version  *$codename*
      
			";
			break;
		}
    }   
}
