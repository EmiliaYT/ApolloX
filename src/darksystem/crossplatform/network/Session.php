<?php

namespace darksystem\crossplatform\network;

use pocketmine\utils\TextFormat;
use darksystem\crossplatform\network\protocol\Login\LoginDisconnectPacket;
use darksystem\crossplatform\network\protocol\Status\PingPacket;
use darksystem\crossplatform\utils\AES;
use darksystem\crossplatform\utils\Binary;

class Session{
	
	/** @var ServerManager */
	private $manager;
	/** @var int */
	private $identifier;
	/** @var resource */
	private $socket;
	/** @var int */
	private $status = 0;
	/** @var string */
	protected $address;
	/** @var int */
	protected $port;
	/** @var AES */
	protected $aes;
	/** @var bool */
	protected $encryptionEnabled = false;

	/** @var ?int */
	private $threshold = null;

	/**
	 * @param ServerManager $manager
	 * @param int           $identifier
	 * @param resource      $socket
	 */
	public function __construct(ServerManager $manager, $identifier, $socket){
		$this->manager = $manager;
		$this->identifier = $identifier;
		$this->socket = $socket;
		$addr = stream_socket_get_name($this->socket, true);
		$final = strrpos($addr, ":");
		$this->port = (int) substr($addr, $final + 1);
		$this->address = substr($addr, 0, $final);
	}

	/**
	 * @param int $threshold
	 */
	public function setCompression($threshold){
		$this->writeRaw(Binary::writeComputerVarInt(0x03) . Binary::writeComputerVarInt($threshold >= 0 ? $threshold : -1));
		$this->threshold = $threshold === -1 ? null : $threshold;
	}

	/**
	 * @param string $data
	 */
	public function write($data){
		if($this->encryptionEnabled){
			@fwrite($this->socket, $this->aes->encrypt($data));
		}else{
			@fwrite($this->socket, $data);
		}
	}

	/**
	 * @param int $len
	 * @return string data read from socket
	 */
	public function read($len){
		if($this->encryptionEnabled){
			$data = @fread($this->socket, $len);
			if(strlen($data) > 0){
				return $this->aes->decrypt($data);
			}else{
				return $data;
			}
		}else{
			return @fread($this->socket, $len);
		}
	}

	/**
	 * @return string address
	 */
	public function getAddress(){
		return $this->address;
	}

	/**
	 * @return int port
	 */
	public function getPort(){
		return $this->port;
	}

	/**
	 * @param string $secret
	 */
	public function enableEncryption($secret){
		$this->aes = new AES();
		$this->aes->enableContinuousBuffer();
		$this->aes->setKey($secret);
		$this->aes->setIV($secret);

		$this->encryptionEnabled = true;
	}

	/**
	 * @param Packet $packet
	 */
	public function writePacket(Packet $packet){
		$data = $packet->write();
		if($this->threshold === null){
			$this->write(Binary::writeComputerVarInt(strlen($data)) . $data);
		}else{
			$dataLength = strlen($data);
			if($dataLength >= $this->threshold){
				$data = zlib_encode($data, ZLIB_ENCODING_DEFLATE, 7);
			}else{
				$dataLength = 0;
			}

			$data = Binary::writeComputerVarInt($dataLength) . $data;
			$this->write(Binary::writeComputerVarInt(strlen($data)) . $data);
		}
	}

	/**
	 * @param string $data
	 */
	public function writeRaw($data){
		if($this->threshold === null){
			$this->write(Binary::writeComputerVarInt(strlen($data)) . $data);
		}else{
			$dataLength = strlen($data);
			if($dataLength >= $this->threshold){
				$data = zlib_encode($data, ZLIB_ENCODING_DEFLATE, 7);
			}else{
				$dataLength = 0;
			}

			$data = Binary::writeComputerVarInt($dataLength) . $data;
			$this->write(Binary::writeComputerVarInt(strlen($data)) . $data);
		}
	}

	public function process(){
		$length = Binary::readVarIntSession($this);
		if($length === false or $this->status === -1){
			$this->close("Connection closed");
			return;
		}elseif($length <= 0 or $length > 131070){
			$this->close("Invalid length");
			return;
		}

		$offset = 0;

		$buffer = $this->read($length);

		if($this->threshold !== null){
			$dataLength = Binary::readComputerVarInt($buffer, $offset);
			if($dataLength !== 0){
				if($dataLength < $this->threshold){
					$this->close("Invalid compression threshold");
				}else{
					$buffer = zlib_decode(substr($buffer, $offset));
					$offset = 0;
				}
			}else{
				$buffer = substr($buffer, $offset);
				$offset = 0;
			}
		}

		if($this->status === 2){ //Login
			$this->manager->sendPacket($this->identifier, $buffer);
		}elseif($this->status === 1){
			$pid = Binary::readComputerVarInt($buffer, $offset);
			if($pid === 0x00){
				$sample = [];
				foreach($this->manager->sample as $id => $name){
					$sample[] = [
						"name" => $name,
						"id" => $id
					];
				}
				$data = [
					"version" => [
						"name" => ServerManager::VERSION,
						"protocol" => ServerManager::PROTOCOL
					],
					"players" => [
						"max" => $this->manager->getServerData()["MaxPlayers"],
						"online" => $this->manager->getServerData()["OnlinePlayers"],
						"sample" => $sample,
					],
					"description" => json_decode(TextFormat::toJSON($this->manager->description))
				];
				if($this->manager->favicon !== null){
					$data["favicon"] = $this->manager->favicon;
				}
				$data = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

				$data = Binary::writeComputerVarInt(0x00) . Binary::writeComputerVarInt(strlen($data)) . $data;
				$this->writeRaw($data);
			}elseif($pid === 0x01){
				$packet = new PingPacket();
				$packet->read($buffer, $offset);
				$this->writePacket($packet);
				$this->status = -1;
			}
		}elseif($this->status === 0){
			$pid = Binary::readComputerVarInt($buffer, $offset);
			if($pid === 0x00){
				$protocol = Binary::readComputerVarInt($buffer, $offset);
				$len = Binary::readComputerVarInt($buffer, $offset);
				$hostname = substr($buffer, $offset, $len);
				$offset += $len;
				$serverPort = Binary::readShort(substr($buffer, $offset, 2));
				$offset += 2;
				$nextState = Binary::readComputerVarInt($buffer, $offset);

				if($nextState === 1){
					$this->status = 1;
				}elseif($nextState === 2){
					$this->status = -1;
					if($protocol < ServerManager::PROTOCOL){
						$packet = new LoginDisconnectPacket();
						$packet->reason = json_encode(["translate" => "multiplayer.disconnect.outdated_client", "with" => [["text" => ServerManager::VERSION]]]);
						$this->writePacket($packet);
					}elseif($protocol > ServerManager::PROTOCOL){
						$packet = new LoginDisconnectPacket();
						$packet->reason = json_encode(["translate" => "multiplayer.disconnect.outdated_server", "with" => [["text" => ServerManager::VERSION]]]);
						$this->writePacket($packet);
					}else{
						$this->manager->openSession($this);
						$this->status = 2;
					}
				}else{
					$this->close();
				}
			}else{
				$this->close("Unexpected packet $pid");
			}
		}
	}

	/**
	 * @return int identifier
	 */
	public function getID(){
		return $this->identifier;
	}

	/**
	 * @param string $reason
	 */
	public function close($reason = ""){
		$this->manager->close($this);
	}
}
