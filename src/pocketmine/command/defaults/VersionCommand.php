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

use pocketmine\command\CommandSender;
use pocketmine\event\TranslationContainer;
use pocketmine\network\protocol\Info as ProtocolInfo;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\Plugin;
use pocketmine\Translate;

class VersionCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"%pocketmine.command.version.description",
			"%pocketmine.command.version.usage"
		);
		$this->setPermission("pocketmine.command.version");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) === 0){
			$ver = \pocketmine\VERSION;
			$name = \pocketmine\CODENAME;
			$company = $name . "-PE";
			if(Translate::checkTurkish() === "yes"){
				$sender->sendMessage("§eBu Sunucu §a$name $ver §eVersiyonunda Çalışıyor\n§eAPI: §a3.0.1\n§e$name'i İndirmek İçin:\n§ahttps://github.com/$company/$name §r");
			}else{
				$sender->sendMessage("§eThis Server is Running on §a$name $ver\n§eAPI: §a3.0.1\n§eTo Download $codename:\n§ahttps://github.com/$company/$name §r");
			}
		}else{
			$pluginName = implode(" ", $args);
			$exactPlugin = $sender->getServer()->getPluginManager()->getPlugin($pluginName);

			if($exactPlugin instanceof Plugin){
				$this->describeToSender($exactPlugin, $sender);
				return true;
			}

			$found = false;
			$pluginName = strtolower($pluginName);
			foreach($sender->getServer()->getPluginManager()->getPlugins() as $plugin){
				if(strpos($plugin->getName(), $pluginName) !== false){
					$this->describeToSender($plugin, $sender);
					$found = true;
				}
			}

			if(!$found){
				$sender->sendMessage(new TranslationContainer("pocketmine.command.version.noSuchPlugin"));
			}
		}

		return true;
	}

	private function describeToSender(Plugin $plugin, CommandSender $sender){
		$desc = $plugin->getDescription();
		$sender->sendMessage(TextFormat::DARK_GREEN . $desc->getName() . TextFormat::WHITE . " Sürüm " . TextFormat::DARK_GREEN . $desc->getVersion());

		if($desc->getDescription() != null){
			$sender->sendMessage($desc->getDescription());
		}

		if($desc->getWebsite() != null){
			if(Translate::checkTurkish() === "yes"){
				$sender->sendMessage("Site: " . $desc->getWebsite());
			}else{
				$sender->sendMessage("Website: " . $desc->getWebsite());
			}
		}

		if(count($authors = $desc->getAuthors()) > 0){
			if(count($authors) === 1){
				if(Translate::checkTurkish() === "yes"){
					$sender->sendMessage("Geliştirici: " . implode(", ", $authors));
				}else{
					$sender->sendMessage("Author: " . implode(", ", $authors));
				}
			}else{
				if(Translate::checkTurkish() === "yes"){
					$sender->sendMessage("Geliştiriciler: " . implode(", ", $authors));
				}else{
					$sender->sendMessage("Authors: " . implode(", ", $authors));
				}
			}
		}
	}
}