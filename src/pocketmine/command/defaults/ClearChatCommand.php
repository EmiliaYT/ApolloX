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
use pocketmine\command\ConsoleCommandSender;
use pocketmine\event\TranslationContainer;
use pocketmine\utils\TextFormat;
use pocketmine\Translate;
use pocketmine\Server;
use pocketmine\Player;

class ClearChatCommand extends VanillaCommand{

    public function __construct($name){
        parent::__construct(
            $name,
            "%pocketmine.command.clearchat.description",
            "%commands.clearchat.usage"
        );
        $this->setPermission("pocketmine.command.clearchat");
    }

    public function execute(CommandSender $sender, $currentAlias, array $args){
        if(!$this->testPermission($sender)){
            return true;
        }
        
        $sender->getServer()->clearChat();
        
        if(Translate::checkTurkish() === "yes"){
        	$sender->sendMessage(TextFormat::GREEN . "Sohbet Temizlendi!");
        }else{
        	$sender->sendMessage(TextFormat::GREEN . "Chat Cleared!");
        }
        
        return true;
    }
}