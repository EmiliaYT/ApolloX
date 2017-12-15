<?php

#______           _    _____           _                  
#|  _  \         | |  /  ___|         | |                 
#| | | |__ _ _ __| | _\ `--. _   _ ___| |_ ___ _ __ ___   
#| | | / _` | '__| |/ /`--. \ | | / __| __/ _ \ '_ ` _ \  
#| |/ / (_| | |  |   </\__/ / |_| \__ \ ||  __/ | | | | | 
#|___/ \__,_|_|  |_|\_\____/ \__, |___/\__\___|_| |_| |_| 
#                             __/ |                       
#                            |___/

namespace pocketmine\plugin;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\utils\Config;
use pocketmine\Server;

abstract class PluginBase implements Plugin{

	/** @var PluginLoader */
	private $loader;

	/** @var \pocketmine\Server */
	private $server;

	/** @var bool */
	private $isEnabled = false;

	/** @var bool */
	private $initialized = false;

	/** @var PluginDescription */
	private $description;

	/** @var string */
	private $dataFolder;
	private $config;
	private $configFile;
	private $file;

	/** @var PluginLogger */
	private $logger;
	
	/** @var array */
	private $jsonCommands = [];
	
	public function onLoad(){

	}

	public function onEnable(){

	}
	
	public function activate(){

	}
	
	public function onDisable(){

	}
	
	public function deactivate(){

	}
	
	public final function isEnabled(){
		return $this->isEnabled === true;
	}
	
	public final function isActivated(){
		return $this->isEnabled === true;
	}
	
	public final function setEnabled($boolean = true){
		if($this->isEnabled !== $boolean){
			$this->isEnabled = $boolean;
			if($this->isEnabled === true){
				$this->onEnable();
			}else{
				$this->onDisable();
			}
		}
	}
	
	public final function isDisabled(){
		return $this->isEnabled === false;
	}
	
	public final function isDeactivated(){
		return $this->isEnabled === false;
	}

	public final function getDataFolder(){
		return $this->dataFolder;
	}

	public final function getDescription(){
		return $this->description;
	}

	public final function init(PluginLoader $loader, Server $server, PluginDescription $description, $dataFolder, $file){
		if($this->initialized === false){
			$this->initialized = true;
			$this->loader = $loader;
			$this->server = $server;
			$this->description = $description;
			$this->dataFolder = rtrim($dataFolder, "\\/") . "/";
			$this->file = rtrim($file, "\\/") . "/";
			$this->configFile = $this->dataFolder . "config.yml";
			$this->logger = new PluginLogger($this);
			$this->initCommands();
		}
	}
	
	protected final function initCommands(){
		$jsonFilePath = "commands.json";
		if(substr($this->file, 0, 4) === "phar"){
			$phar = new \Phar($this->file);
			if(!isset($phar[$jsonFilePath]) || !($phar[$jsonFilePath] instanceof \PharFileInfo)){
				return;
			}
			
			$json = $phar[$jsonFilePath]->getContent();
		}else{
			if(!file_exists($this->file . $jsonFilePath) || ($json = file_get_contents($this->file . $jsonFilePath)) === false){
				return;
			}
		}

		if(is_null($commands = json_decode($json, true))){
			return;
		}
		
		$this->jsonCommands = $commands;
	}
	
	/**
	 * @return array
	 */
	public function getJsonCommands(){
		return $this->jsonCommands;
	}

	public function setJsonCommands($commands){
        $this->jsonCommands = $commands;
    }

	/**
	 * @return PluginLogger
	 */
	public function getLogger(){
		return $this->logger;
	}

	/**
	 * @return bool
	 */
	public final function isInitialized(){
		return $this->initialized;
	}

	/**
	 * @param string $name
	 *
	 * @return Command|PluginIdentifiableCommand
	 */
	public function getCommand($name){
		$command = $this->getServer()->getPluginCommand($name);
		if($command === null or $command->getPlugin() !== $this){
			$command = $this->getServer()->getPluginCommand(strtolower($this->description->getName()) . ":" . $name);
		}

		if($command instanceof PluginIdentifiableCommand and $command->getPlugin() === $this){
			return $command;
		}else{
			return null;
		}
	}

	/**
	 * @param CommandSender $sender
	 * @param Command       $command
	 * @param string        $label
	 * @param array         $args
	 *
	 * @return bool
	 */
	public function onCommand(CommandSender $sender, Command $command, $label, array $args){
		return false;
	}

	/**
	 * @return bool
	 */
	protected function isPhar(){
		return substr($this->file, 0, 7) === "phar://";
	}

	/**
	 * @param string $filename
	 *
	 * @return resource Resource data, or null
	 */
	public function getResource($filename){
		$filename = rtrim(str_replace("\\", "/", $filename), "/");
		if(file_exists($this->file . "resources/" . $filename)){
			return fopen($this->file . "resources/" . $filename, "rb");
		}

		return null;
	}

	/**
	 * @param string $filename
	 * @param bool   $replace
	 *
	 * @return bool
	 */
	public function saveResource($filename, $replace = false){
		if(trim($filename) === ""){
			return false;
		}

		if(($resource = $this->getResource($filename)) === null){
			return false;
		}

		$out = $this->dataFolder . $filename;
		if(!file_exists($this->dataFolder)){
			mkdir($this->dataFolder, 0755, true);
		}

		if(file_exists($out) and $replace !== true){
			return false;
		}

		$ret = stream_copy_to_stream($resource, $fp = fopen($out, "wb")) > 0;
		fclose($fp);
		fclose($resource);
		return $ret;
	}

	/**
	 * Returns all the resources incrusted on the plugin
	 *
	 * @return string[]
	 */
	public function getResources(){
		$resources = [];
		if(is_dir($this->file . "resources/")){
			foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->file . "resources/")) as $resource){
				$resources[] = $resource;
			}
		}

		return $resources;
	}

	/**
	 * @return Config
	 */
	public function getConfig(){
		if(!isset($this->config)){
			$this->reloadConfig();
		}

		return $this->config;
	}

	public function saveConfig(){
		if($this->getConfig()->save() === false){
			$this->getLogger()->critical("Could not save config to " . $this->configFile);
		}
	}

	public function saveDefaultConfig(){
		if(!file_exists($this->configFile)){
			$this->saveResource("config.yml", false);
		}
	}

	public function reloadConfig(){
		$this->config = new Config($this->configFile);
		if(($configStream = $this->getResource("config.yml")) !== null){
			$this->config->setDefaults(yaml_parse(config::fixYAMLIndexes(stream_get_contents($configStream))));
			fclose($configStream);
		}
	}

	/**
	 * @return Server
	 */
	public final function getServer(){
		return $this->server;
	}

	/**
	 * @return string
	 */
	public final function getName(){
		return $this->description->getName();
	}

	/**
	 * @return string
	 */
	public final function getFullName(){
		return $this->description->getFullName();
	}

	protected function getFile(){
		return $this->file;
	}

	/**
	 * @return PluginLoader
	 */
	public function getPluginLoader(){
		return $this->loader;
	}

}
