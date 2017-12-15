<?php

#______           _    _____           _                  
#|  _  \         | |  /  ___|         | |                 
#| | | |__ _ _ __| | _\ `--. _   _ ___| |_ ___ _ __ ___   
#| | | / _` | '__| |/ /`--. \ | | / __| __/ _ \ '_ ` _ \  
#| |/ / (_| | |  |   </\__/ / |_| \__ \ ||  __/ | | | | | 
#|___/ \__,_|_|  |_|\_\____/ \__, |___/\__\___|_| |_| |_| 
#                             __/ |                       
#                            |___/

namespace pocketmine\network\raknet\server;

use pocketmine\network\raknet\Binary;
use pocketmine\network\raknet\protocol\ACK;
use pocketmine\network\raknet\protocol\ADVERTISE_SYSTEM;
use pocketmine\network\raknet\protocol\DATA_PACKET_0;
use pocketmine\network\raknet\protocol\DATA_PACKET_1;
use pocketmine\network\raknet\protocol\DATA_PACKET_2;
use pocketmine\network\raknet\protocol\DATA_PACKET_3;
use pocketmine\network\raknet\protocol\DATA_PACKET_4;
use pocketmine\network\raknet\protocol\DATA_PACKET_5;
use pocketmine\network\raknet\protocol\DATA_PACKET_6;
use pocketmine\network\raknet\protocol\DATA_PACKET_7;
use pocketmine\network\raknet\protocol\DATA_PACKET_8;
use pocketmine\network\raknet\protocol\DATA_PACKET_9;
use pocketmine\network\raknet\protocol\DATA_PACKET_A;
use pocketmine\network\raknet\protocol\DATA_PACKET_B;
use pocketmine\network\raknet\protocol\DATA_PACKET_C;
use pocketmine\network\raknet\protocol\DATA_PACKET_D;
use pocketmine\network\raknet\protocol\DATA_PACKET_E;
use pocketmine\network\raknet\protocol\DATA_PACKET_F;
use pocketmine\network\raknet\protocol\EncapsulatedPacket;
use pocketmine\network\raknet\protocol\NACK;
use pocketmine\network\raknet\protocol\OPEN_CONNECTION_REPLY_1;
use pocketmine\network\raknet\protocol\OPEN_CONNECTION_REPLY_2;
use pocketmine\network\raknet\protocol\OPEN_CONNECTION_REQUEST_1;
use pocketmine\network\raknet\protocol\OPEN_CONNECTION_REQUEST_2;
use pocketmine\network\raknet\protocol\Packet;
use pocketmine\network\raknet\protocol\UNCONNECTED_PING;
use pocketmine\network\raknet\protocol\UNCONNECTED_PING_OPEN_CONNECTIONS;
use pocketmine\network\raknet\protocol\UNCONNECTED_PONG;
use pocketmine\network\raknet\RakNet;
use pocketmine\utils\BinaryStream;

class SessionManager{
	
    protected $packetPool = [];
    
    protected $server;

    protected $socket;

    protected $receiveBytes = 0;
    protected $sendBytes = 0;
    
    protected $sessions = [];

    protected $name = "";

    protected $packetLimit = 1000;

    protected $shutdown = false;

    protected $ticks = 0;
    protected $lastMeasure;

    protected $block = [];
    protected $ipSec = [];

    public $portChecking = true;
    
    private $spamPacket;
    
    public function __construct(RakNetServer $server, UDPServerSocket $socket){
        $this->server = $server;
        $this->socket = $socket;
        $this->registerPackets();
        
        $this->serverId = mt_rand(0, PHP_INT_MAX);
        
        $this->spamPacket = hex2bin("210400");
        
        $this->run();
    }

    public function getPort(){
        return $this->server->getPort();
    }

    public function getLogger(){
        return $this->server->getLogger();
    }

    public function run(){
        $this->tickProcessor();
    }

    private function tickProcessor(){
        $this->lastMeasure = microtime(true);
        while(!$this->shutdown){
            $start = microtime(true);
            $max = 10000;
            while(--$max and $this->receivePacket());
	        while($this->receiveStream());
			$time = microtime(true) - $start;
			if($time < 0.025){
				@time_sleep_until(microtime(true) + 0.025 - $time);
			}
			$this->tick();
        }
    }

	private function tick(){
		$time = microtime(true);
		foreach($this->sessions as $session){
			$session->update($time);
			if(($this->ticks % 70) === 0){ //40
				$this->streamPing($session);
			}
		}
		foreach($this->ipSec as $address => $count){
			if($count >= $this->packetLimit){
				$this->blockAddress($address);
			}
		}
		$this->ipSec = [];
		if(($this->ticks & 0b1111) === 0){
			$diff = max(0.005, $time - $this->lastMeasure);
			$this->streamOption("bandwidth", serialize([
				"up" => $this->sendBytes / $diff,
				"down" => $this->receiveBytes / $diff
			]));
			$this->lastMeasure = $time;
			$this->sendBytes = 0;
			$this->receiveBytes = 0;
			if(count($this->block) > 0){
				asort($this->block);
				$now = microtime(true);
				foreach($this->block as $address => $timeout){
					if($timeout <= $now){
						unset($this->block[$address]);
					}else{
						break;
					}
				}
			}
		}
		++$this->ticks;
	}
	
    private function receivePacket(){
        $len = $this->socket->readPacket($buffer, $source, $port);
        if($buffer !== null){
            $this->receiveBytes += $len;
            if(isset($this->block[$source])){
                return true;
            }
            if(isset($this->ipSec[$source])){
                $this->ipSec[$source]++;
            }else{
                $this->ipSec[$source] = 1;
            }
            if($len > 0){
                $pid = ord($buffer{0});        
                if($pid === UNCONNECTED_PING::$ID){
                    $packet = new UNCONNECTED_PING();
                    $packet->buffer = $buffer;
                    $packet->decode();
                    $pk = new UNCONNECTED_PONG();
                    $pk->serverID = $this->getID();
                    $pk->pingID = $packet->pingID;
                    $pk->serverName = $this->getName();
                    //$pk->serverName = $this->getName() . ";" . $packet->GUID;
                    //$pk->serverName = $this->getName() . ";" . $this->serverId;
                    $this->sendPacket($pk, $source, $port);
                }elseif($pid === UNCONNECTED_PONG::$ID){
                }elseif(($packet = $this->getPacketFromPool($pid)) !== null){
                    $packet->buffer = $buffer;
                    $this->getSession($source, $port)->handlePacket($packet);
                }else{
                    $this->streamRaw($source, $port, $buffer);
                }
            }
            return true;
        }
        return false;
    }

    public function sendPacket(Packet $packet, $dest, $port){
        $packet->encode();
		$this->sendBytes += $this->socket->writePacket($packet->buffer, $dest, $port);    
    }

    /*public function streamEncapsulated(Session $session, EncapsulatedPacket $packet, $flags = RakNet::PRIORITY_NORMAL){
		$id = $session->getAddress() . ":" . $session->getPort();
		$buffer = chr(RakNet::PACKET_ENCAPSULATED) . chr(strlen($id)) . $id . chr($flags) . $packet->toBinary(true);
		$this->server->pushThreadToMainPacket($buffer);
    }*/
    
    public function streamEncapsulated(Session $session, EncapsulatedPacket $packet, $flags = RakNet::PRIORITY_NORMAL){
		$id = $session->getAddress() . ":" . $session->getPort();
		if(ord($packet->buffer{0}) == 0xfe){
			$buff = substr($packet->buffer, 1);
			if(ord($buff{0}) == 0x78){
				$decoded = zlib_decode($buff);
				$stream = new BinaryStream($decoded);
				$length = strlen($decoded);
				while($stream->getOffset() < $length){
					$buf = $stream->getString();
					/*if($buf == $this->spamPacket){
						continue;
					}*/
					$buffer = chr(RakNet::PACKET_ENCAPSULATED) . chr(strlen($id)) . $id . $buf;
					$this->server->pushThreadToMainPacket($buffer);
				}
			}else{
				$buffer = chr(RakNet::PACKET_ENCAPSULATED) . chr(strlen($id)) . $id . $buff;
				$this->server->pushThreadToMainPacket($buffer);
			}
		}
	}
    
	public function streamPing(Session $session){
        $id = $session->getAddress() . ":" . $session->getPort();
		$ping = $session->getPing();
        $buffer = chr(RakNet::PACKET_PING) . chr(strlen($id)) . $id .  chr(strlen($ping)) . $ping;
        $this->server->pushThreadToMainPacket($buffer);
    }

    public function streamRaw($address, $port, $payload){
        $buffer = chr(RakNet::PACKET_RAW) . chr(strlen($address)) . $address . Binary::writeShort($port) . $payload;
        $this->server->pushThreadToMainPacket($buffer);
    }

    protected function streamClose($identifier, $reason){
        $buffer = chr(RakNet::PACKET_CLOSE_SESSION) . chr(strlen($identifier)) . $identifier . chr(strlen($reason)) . $reason;
        $this->server->pushThreadToMainPacket($buffer);
    }

    protected function streamInvalid($identifier){
        $buffer = chr(RakNet::PACKET_INVALID_SESSION) . chr(strlen($identifier)) . $identifier;
        $this->server->pushThreadToMainPacket($buffer);
    }

    protected function streamOpen(Session $session){
        $identifier = $session->getAddress() . ":" . $session->getPort();
        $buffer = chr(RakNet::PACKET_OPEN_SESSION) . chr(strlen($identifier)) . $identifier . chr(strlen($session->getAddress())) . $session->getAddress() . Binary::writeShort($session->getPort()) . Binary::writeLong($session->getID());
        $this->server->pushThreadToMainPacket($buffer);
    }
    
    protected function streamOption($name, $value){
        $buffer = chr(RakNet::PACKET_SET_OPTION) . chr(strlen($name)) . $name . $value;
        $this->server->pushThreadToMainPacket($buffer);
    }

    private function checkSessions(){
        if(count($this->sessions) > 4096){
            foreach($this->sessions as $i => $s){
                if($s->isTemporal()){
                    unset($this->sessions[$i]);
                    if(count($this->sessions) <= 4096){
                        break;
                    }
                }
            }
        }
    }

    public function receiveStream(){
        if(strlen($packet = $this->server->readMainToThreadPacket()) > 0){
            $id = ord($packet{0});
            $offset = 1;
            if($id === RakNet::PACKET_ENCAPSULATED){
                $len = ord($packet{$offset++});
                $identifier = substr($packet, $offset, $len);
                $offset += $len;
                if(isset($this->sessions[$identifier])){
                    $flags = ord($packet{$offset++});
                    $buffer = substr($packet, $offset);
                    $this->sessions[$identifier]->addEncapsulatedToQueue(EncapsulatedPacket::fromBinary($buffer, true), $flags);
                }else{
                    $this->streamInvalid($identifier);
                }
            }elseif($id === RakNet::PACKET_RAW){
                $len = ord($packet{$offset++});
                $address = substr($packet, $offset, $len);
                $offset += $len;
                $port = Binary::readShort(substr($packet, $offset, 2));
                $offset += 2;
                $payload = substr($packet, $offset);
                $this->socket->writePacket($payload, $address, $port);
            }elseif($id === RakNet::PACKET_CLOSE_SESSION){
                $len = ord($packet{$offset++});
                $identifier = substr($packet, $offset, $len);
                if(isset($this->sessions[$identifier])){
                    $this->removeSession($this->sessions[$identifier]);
                }else{
                    $this->streamInvalid($identifier);
                }
            }elseif($id === RakNet::PACKET_INVALID_SESSION){
                $len = ord($packet{$offset++});
                $identifier = substr($packet, $offset, $len);
                if(isset($this->sessions[$identifier])){
                    $this->removeSession($this->sessions[$identifier]);
                }
            }elseif($id === RakNet::PACKET_SET_OPTION){
                $len = ord($packet{$offset++});
                $name = substr($packet, $offset, $len);
                $offset += $len;
                $value = substr($packet, $offset);
                switch($name){
                    case "name":
                        $this->name = $value;
                        break;
                    case "portChecking":
                        $this->portChecking = (bool) $value;
                        break;
                    case "packetLimit":
                        $this->packetLimit = (int) $value;
                        break;
                }
            }elseif($id === RakNet::PACKET_BLOCK_ADDRESS){
                $len = ord($packet{$offset++});
                $address = substr($packet, $offset, $len);
                $offset += $len;
                $timeout = Binary::readInt(substr($packet, $offset, 4));
                $this->blockAddress($address, $timeout);
            }elseif($id === RakNet::PACKET_SHUTDOWN){
                foreach($this->sessions as $session){
                    $this->removeSession($session);
                }
                $this->socket->close();
                $this->shutdown = true;
            }elseif($id === RakNet::PACKET_EMERGENCY_SHUTDOWN){
                $this->shutdown = true;
            }else{
	            return false;
            }
            return true;
        }
        return false;
    }

    public function blockAddress($address, $timeout = 300){
        $final = microtime(true) + $timeout;
        if(!isset($this->block[$address]) or $timeout === -1){
            if($timeout === -1){
                $final = PHP_INT_MAX;
            }else{
                $this->getLogger()->notice("Blocked $address for $timeout seconds"); //TODO: Translate
            }
            $this->block[$address] = $final;
        }elseif($this->block[$address] < $final){
            $this->block[$address] = $final;
        }
    }

    /**
     * @param string $ip
     * @param int    $port
     *
     * @return Session
     */
    public function getSession($ip, $port){
        $id = $ip . ":" . $port;
        if(!isset($this->sessions[$id])){
            $this->checkSessions();
            $this->sessions[$id] = new Session($this, $ip, $port);
        }

        return $this->sessions[$id];
    }

    public function removeSession(Session $session, $reason = "Unknown"){
        $id = $session->getAddress() . ":" . $session->getPort();
        if(isset($this->sessions[$id])){
            $this->sessions[$id]->close();
            unset($this->sessions[$id]);
            $this->streamClose($id, $reason);
        }
    }

    public function openSession(Session $session){
        $this->streamOpen($session);
    }
    
    public function getName(){
        return $this->name;
    }

    public function getID(){
        return $this->serverId;
    }

	private function registerPacket($id, $class){
		$this->packetPool[$id] = new $class;
	}

	/**
	 * @param $id
	 *
	 * @return Packet
	 */
	public function getPacketFromPool($id){
		if(isset($this->packetPool[$id])){
			return clone $this->packetPool[$id];
		}

		return null;
	}

    private function registerPackets(){
        //$this->registerPacket(UNCONNECTED_PING::$ID, UNCONNECTED_PING::class);
        $this->registerPacket(UNCONNECTED_PING_OPEN_CONNECTIONS::$ID, UNCONNECTED_PING_OPEN_CONNECTIONS::class);
        $this->registerPacket(OPEN_CONNECTION_REQUEST_1::$ID, OPEN_CONNECTION_REQUEST_1::class);
        $this->registerPacket(OPEN_CONNECTION_REPLY_1::$ID, OPEN_CONNECTION_REPLY_1::class);
        $this->registerPacket(OPEN_CONNECTION_REQUEST_2::$ID, OPEN_CONNECTION_REQUEST_2::class);
        $this->registerPacket(OPEN_CONNECTION_REPLY_2::$ID, OPEN_CONNECTION_REPLY_2::class);
        $this->registerPacket(UNCONNECTED_PONG::$ID, UNCONNECTED_PONG::class);
        $this->registerPacket(ADVERTISE_SYSTEM::$ID, ADVERTISE_SYSTEM::class);
        $this->registerPacket(DATA_PACKET_0::$ID, DATA_PACKET_0::class);
        $this->registerPacket(DATA_PACKET_1::$ID, DATA_PACKET_1::class);
        $this->registerPacket(DATA_PACKET_2::$ID, DATA_PACKET_2::class);
        $this->registerPacket(DATA_PACKET_3::$ID, DATA_PACKET_3::class);
        $this->registerPacket(DATA_PACKET_4::$ID, DATA_PACKET_4::class);
        $this->registerPacket(DATA_PACKET_5::$ID, DATA_PACKET_5::class);
        $this->registerPacket(DATA_PACKET_6::$ID, DATA_PACKET_6::class);
        $this->registerPacket(DATA_PACKET_7::$ID, DATA_PACKET_7::class);
        $this->registerPacket(DATA_PACKET_8::$ID, DATA_PACKET_8::class);
        $this->registerPacket(DATA_PACKET_9::$ID, DATA_PACKET_9::class);
        $this->registerPacket(DATA_PACKET_A::$ID, DATA_PACKET_A::class);
        $this->registerPacket(DATA_PACKET_B::$ID, DATA_PACKET_B::class);
        $this->registerPacket(DATA_PACKET_C::$ID, DATA_PACKET_C::class);
        $this->registerPacket(DATA_PACKET_D::$ID, DATA_PACKET_D::class);
        $this->registerPacket(DATA_PACKET_E::$ID, DATA_PACKET_E::class);
        $this->registerPacket(DATA_PACKET_F::$ID, DATA_PACKET_F::class);
        $this->registerPacket(NACK::$ID, NACK::class);
        $this->registerPacket(ACK::$ID, ACK::class);
    }
    
}
