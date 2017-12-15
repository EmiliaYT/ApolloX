<?php

#______           _    _____           _                  
#|  _  \         | |  /  ___|         | |                 
#| | | |__ _ _ __| | _\ `--. _   _ ___| |_ ___ _ __ ___   
#| | | / _` | '__| |/ /`--. \ | | / __| __/ _ \ '_ ` _ \  
#| |/ / (_| | |  |   </\__/ / |_| \__ \ ||  __/ | | | | | 
#|___/ \__,_|_|  |_|\_\____/ \__, |___/\__\___|_| |_| |_| 
#                             __/ |                       
#                            |___/

namespace pocketmine\inventory;

use pocketmine\entity\Human;
use pocketmine\event\entity\EntityArmorChangeEvent;
use pocketmine\event\entity\EntityInventoryChangeEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\network\protocol\ContainerSetContentPacket;
use pocketmine\network\protocol\ContainerSetSlotPacket;
use pocketmine\network\protocol\MobArmorEquipmentPacket;
use pocketmine\network\protocol\MobEquipmentPacket;
use pocketmine\network\protocol\Info;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\Server;

class PlayerInventory extends BaseInventory{
	
	const OFFHAND_ARMOR_SLOT_ID = 4;

	protected $itemInHandIndex = 0;
	protected $hotbar;
	/** @var Player|Human $holder */
	protected $holder;

	public function __construct(Human $player){
		for($i = 0; $i < $this->getHotbarSize(); $i++){
			$this->hotbar[$i] = $i;
		}
		parent::__construct($player, InventoryType::get(InventoryType::PLAYER));
	}

	public function getSize(){
		return parent::getSize() - 5;
	}

	public function setSize($size){
		parent::setSize($size + 5);
	}
	
	/**
	 * 
	 * @param int $index
	 * @return Item
	 */
	public function getHotbarSlotItem($index){
		$slot = $this->getHotbarSlotIndex($index);
		return $this->getItem($slot);
	}

	public function getHotbarSlotIndex($index){
		return ($index >= 0 && $index < $this->getHotbarSize()) ? $this->hotbar[$index] : -1;
	}

	public function setHotbarSlotIndex($index, $slot){
		if($this->holder instanceof Player && $this->holder->getInventoryType() == Player::INVENTORY_CLASSIC){
			if($index == $slot || $slot < 0){
				return;
			}
			$tmp = $this->getItem($index);
			$this->setItem($index, $this->getItem($slot));
			$this->setItem($slot, $tmp);
		} else {
			if($index >= 0 && $index < $this->getHotbarSize() && $slot >= -1 && $slot < $this->getSize()){
				$this->hotbar[$index] = $slot;
			}
		}
	}

	public function getHeldItemIndex(){
		return $this->itemInHandIndex;
	}
	
	/**
	 * @param int $index
	 */
	public function justSetHeldItemIndex($index){
		if($index >= 0 && $index < $this->getHotbarSize()){
			$this->itemInHandIndex = $index;
		}
	}

	public function setHeldItemIndex($index, $isNeedSendToHolder = true){
		if($index >= 0 && $index < $this->getHotbarSize()){
			$this->itemInHandIndex = $index;
			if($isNeedSendToHolder && $this->getHolder() instanceof Player){
				$this->sendHeldItem($this->getHolder());
				$this->sendContents($this->getHolder());
			}
			$this->sendHeldItem($this->getHolder()->getViewers());
			$this->sendContents($this->getHolder()->getViewers());
		}
	}

	public function getItemInHand(){
		$item = $this->getItem($this->getHeldItemSlot());
		if($item instanceof Item){
			return $item;
		}else{
			return clone $this->air;
		}
	}

	/**
	 * @param Item $item
	 *
	 * @return bool
	 */
	public function setItemInHand(Item $item){
		return $this->setItem($this->getHeldItemSlot(), $item);
	}

	public function getHeldItemSlot(){
		return $this->getHotbarSlotIndex($this->itemInHandIndex);
	}

	public function setHeldItemSlot($slot){
		if($slot >= -1 && $slot < $this->getSize()){
			$item = $this->getItem($slot);
			$itemIndex = $this->getHeldItemIndex();
			if($this->getHolder() instanceof Player){
				Server::getInstance()->getPluginManager()->callEvent($ev = new PlayerItemHeldEvent($this->getHolder(), $item, $slot, $itemIndex));
				if($ev->isCancelled()){
					$this->sendContents($this->getHolder());
					return;
				}
			}
			$this->setHotbarSlotIndex($itemIndex, $slot);
		}
	}

	/**
	 * @param Player|Player[] $target
	 */
	public function sendHeldItem($target){
		$item = $this->getItemInHand();
		$pk = new MobEquipmentPacket();
		$pk->eid = $this->getHolder()->getId();
		$pk->item = $item;
		$pk->slot = $this->getHeldItemSlot();
		$pk->selectedSlot = $this->getHeldItemIndex();
		$level = $this->getHolder()->getLevel();
		if(!is_array($target)){
			if($level->mayAddPlayerHandItem($this->getHolder(), $target)){
				$target->dataPacket($pk);
				if($target === $this->getHolder()){
					$this->sendSlot($this->getHeldItemSlot(), $target);
				}
			}
		}else{
			foreach($target as $player){
				if($level->mayAddPlayerHandItem($this->getHolder(), $player)){
					$player->dataPacket($pk);
					if($player === $this->getHolder()){
						$this->sendSlot($this->getHeldItemSlot(), $player);
					}
				}
			}
		}
	}

	public function onSlotChange($index, $before, $sendPacket){
		if($sendPacket){
			$holder = $this->getHolder();
			if(!$holder instanceof Player or !$holder->spawned){
				return false;
			}
			parent::onSlotChange($index, $before, $sendPacket);
		}
		$this->setHeldItemIndex($index);
		if($index === $this->itemInHandIndex){
			$this->sendHeldItem($this->getHolder()->getViewers());
			if($sendPacket){
				$this->sendHeldItem($this->getHolder());
			}
		}elseif($index >= $this->getSize()){
			$this->sendArmorSlot($index, $this->getViewers());
			$this->sendArmorSlot($index, $this->getHolder()->getViewers());
		}
		return true;
	}

	public function getHotbarSize(){
		return 9;
	}

	public function getArmorItem($index){
		return $this->getItem($this->getSize() + $index);
	}

	public function setArmorItem($index, Item $item, $sendPacket = true){
		return $this->setItem($this->getSize() + $index, $item, $sendPacket);
	}

	public function getHelmet(){
		return $this->getItem($this->getSize());
	}

	public function getChestplate(){
		return $this->getItem($this->getSize() + 1);
	}

	public function getLeggings(){
		return $this->getItem($this->getSize() + 2);
	}

	public function getBoots(){
		return $this->getItem($this->getSize() + 3);
	}

	public function setHelmet(Item $helmet){
		return $this->setItem($this->getSize(), $helmet);
	}

	public function setChestplate(Item $chestplate){
		return $this->setItem($this->getSize() + 1, $chestplate);
	}

	public function setLeggings(Item $leggings){
		return $this->setItem($this->getSize() + 2, $leggings);
	}

	public function setBoots(Item $boots){
		return $this->setItem($this->getSize() + 3, $boots);
	}

	public function setItem($index, Item $item, $sendPacket = true){
		if($index < 0 || $index >= $this->size){
			return false;
		}elseif($item->getId() === 0 || $item->getCount() <= 0){
			return $this->clear($index);
		}
		if($index >= $this->getSize()){
			Server::getInstance()->getPluginManager()->callEvent($ev = new EntityArmorChangeEvent($this->getHolder(), $this->getItem($index), $item, $index));
			if($ev->isCancelled() && $this->getHolder() instanceof Human){
				$this->sendArmorSlot($index, $this->getHolder());
				return false;
			}
			$item = $ev->getNewItem();
		}else{
			Server::getInstance()->getPluginManager()->callEvent($ev = new EntityInventoryChangeEvent($this->getHolder(), $this->getItem($index), $item, $index));
			if($ev->isCancelled()){
				$this->sendSlot($index, $this->getHolder());
				return false;
			}
			$index = $ev->getSlot();
			$item = $ev->getNewItem();
		}
		$old = $this->getItem($index);
		$this->slots[$index] = clone $item;
		$this->onSlotChange($index, $old, $sendPacket);
		return true;
	}

	public function clear($index, $sendPacket = true){
		if(isset($this->slots[$index])){
			$item = clone $this->air;
			$old = $this->slots[$index];
			if($index >= $this->getSize() && $index < $this->size){
				Server::getInstance()->getPluginManager()->callEvent($ev = new EntityArmorChangeEvent($this->getHolder(), $old, $item, $index));
				if($ev->isCancelled()){
					if($index >= $this->size){
						$this->sendArmorSlot($index, $this->getHolder());
					}else{
						$this->sendSlot($index, $this->getHolder());
					}
					return false;
				}
				$item = $ev->getNewItem();
			}else{
				Server::getInstance()->getPluginManager()->callEvent($ev = new EntityInventoryChangeEvent($this->getHolder(), $old, $item, $index));
				if($ev->isCancelled()){
					if($index >= $this->size){
						$this->sendArmorSlot($index, $this->getHolder());
					}else{
						$this->sendSlot($index, $this->getHolder());
					}
					return false;
				}
				$item = $ev->getNewItem();
			}
			if($item->getId() !== Item::AIR){
				$this->slots[$index] = clone $item;
				//$this->sendContents($this);
				//$this->sendContents($this->getViewers());
			}else{
				unset($this->slots[$index]);
			}
			$this->onSlotChange($index, $old, $sendPacket);
		}
		return true;
	}

	/**
	 * @return Item[]
	 */
	public function getArmorContents(){
		$armor = [];
		for($i = 0; $i < 4; ++$i){
			$armor[$i] = $this->getItem($this->getSize() + $i);
		}
		return $armor;
	}

	public function clearAll(){
		$limit = $this->getSize() + 5;
		for($index = 0; $index < $limit; ++$index){
			$this->clear($index);
		}
		$this->hotbar = range(0, $this->getHotbarSize() - 1, 1);
		//$this->sendContents($this);
		$this->sendContents($this->getViewers());
		//$this->sendArmorContents($this);
		$this->sendArmorContents($this->getViewers());
	}

	/**
	 * @param Player|Player[] $target
	 */
	public function sendArmorContents($target){
		if($target instanceof Player){
			$target = [$target];
		}
		$armor = $this->getArmorContents();
		$pk = new MobArmorEquipmentPacket();
		$pk->eid = $this->getHolder()->getId();
		$pk->slots = $armor;
		foreach($target as $player){
			if($player === $this->getHolder()){
				$pk2 = new ContainerSetContentPacket();
				$pk2->eid = $this->getHolder()->getId();
				$pk2->windowid = ContainerSetContentPacket::SPECIAL_ARMOR;
				$pk2->slots = $armor;
				$player->dataPacket($pk2);
			}else{
				$player->dataPacket($pk);
			}
		}
		$this->sendOffHandContents($target);
	}
	
	private function sendOffHandContents($target){
		$pk = new MobEquipmentPacket();
		$pk->eid = $this->getHolder()->getId();
		$pk->item = $this->getItem($this->getSize() + self::OFFHAND_ARMOR_SLOT_ID);
		$pk->slot = $this->getHeldItemSlot();
		$pk->selectedSlot = $this->getHeldItemIndex();
		$pk->windowId = MobEquipmentPacket::WINDOW_ID_PLAYER_OFFHAND;
		/** @var Player $player */
		foreach($target as $player){
			if($player->getPlayerProtocol() >= Info::PROTOCOL_110){
				if($player === $this->getHolder()){
					$pk2 = new ContainerSetSlotPacket();
					$pk2->windowid = ContainerSetContentPacket::SPECIAL_OFFHAND;
					$pk2->slot = 0;
					$pk2->item = $this->getItem($this->getSize() + self::OFFHAND_ARMOR_SLOT_ID);
					$player->dataPacket($pk2);
				}else{
					$player->dataPacket($pk);
				}
			}
		}
	}

	/**
	 * @param Item[] $items
	 */
	public function setArmorContents(array $items, $sendPacket = true){
		for($i = 0; $i < 4; ++$i){
			if(!isset($items[$i]) || !($items[$i] instanceof Item)){
				$items[$i] = clone $this->air;
			}
			if($items[$i]->getId() === Item::AIR){
				$this->clear($this->getSize() + $i);
			}else{
				$this->setItem($this->getSize() + $i, $items[$i], $sendPacket);
			}
		}
	}
	
	/**
	 * @param int             $index
	 * @param Player|Player[] $target
	 */
	public function sendArmorSlot($index, $target){
		if($target instanceof Player){
			$target = [$target];
		}
		if($index - $this->getSize() == self::OFFHAND_ARMOR_SLOT_ID){
			$this->sendOffHandContents($target);
			return;
		}
		$armor = $this->getArmorContents();
		$pk = new MobArmorEquipmentPacket();
		$pk->eid = $this->getHolder()->getId();
		$pk->slots = $armor;
		foreach($target as $player){
			if($player === $this->getHolder()){
				$pk2 = new ContainerSetSlotPacket();
				$pk2->windowid = ContainerSetContentPacket::SPECIAL_ARMOR;
				$pk2->slot = $index - $this->getSize();
				$pk2->item = $this->getItem($index);
				$player->dataPacket($pk2);
			}else{
				$player->dataPacket($pk);
			}
		}
	}
	
	/**
	 * @param Player|Player[] $target
	 */
	public function sendContents($target){
		$pk = new ContainerSetContentPacket();
		$pk->eid = $this->getHolder()->getId();
		$pk->windowid = ContainerSetContentPacket::SPECIAL_INVENTORY;
		$pk->slots = [];
		for($i = 0; $i < $this->getSize(); ++$i){
			$pk->slots[$i] = $this->getItem($i);
		}
		for($i = $this->getSize(); $i < $this->getSize() + 9; ++$i){
			$pk->slots[$i] = clone $this->air;
		}
		$pk->hotbar = [];
		for($i = 0; $i < $this->getHotbarSize(); ++$i){
			$index = $this->getHotbarSlotIndex($i);
			$pk->hotbar[] = $index <= -1 ? -1 : $index + 9;
		}
		$this->getHolder()->dataPacket($pk);
	}

	/**
	 * @param int             $index
	 * @param Player|Player[] $target
	 */
	public function sendSlot($index, $target){
		if(!$target instanceof Player){
			return;
		}
		
		$pk = new ContainerSetSlotPacket();
		$pk->slot = $index;
		$pk->item = clone $this->getItem($index);
		$pk->windowid = ContainerSetContentPacket::SPECIAL_INVENTORY;
		$this->getHolder()->dataPacket($pk);
	}
	
	public function removeItemWithCheckOffHand(Item $searchItem){
		$offhandSlotId = $this->getSize() + self::OFFHAND_ARMOR_SLOT_ID;
		$item = $this->getItem($offhandSlotId);
		if($item->getId() !== Item::AIR && $item->getCount() > 0){
			if($searchItem->equals($item, $searchItem->getDamage() === null ? false : true, $searchItem->getCompound() === null ? false : true)){
				$amount = min($item->getCount(), $searchItem->getCount());
				$searchItem->setCount($searchItem->getCount() - $amount);
				$item->setCount($item->getCount() - $amount);
				$this->setItem($offhandSlotId, $item);
				return;
			}
		}
		parent::removeItem($searchItem);
	}

    /**
     * @return Player|Human
     */
    public function getHolder(){
        return $this->holder;
    }
}
