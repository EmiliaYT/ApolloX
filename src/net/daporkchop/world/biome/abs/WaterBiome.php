<?php

namespace net\daporkchop\world\biome\abs;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\level\biome\NormalBiome;

abstract class WaterBiome extends NormalBiome    {
    public function __construct()   {
        $this->setGroundCover([
            BlockFactory::get(Block::DIRT, 0),
            BlockFactory::get(Block::DIRT, 0),
            BlockFactory::get(Block::DIRT, 0),
            BlockFactory::get(Block::DIRT, 0),
        ]);
    }
}
