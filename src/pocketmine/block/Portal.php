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
use pocketmine\Player;
use pocketmine\math\Vector3;

class Portal extends Transparent
{
    protected $id = self::PORTAL;
    
    private $temporalVector = null;

    public function __construct()
    {
        if ($this->temporalVector === null) {
            $this->temporalVector = new Vector3(0, 0, 0);
        }
    }

    public function getName()
    {
        return "Portal";
    }

    public function getHardness()
    {
        return -1;
    }

    public function getResistance()
    {
        return 0;
    }

    public function getToolType()
    {
        return Tool::TYPE_PICKAXE;
    }

    public function canPassThrough()
    {
        return true;
    }

    public function hasEntityCollision()
    {
        return true;
    }

    public function onBreak(Item $item)
    {
        $block = $this;
        if ($this->getLevel()->getBlock($this->temporalVector->setComponents($block->x - 1, $block->y, $block->z))->getId() == Block::PORTAL or
            $this->getLevel()->getBlock($this->temporalVector->setComponents($block->x + 1, $block->y, $block->z))->getId() == Block::PORTAL
        ) {
            for ($x = $block->x; $this->getLevel()->getBlock($this->temporalVector->setComponents($x, $block->y, $block->z))->getId() == Block::PORTAL; $x++) {
                for ($y = $block->y; $this->getLevel()->getBlock($this->temporalVector->setComponents($x, $y, $block->z))->getId() == Block::PORTAL; $y++) {
                    $this->getLevel()->setBlock($this->temporalVector->setComponents($x, $y, $block->z), new Air());
                }
                for ($y = $block->y - 1; $this->getLevel()->getBlock($this->temporalVector->setComponents($x, $y, $block->z))->getId() == Block::PORTAL; $y--) {
                    $this->getLevel()->setBlock($this->temporalVector->setComponents($x, $y, $block->z), new Air());
                }
            }
            for ($x = $block->x - 1; $this->getLevel()->getBlock($this->temporalVector->setComponents($x, $block->y, $block->z))->getId() == Block::PORTAL; $x--) {
                for ($y = $block->y; $this->getLevel()->getBlock($this->temporalVector->setComponents($x, $y, $block->z))->getId() == Block::PORTAL; $y++) {
                    $this->getLevel()->setBlock($this->temporalVector->setComponents($x, $y, $block->z), new Air());
                }
                for ($y = $block->y - 1; $this->getLevel()->getBlock($this->temporalVector->setComponents($x, $y, $block->z))->getId() == Block::PORTAL; $y--) {
                    $this->getLevel()->setBlock($this->temporalVector->setComponents($x, $y, $block->z), new Air());
                }
            }
        } else {
            for ($z = $block->z; $this->getLevel()->getBlock($this->temporalVector->setComponents($block->x, $block->y, $z))->getId() == Block::PORTAL; $z++) {
                for ($y = $block->y; $this->getLevel()->getBlock($this->temporalVector->setComponents($block->x, $y, $z))->getId() == Block::PORTAL; $y++) {
                    $this->getLevel()->setBlock($this->temporalVector->setComponents($block->x, $y, $z), new Air());
                }
                for ($y = $block->y - 1; $this->getLevel()->getBlock($this->temporalVector->setComponents($block->x, $y, $z))->getId() == Block::PORTAL; $y--) {
                    $this->getLevel()->setBlock($this->temporalVector->setComponents($block->x, $y, $z), new Air());
                }
            }
            for ($z = $block->z - 1; $this->getLevel()->getBlock($this->temporalVector->setComponents($block->x, $block->y, $z))->getId() == Block::PORTAL; $z--) {
                for ($y = $block->y; $this->getLevel()->getBlock($this->temporalVector->setComponents($block->x, $y, $z))->getId() == Block::PORTAL; $y++) {
                    $this->getLevel()->setBlock($this->temporalVector->setComponents($block->x, $y, $z), new Air());
                }
                for ($y = $block->y - 1; $this->getLevel()->getBlock($this->temporalVector->setComponents($block->x, $y, $z))->getId() == Block::PORTAL; $y--) {
                    $this->getLevel()->setBlock($this->temporalVector->setComponents($block->x, $y, $z), new Air());
                }
            }
        }
        parent::onBreak($item);
    }

    public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null)
    {
        if ($player instanceof Player) {
            $this->meta = $player->getDirection() & 0x01;
        }
        $this->getLevel()->setBlock($block, $this, true, true);

        return true;
    }

    public function getDrops(Item $item)
    {
        return [];
    }
}