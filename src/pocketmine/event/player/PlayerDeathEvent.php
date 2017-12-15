<?php

namespace pocketmine\event\player;

use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\TextContainer;
use pocketmine\item\Item;
use pocketmine\Player;

class PlayerDeathEvent extends EntityDeathEvent{

	public static $handlerList = null;

	/** @var TextContainer|string */
	private $deathMessage;
	private $keepInventory = false;
	private $keepExperience = false;

	/**
	 * @param Player               $entity
	 * @param Item[]               $drops
	 * @param string|TextContainer $deathMessage
	 */
	public function __construct(Player $entity, array $drops, $deathMessage){
		parent::__construct($entity, $drops);
		
		$this->deathMessage = $deathMessage;
	}

	/**
	 * @return \pocketmine\entity\Entity|Player
     */
	public function getEntity(){
		return $this->entity;
	}

	/**
	 * @return \pocketmine\entity\Entity|Player
     */
	public function getPlayer(){
		return $this->entity;
	}

	/**
	 * @return TextContainer|string
	 */
	public function getDeathMessage(){
		return $this->deathMessage;
	}

	/**
	 * @param string|TextContainer $deathMessage
	 */
	public function setDeathMessage($deathMessage){
		$this->deathMessage = $deathMessage;
	}

	public function getKeepInventory(){
		return $this->keepInventory;
	}

	public function setKeepInventory($keepInventory){
		$this->keepInventory = $keepInventory;
	}

	public function getKeepExperience(){
		return $this->keepExperience;
	}

	public function setKeepExperience($keepExperience){
		$this->keepExperience = $keepExperience;
	}
	
}