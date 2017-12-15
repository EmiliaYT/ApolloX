<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

namespace pocketmine\network\protocol;

class EventPacket extends PEPacket{
	
	const NETWORK_ID = Info::EVENT_PACKET;
	const PACKET_NAME = "EVENT_PACKET";
	
	const TYPE_ACHIEVEMENT_AWARDED = 0;
	const TYPE_ENTITY_INTERACT = 1;
	const TYPE_PORTAL_BUILT = 2;
	const TYPE_PORTAL_USED = 3;
	const TYPE_MOB_KILLED = 4;
	const TYPE_CAULDRON_USED = 5;
	const TYPE_PLAYER_DEATH = 6;
	const TYPE_BOSS_KILLED = 7;
	const TYPE_AGENT_COMMAND = 8;
	const TYPE_AGENT_CREATED = 9;

	public $eid;
	public $eventData;
	public $type;

	public function decode(){
		$this->getHeader($playerProtocol);
		$this->eid = $this->getVarInt();
		$this->eventData = $this->getVarInt();
		$this->type = $this->getByte();
	}

	public function encode(){
		$this->putVarInt($this->eid);
		$this->putVarInt($this->eventData);
		$this->putByte($this->type);
	}
	
}
