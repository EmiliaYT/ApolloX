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
use pocketmine\Player;

class BanCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"%pocketmine.command.ban.player.description",
			"%commands.ban.usage"
		);
		$this->setPermission("pocketmine.command.ban.player");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) === 0){
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));

			return false;
		}

		$name = array_shift($args);
		if(isset($args[0]) and isset($args[1])){
			$reason = $args[0];
			if($args[1] != null and is_numeric($args[1])){
				$until = new \DateTime('@' . ($args[1] * 86400 + time()));
			}else{
				$until = null;
			}

			$sender->getServer()->getNameBans()->addBan($name, $reason, $until, $sender->getName());
		}else{
			$sender->getServer()->getNameBans()->addBan($name, $reason = implode(" ", $args), null, $sender->getName());
		}


		if(($player = $sender->getServer()->getPlayerExact($name)) instanceof Player){
			$player->kick($reason !== "" ? "§cSunucumuza Girmeniz Yasaklandı! Nedeni: " . $reason : "§cYönetici Tarafından Atıldınız!" . "Süre:" . date('r'), $until = "Sonsuza Kadar");
			
			$sender->getServer()->getUUIDBans()->addBan($player->getUniqueId()->toString(), $reason, null, $sender->getName());

			$mapFilePath = $sender->getServer()->getDataPath() . "banned-player-uuid-map.yml";

			$mapFileData = [];

			if(file_exists($mapFilePath)){
				$mapFileData = yaml_parse_file($mapFilePath);
			}

			$mapFileData[strtolower($name)] = $player->getUniqueId()->toString();
			yaml_emit_file($mapFilePath, $mapFileData);
		}
		
		Command::broadcastCommandMessage($sender, new TranslationContainer("%commands.ban.success", [$player !== null ? $player->getName() : $name]));

		return true;
	}
}