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
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use pocketmine\Server;

class GamemodeCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"%pocketmine.command.gamemode.description",
			"%commands.gamemode.usage",
			["gm"]
		);
		$this->setPermission("pocketmine.command.gamemode");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) === 0){
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));
			return false;
		}

		$gameMode = (int) Server::getGamemodeFromString($args[0]);

		if($gameMode === -1){
			$sender->sendMessage("§cBilinmeyen Oyun Modu!");
			return true;
		}

		$target = $sender;
		if(isset($args[1])){
			$target = $sender->getServer()->getPlayer($args[1]);
			if($target === null){
				$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.player.notFound"));
				return true;
			}
		}elseif(!($sender instanceof Player)){
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));
			return true;
		}

		if($target->setGamemode($gameMode) === false){
			$sender->sendMessage(TextFormat::RED . "Komut Uygulanırken Bilinmeyen Hata Oluştu!");
		}else{
			if($target === $sender){
				Command::broadcastCommandMessage($sender, new TranslationContainer("commands.gamemode.success.self", [Server::getGamemodeString($gameMode)]));
			}else{
				//$target->sendMessage(new TranslationContainer("gameMode.changed"));
				Command::broadcastCommandMessage($sender, new TranslationContainer("commands.gamemode.success.other", [$target->getName(), Server::getGamemodeString($gameMode)]));
			}
		}

		return true;
	}
}