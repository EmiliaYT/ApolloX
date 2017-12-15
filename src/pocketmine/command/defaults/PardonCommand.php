<?php

#______           _    _____           _                  
#|  _  \         | |  /  ___|         | |                 
#| | | |__ _ _ __| | _\ `--. _   _ ___| |_ ___ _ __ ___   
#| | | / _` | '__| |/ /`--. \ | | / __| __/ _ \ '_ ` _ \  
#| |/ / (_| | |  |   </\__/ / |_| \__ \ ||  __/ | | | | | 
#|___/ \__,_|_|  |_|\_\____/ \__, |___/\__\___|_| |_| |_| 
#                             __/ |                       
#                            |___/

namespace pocketmine\command\defaults;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\TranslationContainer;
use pocketmine\utils\UUID;

class PardonCommand extends VanillaCommand{
	
	public function __construct($name){
		parent::__construct(
			$name,
			"%pocketmine.command.unban.player.description",
			"%commands.unban.usage"
		);
		$this->setPermission("pocketmine.command.unban.player");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) !== 1){
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));
			return false;
		}

		$sender->getServer()->getNameBans()->remove($args[0]);
		$mapFilePath = $sender->getServer()->getDataPath() . "banned-player-uuid-map.yml";
		
		if(file_exists($mapFilePath)){
			$mapFileData = yaml_parse_file($mapFilePath);

			if(isset($mapFileData[strtolower($args[0])])){
				try{
					$uuid = UUID::fromString($mapFileData[strtolower($args[0])]);

					$sender->getServer()->getUUIDBans()->remove($uuid->toString());
				}catch(\Exception $exception){
					$sender->getServer()->getLogger()->debug("UUID for pardoned player found, but invalid");
				}

				unset($mapFileData[$args[0]]);
			}

			yaml_emit_file($mapFilePath, $mapFileData);
		}
		
		Command::broadcastCommandMessage($sender, new TranslationContainer("commands.unban.success", [$args[0]]));

		return true;
	}
}