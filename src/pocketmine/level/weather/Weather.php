<?php

#______           _    _____           _                  
#|  _  \         | |  /  ___|         | |                 
#| | | |__ _ _ __| | _\ `--. _   _ ___| |_ ___ _ __ ___   
#| | | / _` | '__| |/ /`--. \ | | / __| __/ _ \ '_ ` _ \  
#| |/ / (_| | |  |   </\__/ / |_| \__ \ ||  __/ | | | | | 
#|___/ \__,_|_|  |_|\_\____/ \__, |___/\__\___|_| |_| |_| 
#                             __/ |                       
#                            |___/

namespace pocketmine\level\weather;

use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\event\level\WeatherChangeEvent;
use pocketmine\network\protocol\LevelEventPacket;
use pocketmine\Player;

class Weather extends WeatherLevels{
	
	private $level;
	private $weatherNow = 0;
	private $strength1;
	private $strength2;
	private $duration;
	private $canCalculate = true;
	
	private $temporalVector = null;

	private $lastUpdate = 0;

	private $randomWeatherData = [0, 1, 0, 1, 0, 1, 0, 2, 0, 3];

	public function __construct(Level $level, $duration = 1200){
		$this->level = $level;
		$this->weatherNow = self::SUNNY;
		$this->duration = $duration;
		$this->lastUpdate = $level->getServer()->getTick();
		$this->temporalVector = new Vector3(0, 0, 0);
	}

	public function canCalculate(){
		return $this->canCalculate;
	}

	public function setCanCalculate($canCalc){
		$this->canCalculate = $canCalc;
	}

	public function calcWeather($currentTick){
		if($this->canCalculate()){
			$tickDiff = $currentTick - $this->lastUpdate;
			$this->duration -= $tickDiff;
			
			if($this->duration <= 0){
				$duration = mt_rand(
					min($this->level->getServer()->weatherRandomDurationMin, $this->level->getServer()->weatherRandomDurationMax), 
					max($this->level->getServer()->weatherRandomDurationMin, $this->level->getServer()->weatherRandomDurationMax)
				);
				if($this->weatherNow === self::SUNNY){ 
					$weather = $this->randomWeatherData[array_rand($this->randomWeatherData)];
					$this->setWeather($weather, $duration);
				}else{
					$weather = self::SUNNY;
					$this->setWeather($weather, $duration);
				}
			}
			
			if(($this->weatherNow >= self::RAINY_THUNDER) and ($this->level->getServer()->lightningTime > 0) and is_int($this->duration / $this->level->getServer()->lightningTime)){
				$players = $this->level->getPlayers();
				if(count($players) > 0){
					$p = $players[array_rand($players)];
					$x = $p->x + mt_rand(-64, 64);
					$z = $p->z + mt_rand(-64, 64);
					$y = $this->level->getHighestBlockAt($x, $z);
					$this->level->spawnLightning($this->temporalVector->setComponents($x, $y, $z));
				}
			}
		}
		
		$this->lastUpdate = $currentTick;
	}

	public function setWeather($wea, $duration = 12000){
		$this->level->getServer()->getPluginManager()->callEvent($ev = new WeatherChangeEvent($this->level, $wea, $duration));
		if(!$ev->isCancelled()){
			$this->weatherNow = $ev->getWeather();
			$this->strength1 = mt_rand(90000, 110000);
			$this->strength2 = mt_rand(30000, 40000);
			$this->duration = $ev->getDuration();
			$this->sendWeatherToAll();
		}
	}

	public function getRandomWeatherData(){
		return $this->randomWeatherData;
	}

	public function setRandomWeatherData($randomWeatherData){
		$this->randomWeatherData = $randomWeatherData;
	}

	public function getWeather(){
		return $this->weatherNow;
	}

	public static function getWeatherFromString($weather){
		if(is_int($weather)){
			if($weather <= 3){
				return $weather;
			}
			
			return self::SUNNY;
		}
		
		switch(strtolower($weather)){
			case "clear":
			case "sunny":
			case "fine":
				return self::SUNNY;
			case "rain":
			case "rainy":
				return self::RAINY;
			case "thunder":
				return self::THUNDER;
			case "rain_thunder":
			case "rainy_thunder":
			case "storm":
				return self::RAINY_THUNDER;
				default;
				return self::SUNNY;
		}
	}

	/**
	 * @return bool
	 */
	public function isSunny(){
		return $this->getWeather() === self::SUNNY;
	}

	/**
	 * @return bool
	 */
	public function isRainy(){
		return $this->getWeather() === self::RAINY;
	}

	/**
	 * @return bool
	 */
	public function isRainyThunder(){
		return $this->getWeather() === self::RAINY_THUNDER;
	}

	/**
	 * @return bool
	 */
	public function isThunder(){
		return $this->getWeather() === self::THUNDER;
	}

	public function getStrength(){
		return [$this->strength1, $this->strength2];
	}

	public function sendWeather(Player $p){
		$pks = [
			new LevelEventPacket(),
			new LevelEventPacket()
		];
		
		$pks[0]->evid = LevelEventPacket::EVENT_STOP_RAIN;
		$pks[0]->data = $this->strength1;	
		$pks[1]->evid = LevelEventPacket::EVENT_STOP_THUNDER;
		$pks[1]->data = $this->strength2;
		
		switch($this->weatherNow){
			case self::RAIN:
				$pks[0]->evid = LevelEventPacket::EVENT_START_RAIN;
				$pks[0]->data = $this->strength1;
				break;
			case self::RAINY_THUNDER:
				$pks[0]->evid = LevelEventPacket::EVENT_START_RAIN;
				$pks[0]->data = $this->strength1;
				$pks[1]->evid = LevelEventPacket::EVENT_START_THUNDER;
				$pks[1]->data = $this->strength2;
				break;
			case self::THUNDER:
				$pks[1]->evid = LevelEventPacket::EVENT_START_THUNDER;
				$pks[1]->data = $this->strength2;
				break;
			default: break;
		}
		
		foreach($pks as $pk){
			$p->dataPacket($pk);
		}
		
		$p->weatherData = [$this->weatherNow, $this->strength1, $this->strength2];
	}

	public function sendWeatherToAll(){
		foreach($this->level->getPlayers() as $player){
			$this->sendWeather($player);
		}
	}
}
