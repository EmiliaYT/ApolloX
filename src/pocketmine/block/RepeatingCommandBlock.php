<?php

namespace pocketmine\block;

use pocketmine\tile\CommandBlock as TileCB;

class RepeatingCommandBlock extends CommandBlock{

    protected $id = self::REPEATING_COMMAND_BLOCK;

    public function getName(){
        return "Repeating Command Block";
    }

    public function getBlockType(){
        return TileCB::REPEATING;
    }
}