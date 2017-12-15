<?php

#______           _    _____           _                  
#|  _  \         | |  /  ___|         | |                 
#| | | |__ _ _ __| | _\ `--. _   _ ___| |_ ___ _ __ ___   
#| | | / _` | '__| |/ /`--. \ | | / __| __/ _ \ '_ ` _ \  
#| |/ / (_| | |  |   </\__/ / |_| \__ \ ||  __/ | | | | | 
#|___/ \__,_|_|  |_|\_\____/ \__, |___/\__\___|_| |_| |_| 
#                             __/ |                       
#                            |___/

namespace pocketmine\tile;

use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\NBT;

class ItemFrame extends Spawnable{
	
	public $map_uuid = -1;
	
	public function __construct(Level $level, CompoundTag $nbt){
		if(!isset($nbt->ItemRotation)){
			$nbt->ItemRotation = new ByteTag("ItemRotation", 0);
		}

		if(!isset($nbt->ItemDropChance)){
			$nbt->ItemDropChance = new FloatTag("ItemDropChance", 1.0);
		}

		parent::__construct($level, $nbt);
	}

	public function hasItem(){
		return $this->getItem()->getId() !== Item::AIR;
	}

	public function getItem(){
		if(isset($this->namedtag->Item)){
			return NBT::getItemHelper($this->namedtag->Item);
		}else{
			return Item::get(Item::AIR);
		}
	}

	public function setItem(Item $item = null){
		if($item !== null and $item->getId() !== Item::AIR){
			$this->namedtag->Item = NBT::putItemHelper(-1, "Item");
			//$this->namedtag->Item = $item->nbtSerialize(-1, "Item");
		}else{
			unset($this->namedtag->Item);
		}
		
		$this->onChanged();
	}
	
	public function setMapID($mapId){
		$this->map_uuid = $mapId;
		$this->namedtag->Map_UUID = new StringTag("map_uuid", $mapId);
		$this->onChanged();
	}
	
	public function getMapID(){
		return $this->map_uuid;
	}
	
	public function getItemRotation(){
		return $this->namedtag->ItemRotation->getValue();
	}

	public function setItemRotation($rotation){
		$this->namedtag->ItemRotation->setValue($rotation);
		$this->onChanged();
	}

	public function getItemDropChance(){
		return $this->namedtag->ItemDropChance->getValue();
	}

	public function setItemDropChance($chance){
		$this->namedtag->ItemDropChance->setValue($chance);
		$this->onChanged();
	}
	
	public function addAdditionalSpawnData(CompoundTag $nbt){
		$nbt->ItemDropChance = $this->namedtag->ItemDropChance;
		$nbt->ItemRotation = $this->namedtag->ItemRotation;
		if($this->hasItem()){
			$nbt->Item = $this->namedtag->Item;
		}
	}
	
	public function getSpawnCompound(){
		$tag = new CompoundTag("", [
			new StringTag("id", Tile::ITEM_FRAME),
			new IntTag("x", (int) $this->x),
			new IntTag("y", (int) $this->y),
			new IntTag("z", (int) $this->z),
			$this->namedtag->ItemDropChance,
			$this->namedtag->ItemRotation,
		]);
		
		if($this->hasItem()){
			$tag->Item = $this->namedtag->Item;
			if($this->getItem()->getId() === Item::FILLED_MAP){
				if(isset($this->namedtag->Map_UUID)){
					$tag->Map_UUID = $this->namedtag->Map_UUID;
				}
			}
		}
		
		return $tag;
	}
	
}
