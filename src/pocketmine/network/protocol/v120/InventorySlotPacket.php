<?php

namespace pocketmine\network\protocol\v120;

use pocketmine\network\protocol\Info120;
use pocketmine\network\protocol\PEPacket;

class InventorySlotPacket extends PEPacket{
	
	const NETWORK_ID = Info120::INVENTORY_SLOT_PACKET;
	const PACKET_NAME = "INVENTORY_SLOT_PACKET";
	
	public $containerId;
	public $slot;
	public $item = null;
	
	public function decode($playerProtocol){
		
	}

	public function encode($playerProtocol){
		$this->reset($playerProtocol);
		$this->putVarInt($this->containerId);
		$this->putVarInt($this->slot);
		if($this->item == null){
			$this->putSignedVarInt(0);
		} else {
			$this->putSlot($this->item, $playerProtocol);
		}
	}
}
