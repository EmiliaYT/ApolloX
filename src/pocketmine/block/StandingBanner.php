<?php

#______           _    _____           _                  
#|  _  \         | |  /  ___|         | |                 
#| | | |__ _ _ __| | _\ `--. _   _ ___| |_ ___ _ __ ___   
#| | | / _` | '__| |/ /`--. \ | | / __| __/ _ \ '_ ` _ \  
#| |/ / (_| | |  |   </\__/ / |_| \__ \ ||  __/ | | | | | 
#|___/ \__,_|_|  |_|\_\____/ \__, |___/\__\___|_| |_| |_| 
#                             __/ |                       
#                            |___/

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\Player;
use pocketmine\tile\Tile;

class StandingBanner extends Transparent{

	protected $id = self::STANDING_BANNER;

	protected $itemId = Item::BANNER;

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function getHardness(){
		return 1;
	}

	public function isSolid(){
		return false;
	}

	public function getName(){
		return "Standing Banner";
	}

	protected function recalculateBoundingBox(){
		return null;
	}

	public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
		if($face !== Vector3::SIDE_DOWN){
			$nbt = new CompoundTag("", [
				new StringTag("id", Tile::BANNER),
				new IntTag("x", $blockReplace->x),
				new IntTag("y", $blockReplace->y),
				new IntTag("z", $blockReplace->z),
				$item->getNamedTag()->Base ?? new IntTag("Base", $item->getDamage() & 0x0f),
			]);

			if($face === Vector3::SIDE_UP){
				$this->meta = floor((($player->yaw + 180) * 16 / 360) + 0.5) & 0x0f;
				$this->getLevel()->setBlock($blockReplace, $this, true);
			}else{
				$this->meta = $face;
				$this->getLevel()->setBlock($blockReplace, new WallBanner($this->meta), true);
			}
			
			if(isset($item->getNamedTag()->Patterns) and ($item->getNamedTag()->Patterns instanceof ListTag)){
				$nbt->Patterns = $item->getNamedTag()->Patterns;
			}
			
			Tile::createTile(Tile::BANNER, $this->getLevel(), $nbt);
			
			return true;
		}

		return false;
	}

	public function onUpdate($type){
		if($type === Level::BLOCK_UPDATE_NORMAL){
			if($this->getSide(Vector3::SIDE_DOWN)->getId() === self::AIR){
				$this->getLevel()->useBreakOn($this);
				return Level::BLOCK_UPDATE_NORMAL;
			}
		}

		return false;
	}

	public function getToolType(){
		return Tool::TYPE_AXE;
	}

	public function getVariantBitmask(){
		return 0;
	}

	public function getDrops(Item $item){
		return [];
	}

	public function onBreak(Item $item, Player $player = null){
		if(($tile = $this->level->getTile($this)) !== null) {
			$this->level->dropItem($this, Item::get(Item::BANNER)->setNamedTag($tile->getCleanedNBT()));
		}
		
		return parent::onBreak($item, $player);
	}
	
}
