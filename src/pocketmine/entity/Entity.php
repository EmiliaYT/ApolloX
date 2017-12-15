<?php

#______           _    _____           _                  
#|  _  \         | |  /  ___|         | |                 
#| | | |__ _ _ __| | _\ `--. _   _ ___| |_ ___ _ __ ___   
#| | | / _` | '__| |/ /`--. \ | | / __| __/ _ \ '_ ` _ \  
#| |/ / (_| | |  |   </\__/ / |_| \__ \ ||  __/ | | | | | 
#|___/ \__,_|_|  |_|\_\____/ \__, |___/\__\___|_| |_| |_| 
#                             __/ |                       
#                            |___/

namespace pocketmine\entity;

use pocketmine\block\Block;
use pocketmine\block\Water;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDespawnEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Timings;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Tool;

use pocketmine\level\Level;
use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Math;
use pocketmine\math\Vector3;
use darksystem\metadata\Metadatable;
use darksystem\metadata\MetadataValue;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\protocol\MobEffectPacket;
use pocketmine\network\protocol\RemoveEntityPacket;
use pocketmine\network\protocol\SetEntityDataPacket;
use pocketmine\network\protocol\SetEntityLinkPacket;
use pocketmine\network\protocol\SetTimePacket;
use pocketmine\plugin\Plugin;
use pocketmine\Player;
use pocketmine\utils\ChunkException;
use pocketmine\block\Liquid;
use pocketmine\block\Cobweb;
use pocketmine\block\Fire;
use pocketmine\block\Ladder;
use pocketmine\block\Vine;

abstract class Entity extends Location implements Metadatable, EntityIds{
	
	const NETWORK_ID = -1;

	const SOUTH = 0, DIRECTION_SOUTH = 0;
	const WEST = 1, DIRECTION_WEST = 1;
	const NORTH = 2, DIRECTION_NORTH = 2;
	const EAST = 3, DIRECTION_EAST = 3;
	
	const DATA_TYPE_BYTE = 0;
	const DATA_TYPE_SHORT = 1;
	const DATA_TYPE_INT = 2;
	const DATA_TYPE_FLOAT = 3;
	const DATA_TYPE_STRING = 4;
	const DATA_TYPE_SLOT = 5;
	const DATA_TYPE_POS = 6;
	const DATA_TYPE_LONG = 7;
	const DATA_TYPE_VECTOR3 = 8;

	const DATA_FLAGS = 0;
	const DATA_VARIANT = 2, DATA_ANIMAL_VARIANT = 2; // type: int
	const DATA_COLOR = 3, DATA_COLOUR = 3; //byte
	const DATA_NAMETAG = 4; // type: string
	const DATA_OWNER_EID = 5; //long
	const DATA_AIR = 7; //short
	const DATA_POTION_COLOR = 8; // type: int data: rgb
	const DATA_POTION_AMBIENT = 9;
	const DATA_JUMP_DURATION = 10; // type: long
	const DATA_HURT_TIME = 11; //int
	const DATA_HURT_DIRECTION = 12; //int
	const DATA_PADDLE_TIME_LEFT = 13; //float
	const DATA_PADDLE_TIME_RIGHT = 14; //float
	const DATA_EXPERIENCE_VALUE = 15; //int
	const DATA_HORSE_FLAGS = 16; // type: int
	const DATA_MINECART_DISPLAY_OFFSET = 17; //int
	const DATA_MINECART_HAS_DISPLAY = 18; //byte
	const DATA_HORSE_TYPE = 19; // type: byte
	const DATA_ENDERMAN_BLOCK_ID = 23, DATA_ENDERMAN_HELD_ITEM_ID = 23; // type: short
	const DATA_ENDERMAN_BLOCK_META = 24, DATA_ENDERMAN_HELD_ITEM_DAMAGE = 24; // type:short
	const DATA_ENTITY_AGE = 25; //short
	const DATA_PLAYER_FLAGS = 27;
	const DATA_PLAYER_BED_POSITION = 29;
	
	const DATA_FIREBALL_POWER_X = 30;
	const DATA_FIREBALL_POWER_Y = 31;
	const DATA_FIREBALL_POWER_Z = 32;
	
	const DATA_POTION_AUX_VALUE = 37; //short
	const DATA_LEAD_HOLDER = 38, DATA_LEAD_HOLDER_EID = 38;
	const DATA_SCALE = 39; // type: float
	const DATA_BUTTON_TEXT = 40, DATA_INTERACTIVE_TAG = 40;
	const DATA_NPC_SKIN_ID = 41; //string
	const DATA_URL_TAG = 42; //string
	const DATA_MAX_AIR = 43; // type: short
	const DATA_MARK_VARIANT = 44; //int
	const DATA_BLOCK_TARGET = 48;
	const DATA_WITHER_INVULNERABLE_TICKS = 49; //int
	const DATA_WITHER_TARGET_1 = 50; //long
	const DATA_WITHER_TARGET_2 = 51; //long
	const DATA_WITHER_TARGET_3 = 52; //long
	const DATA_BOUNDING_BOX_WIDTH = 54; //float
	const DATA_BOUNDING_BOX_HEIGHT = 55; //float
	const DATA_EXPLODE_TIMER = 56, DATA_FUSE_LENGTH = 56; //int
	const DATA_RIDER_SEAT_POSITION = 57; //vector3f
	const DATA_RIDER_ROTATION_LOCKED = 58; //byte
	const DATA_RIDER_MAX_ROTATION = 59; //float
	const DATA_RIDER_MIN_ROTATION = 60; //float
	const DATA_AREA_EFFECT_CLOUD_RADIUS = 61; //float
	const DATA_AREA_EFFECT_CLOUD_WAITING = 62; //int
	const DATA_AREA_EFFECT_CLOUD_PARTICLE_ID = 63; //int
	const DATA_SHULKER_ATTACH_FACE = 65; //byte
	const DATA_SHULKER_ATTACH_POS = 67;
	const DATA_TRADING_PLAYER_EID = 68; //long
	
	const DATA_COMMAND_BLOCK_COMMAND = 71; //string
	const DATA_COMMAND_BLOCK_LAST_OUTPUT = 72; //string
	const DATA_COMMAND_BLOCK_TRACK_OUTPUT = 73; //byte
	const DATA_CONTROLLING_RIDER_SEAT_NUMBER = 74; //byte
	const DATA_STRENGTH = 75; //int
	const DATA_MAX_STRENGTH = 76; //int
	
	const DATA_SILENT = 4;
	const DATA_LEAD = 24;
	
	const DATA_NO_AI = 231321;
	
	const DATA_FLAG_ONFIRE = 0;
	const DATA_FLAG_SNEAKING = 1;
	const DATA_FLAG_RIDING = 2;
	const DATA_FLAG_SPRINTING = 3;
	const DATA_FLAG_ACTION = 4;
	const DATA_FLAG_INVISIBLE = 5;
	const DATA_FLAG_TEMPTED = 6;
	const DATA_FLAG_INLOVE = 7;
	const DATA_FLAG_SADDLE = 8, DATA_FLAG_SADDLED = 8;
	const DATA_FLAG_POWERED = 9;
	const DATA_FLAG_IGNITED = 10;
	const DATA_FLAG_BABY = 11, DATA_FLAG_IS_BABY = 11;
	const DATA_FLAG_CONVERTING = 12;
	const DATA_FLAG_CRITICAL = 13;
	const DATA_FLAG_SHOW_NAMETAG = 14, DATA_FLAG_CAN_SHOW_NAMETAG = 14;
	const DATA_FLAG_ALWAYS_SHOW_NAMETAG = 15;
	const DATA_FLAG_IMMOBILE = 16, DATA_FLAG_NO_AI = 16, DATA_FLAG_NOT_MOVE = 16;
	const DATA_FLAG_SILENT = 17;
	const DATA_FLAG_WALLCLIMBING = 18, DATA_FLAG_IS_CLIMBING = 18, DATA_FLAG_CAN_CLIMBING = 18;
	const DATA_FLAG_CAN_CLIMB = 19;
	const DATA_FLAG_SWIMMER = 20;
	const DATA_FLAG_CAN_FLY = 21;
	const DATA_FLAG_RESTING = 22, DATA_FLAG_RESTING_BAT = 22;
	const DATA_FLAG_SITTING = 23, DATA_FLAG_ANIMAL_SIT = 23;
	const DATA_FLAG_ANGRY = 24, DATA_FLAG_ANGRY_WOLF = 24;
	const DATA_FLAG_INTERESTED = 25;
	const DATA_FLAG_CHARGED = 26, DATA_FLAG_ANGRY_BLAZE = 26;
	const DATA_FLAG_TAMED = 27, DATA_FLAG_TAME_WOLF = 27;
	const DATA_FLAG_LEASHED = 28;
	const DATA_FLAG_SHEARED = 29, DATA_FLAG_SHAVED_SHIP = 29;
	const DATA_FLAG_GLIDING = 30, DATA_FLAG_FALL_FLYING = 30;
	const DATA_FLAG_ELDER = 31, DATA_FLAG_ELDER_GUARDIAN = 31;
	const DATA_FLAG_MOVING = 32;
	const DATA_FLAG_NOT_IN_WATER = 33, DATA_FLAG_BREATHING = 33;
	const DATA_FLAG_CHESTED = 34, DATA_FLAG_CHESTED_MOUNT = 34;
	const DATA_FLAG_STACKABLE = 35;
	const DATA_FLAG_SHOWBASE = 36;
	const DATA_FLAG_REARING = 37, DATA_FLAG_IS_STAING = 37;
	const DATA_FLAG_VIBRATING = 38;
	const DATA_FLAG_IDLING = 39;
	const DATA_FLAG_EVOKER_SPELL = 40;
	const DATA_FLAG_CHARGE_ATTACK = 41;
	const DATA_FLAG_WASD_CONTROLLED = 42, DATA_FLAG_IS_WASD_CONTROLLED = 42;
	const DATA_FLAG_CAN_POWER_JUMP = 43;
	const DATA_FLAG_LINGER = 44; //45
	const DATA_FLAG_HAS_COLLISION = 45;
	const DATA_FLAG_AFFECTED_BY_GRAVITY = 46;
	const DATA_FLAG_FIRE_IMMUNE = 47;
	const DATA_FLAG_DANCING = 48;
	
	const DATA_PLAYER_FLAG_SLEEP = 1;
	const DATA_PLAYER_FLAG_DEAD = 2;
	
	public static $entityCount = 2;
	
	private static $knownEntities = [];
	private static $shortNames = [];

	/** @var Player[] */
	protected $hasSpawned = [];
	
	protected $effects = [];

	protected $id;

	private $temporalVector;

	protected $dataFlags = 0;
	protected $dataProperties = [	
		Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, 0],
		Entity::DATA_AIR => [Entity::DATA_TYPE_SHORT, 300],
		Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, ""],
		Entity::DATA_LEAD_HOLDER => [Entity::DATA_TYPE_LONG, -1],
		Entity::DATA_MAX_AIR => [Entity::DATA_TYPE_SHORT, 300],
		Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 1],
	];
	
	public $passenger = null;
	public $vehicle = null;
	
	public $chunkX;
	
	public $chunkZ;
	
	public $chunk;

	private $isPlayer;

	protected $lastDamageCause = null;

	public $lastX = null;
	public $lastY = null;
	public $lastZ = null;

	public $motionX;
	public $motionY;
	public $motionZ;
	public $lastMotionX;
	public $lastMotionY;
	public $lastMotionZ;

	public $lastYaw;
	public $lastPitch;
	public $boundingBox;
	public $onGround;
	public $inBlock = false;
	public $positionChanged;
	public $motionChanged;
	public $dead;
	public $deadTicks = 0;
	
	protected $age = 0;

	public $height;

	public $eyeHeight = null;

	public $width;
	public $length;
	
	private $health = 20;
	private $maxHealth = 20;

	protected $ySize = 0;
	protected $stepHeight = 0;
	
	public $keepMovement = false;

	public $fallDistance = 0;
	public $ticksLived = 0;
	public $lastUpdate;
	public $maxFireTicks;
	public $fireTicks;
	public $airTicks;
	public $namedtag;
	public $canCollide = true;

	protected $isStatic = false;

	public $isCollided = false;
	public $isCollidedHorizontally = false;
	public $isCollidedVertically = false;

	public $noDamageTicks;
	
	protected $justCreated;
	protected $fireProof;
	
	private $invulnerable;
	
	protected $attributeMap;
	
	protected $gravity;
	protected $drag;
	
	protected $server;

	public $closed = false;
	
	protected $timings;
	
	protected $linkedEntity = null;
	
	protected $linkedType = null;
	
	protected $fireDamage = 1;
	
	public function __construct(Level $level, CompoundTag $nbt){
		if($level === null || $level->getProvider() === null){
			throw new ChunkException("Invalid garbage Chunk/Level given to Entity");
		}
		
		$this->timings = Timings::getEntityTimings($this);

		$this->isPlayer = $this instanceof Player;

		$this->temporalVector = new Vector3();

		if($this->eyeHeight === null){
			$this->eyeHeight = $this->height / 2 + 0.1;
		}

		$this->id = Entity::$entityCount++;
		$this->justCreated = true;
		$this->namedtag = $nbt;
		
		$this->chunk = $level->getChunk($this->namedtag["Pos"][0] >> 4, $this->namedtag["Pos"][2] >> 4, true);
		assert($this->chunk !== null);
		$this->setLevel($level);
		$this->server = $level->getServer();
		$this->server->addSpawnedEntity($this);

		$this->boundingBox = new AxisAlignedBB(0, 0, 0, 0, 0, 0);
		
		if(isset($this->namedtag->Motion)){
			$this->setMotion($this->temporalVector->setComponents($this->namedtag["Motion"][0], $this->namedtag["Motion"][1], $this->namedtag["Motion"][2]));
		}else{
			$this->setMotion($this->temporalVector->setComponents(0, 0, 0));
		}
		
		$this->motionX = $this->namedtag["Motion"][0];
		$this->motionY = $this->namedtag["Motion"][1];
		$this->motionZ = $this->namedtag["Motion"][2];
		
		if(!isset($this->namedtag->FallDistance)){
			$this->namedtag->FallDistance = new FloatTag("FallDistance", 0);
		}
		
		$this->fallDistance = $this->namedtag["FallDistance"];

		if(!isset($this->namedtag->Fire)){
			$this->namedtag->Fire = new ShortTag("Fire", 0);
		}
		
		$this->fireTicks = $this->namedtag["Fire"];

		if(!isset($this->namedtag->Air)){
			$this->namedtag->Air = new ShortTag("Air", 300);
		}
		
		$this->dataProperties[Entity::DATA_AIR] = [Entity::DATA_TYPE_SHORT, 300];
		$this->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_NOT_IN_WATER, true);
		$this->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_SHOW_NAMETAG, true);
		$this->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG, true);
		//$this->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_CAN_CLIMBING, true);
		//$this->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_CAN_FLY, true);
		
		if(!isset($this->namedtag->OnGround)){
			$this->namedtag->OnGround = new ByteTag("OnGround", 0);
		}
		
		$this->onGround = $this->namedtag["OnGround"] > 0 ? true : false;

		if(!isset($this->namedtag->Invulnerable)){
			$this->namedtag->Invulnerable = new ByteTag("Invulnerable", 0);
		}
		
		$this->invulnerable = $this->namedtag["Invulnerable"] > 0 ? true : false;

		$this->attributeMap = new AttributeMap();
		
		$this->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_AFFECTED_BY_GRAVITY, true);
		
		$this->chunk->addEntity($this);
		$level->addEntity($this);
		$this->initEntity();
		$this->lastUpdate = $this->server->getTick();
		$this->server->getPluginManager()->callEvent(new EntitySpawnEvent($this));

		$this->scheduleUpdate();
	}
	
	/**
	 * @return mixed
	 */
	public function getHeight(){
		return $this->height;
	}

	/**
	 * @return mixed
	 */
	public function getWidth(){
		return $this->width;
	}

	/**
	 * @return mixed
	 */
	public function getLength(){
		return $this->length;
	}
	
	/**
	 * @param $scale
	 */
	public function setScale($scale){
		$this->setDataProperty(Entity::DATA_SCALE, Entity::DATA_TYPE_FLOAT, $scale);
	}

	/**
	 * @return mixed
	 */
	public function getScale(){
		return $this->getDataProperty(Entity::DATA_SCALE, Entity::DATA_TYPE_FLOAT);
	}
	
	/**
	 * @return string
	 */
	public function getNameTag(){
		return $this->getDataProperty(Entity::DATA_NAMETAG);
	}

	/**
	 * @return bool
	 */
	public function isNameTagVisible(){
		return $this->getDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_SHOW_NAMETAG);
	}
	
	/**
	 * @return bool
	 */
	public function isNameTagAlwaysVisible(){
		return $this->getDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG);
	}
	
	/**
	 * @param string $name
	 */
	public function setNameTag($name){
		$this->setDataProperty(Entity::DATA_NAMETAG, Entity::DATA_TYPE_STRING, $name);
	}

	/**
	 * @param bool $value
	 */
	public function setNameTagVisible($value = true){
		$this->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_SHOW_NAMETAG, (bool) $value);
	}

	/**
	 * @param bool $value
	 */
	public function setNameTagAlwaysVisible($value = true){
		$this->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG, (bool) $value);
	}
	
	public function isSneaking(){
		return $this->getDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_SNEAKING);
	}

	public function setSneaking($value = true){
		$this->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_SNEAKING, (bool) $value);
	}

	public function isSprinting(){
		return $this->getDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_SPRINTING);
	}

	public function setSprinting($value = true){
		$this->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_SPRINTING, (bool) $value);
	}
	
	public function setFlyingFlag($value = true){
		$this->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_FALL_FLYING, (bool) $value);
	}
	
	public function isImmobile(){
		return $this->getDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE);
	}
	
	public function setImmobile($value = true){
		$this->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_IMMOBILE, (bool) $value);
	}
	
	public function canClimb(){
		return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_CAN_CLIMB);
	}
	
	public function setCanClimb($value = true){
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_CAN_CLIMB, $value);
	}
	
	public function canClimbWalls(){
		return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_WALLCLIMBING);
	}
	
	public function setCanClimbWalls($value = true){
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_WALLCLIMBING, $value);
	}
	
	public function getOwningEntityId(){
		return $this->getDataProperty(self::DATA_OWNER_EID);
	}
	
	public function getOwningEntity(){
		$eid = $this->getOwningEntityId();
		if($eid !== null){
			return $this->server->findEntity($eid, $this->level);
		}
		
		return null;
	}
	
	public function setOwningEntity(Entity $owner){
		if($owner->closed){
			throw new \InvalidArgumentException("Supplied owning entity is garbage and cannot be used");
		}
		
		$this->setDataProperty(self::DATA_OWNER_EID, self::DATA_TYPE_LONG, $owner->getId());
		return true;
	}
	
	public function getEffects(){
		return $this->effects;
	}

	public function removeAllEffects(){
		foreach($this->effects as $effectId => $effect){
			unset($this->effects[$effectId]);
			$effect->remove($this);
		}
		
		$this->recalculateEffectColor();
	}

	public function removeEffect($effectId){
		if(isset($this->effects[$effectId])){
			$effect = $this->effects[$effectId];
			unset($this->effects[$effectId]);
			$effect->remove($this);

			$this->recalculateEffectColor();
		}
	}

	public function getEffect($effectId){
		return isset($this->effects[$effectId]) ? $this->effects[$effectId] : null;
	}

	public function hasEffect($effectId){
		return isset($this->effects[$effectId]);
	}

	public function addEffect(Effect $effect){
		$effectId = $effect->getId();
		if(isset($this->effects[$effectId])){
			if(abs($effect->getAmplifier()) < abs($this->effects[$effectId]->getAmplifier()) || (
				abs($effect->getAmplifier()) === abs($this->effects[$effectId]->getAmplifier()) &&
				$effect->getDuration() <= $this->effects[$effectId]->getDuration())){
				return;
			}
			$effect->add($this, true);
		}else{
			$effect->add($this, false);
		}

		$this->effects[$effectId] = $effect;

		$this->recalculateEffectColor();

		if($effectId === Effect::HEALTH_BOOST){
			$this->setHealth($this->getHealth() + 4 * ($effect->getAmplifier() + 1));
		}
	}

	protected function recalculateEffectColor(){
		$color = [0, 0, 0];
		$count = 0;
		$ambient = true;
		foreach($this->effects as $effect){
			if($effect->isVisible()){
				$c = $effect->getColor();
				$amplifier = $effect->getAmplifier() + 1;
				$color[0] += $c[0] * $amplifier;
				$color[1] += $c[1] * $amplifier;
				$color[2] += $c[2] * $amplifier;
				$count += $amplifier;
				if($ambient === true && !$effect->isAmbient()){
					$ambient = false;
				}
			}
		}

		if($count > 0){
			$r = ($color[0] / $count) & 0xff;
			$g = ($color[1] / $count) & 0xff;
			$b = ($color[2] / $count) & 0xff;

			$this->setDataProperty(Entity::DATA_POTION_COLOR, Entity::DATA_TYPE_INT, ($r << 16) + ($g << 8) + $b);
			$this->setDataProperty(Entity::DATA_POTION_AMBIENT, Entity::DATA_TYPE_BYTE, $ambient ? 1 : 0);
		}else{
			$this->setDataProperty(Entity::DATA_POTION_COLOR, Entity::DATA_TYPE_INT, 0);
			$this->setDataProperty(Entity::DATA_POTION_AMBIENT, Entity::DATA_TYPE_BYTE, 0);
		}
	}

    /**
     * @param $type
     * @param Level $level
     * @param CompoundTag $nbt
     * @param array ...$args
     * @return null
     */
	public static function createEntity($type, Level $level, CompoundTag $nbt, ...$args){
		if(isset(Entity::$knownEntities[$type])){
			$class = Entity::$knownEntities[$type];
			return new $class($level, $nbt, ...$args);
		}

		return null;
	}

	public static function registerEntity($className, $force = false){
		$class = new \ReflectionClass($className);
		if(is_a($className, Entity::class, true) && !$class->isAbstract()){
			if($className::NETWORK_ID !== -1){
				Entity::$knownEntities[$className::NETWORK_ID] = $className;
			}elseif(!$force){
				return false;
			}

			Entity::$knownEntities[$class->getShortName()] = $className;
			Entity::$shortNames[$className] = $class->getShortName();
			return true;
		}

		return false;
	}
	
	public static function createBaseNBT(Vector3 $pos, Vector3 $motion = null, $yaw = 0.0, $pitch = 0.0){
		return new CompoundTag("", [
			new ListTag("Pos", [
				new DoubleTag("", $pos->x),
				new DoubleTag("", $pos->y),
				new DoubleTag("", $pos->z)
			]),
			new ListTag("Motion", [
				new DoubleTag("", $motion ? $motion->x : 0.0),
				new DoubleTag("", $motion ? $motion->y : 0.0),
				new DoubleTag("", $motion ? $motion->z : 0.0)
			]),
			new ListTag("Rotation", [
				new FloatTag("", $yaw),
				new FloatTag("", $pitch)
			])
		]);
	}
	
	/**
	 * @return string
	 */
	public function getSaveId(){
		return Entity::$shortNames[static::class];
	}

	public function saveNBT(){
		if(!($this instanceof Player)){
			$this->namedtag->id = new StringTag("id", $this->getSaveId());
			if($this->getNameTag() !== ""){
				$this->namedtag->CustomName = new StringTag("CustomName", $this->getNameTag());
				$this->namedtag->CustomNameVisible = new StringTag("CustomNameVisible", $this->isNameTagVisible());
			}else{
				unset($this->namedtag->CustomName);
				unset($this->namedtag->CustomNameVisible);
			}
		}

		$this->namedtag->Pos = new ListTag("Pos", [
			new DoubleTag(0, $this->x),
			new DoubleTag(1, $this->y),
			new DoubleTag(2, $this->z)
		]);

		$this->namedtag->Motion = new ListTag("Motion", [
			new DoubleTag(0, $this->motionX),
			new DoubleTag(1, $this->motionY),
			new DoubleTag(2, $this->motionZ)
		]);

		$this->namedtag->Rotation = new ListTag("Rotation", [
			new FloatTag(0, $this->yaw),
			new FloatTag(1, $this->pitch)
		]);

		$this->namedtag->FallDistance = new FloatTag("FallDistance", $this->fallDistance);
		$this->namedtag->Fire = new ShortTag("Fire", $this->fireTicks);
		$this->namedtag->Air = new ShortTag("Air", $this->getDataProperty(Entity::DATA_AIR));
		$this->namedtag->OnGround = new ByteTag("OnGround", $this->onGround ? 1 : 0);
		$this->namedtag->Invulnerable = new ByteTag("Invulnerable", $this->invulnerable ? 1 : 0);

		if(count($this->effects) > 0){
			$effects = [];
			foreach ($this->effects as $effectId => $effect){
				$effects[$effectId] = new CompoundTag($effectId, [
					"Id" => new ByteTag("Id", $effectId),
					"Amplifier" => new ByteTag("Amplifier", $effect->getAmplifier()),
					"Duration" => new IntTag("Duration", $effect->getDuration()),
					"Ambient" => new ByteTag("Ambient", 0),
					//"ShowParticles" => new ByteTag("ShowParticles", $effect->isVisible() ? 1 : 0)
					"ShowParticles" => new ByteTag("ShowParticles", 0)
				]);
			}

			$this->namedtag->ActiveEffects = new ListTag("ActiveEffects", $effects);
		}else{
			unset($this->namedtag->ActiveEffects);
		}
	}

	protected function initEntity(){
		if(isset($this->namedtag->ActiveEffects)){
			foreach($this->namedtag->ActiveEffects->getValue() as $e){
				$effect = Effect::getEffect($e["Id"]);
				if($effect === null){
					continue;
				}
				
				//$effect->setAmplifier($e["Amplifier"])->setDuration($e["Duration"])->setVisible($e["ShowParticles"] > 0);
				$effect->setAmplifier($e["Amplifier"])->setDuration($e["Duration"])->setVisible(0);
				$this->addEffect($effect);
			}
		}
		
		if(isset($this->namedtag->CustomName)){
			$this->setNameTag($this->namedtag["CustomName"]);
			if(isset($this->namedtag->CustomNameVisible)){
				$this->setNameTagVisible($this->namedtag["CustomNameVisible"] > 0);
			}
		}

		$this->scheduleUpdate();
	}

	/**
	 * @return Player[]
	 */
	public function getViewers(){
		return $this->hasSpawned;
	}

	/**
	 * @param Player $player
	 */
	public function spawnTo(Player $player){
		if(!isset($this->hasSpawned[$player->getId()]) && isset($player->usedChunks[Level::chunkHash($this->chunk->getX(), $this->chunk->getZ())])){
			$this->hasSpawned[$player->getId()] = $player;
		}
	}
	
	
	public function isSpawned(Player $player){
		if(isset($this->hasSpawned[$player->getId()])){
			return true;
		}
		
		return false;
	}

	public function sendPotionEffects(Player $player){
		foreach ($this->effects as $effect){
			$pk = new MobEffectPacket();
			$pk->eid = $player->getId();
			$pk->effectId = $effect->getId();
			$pk->amplifier = $effect->getAmplifier();
			$pk->particles = $effect->isVisible();
			$pk->duration = $effect->getDuration();
			$pk->eventId = MobEffectPacket::EVENT_ADD;

			$player->dataPacket($pk);
		}
	}

    /**
     * @param $player
     */
	public function sendMetadata($player){
		$this->sendData($player);
	}

	/**
	 * @param Player[]|Player $player
	 * @param array           $data Properly formatted entity data, defaults to everything
	 */
	public function sendData($player, array $data = null){
		if(!is_array($player)){
			$player = [$player];
		}

		$pk = new SetEntityDataPacket();
		$pk->eid = $this->getId();
		$pk->metadata = $data === null ? $this->dataProperties : $data;

		foreach($player as $p){
			if($p === $this){
				continue;
			}
			
			$p->dataPacket(clone $pk);
		}
		
		if($this instanceof Player){
			$this->dataPacket($pk);
		}
	}

	/**
	 * @param Player $player
	 */
	public function despawnFrom(Player $player){
		if(isset($this->hasSpawned[$player->getId()])){
			$pk = new RemoveEntityPacket();
			$pk->eid = $this->getId();
			$player->dataPacket($pk);
			unset($this->hasSpawned[$player->getId()]);
		}
	}

	/**
	 * @param float             $damage
	 * @param EntityDamageEvent $source
	 *
	 */
	public function attack($damage, EntityDamageEvent $source){
		$cause = $source->getCause();
		if($this->hasEffect(Effect::FIRE_RESISTANCE) && (
			$cause === EntityDamageEvent::CAUSE_FIRE || 
			$cause === EntityDamageEvent::CAUSE_FIRE_TICK || 
			$cause === EntityDamageEvent::CAUSE_LAVA)){
			
			$source->setCancelled();
		}
		
		$this->server->getPluginManager()->callEvent($source);
		if($source->isCancelled()){
			return;
		}
		
		if($source instanceof EntityDamageByEntityEvent){
			$damager = $source->getDamager();
			if($damager instanceof Player){
				$weapon = $damager->getInventory()->getItemInHand();
				if($weapon instanceof Tool){
					$enchantment = $weapon->getEnchantment(Enchantment::TYPE_WEAPON_FIRE_ASPECT);
					if(!is_null($enchantment)){
						$fireDamage = max(($enchantment->getLevel() * 4) - 1, 1);
						$this->setOnFire(4, $fireDamage);
					}
				}
			}
		}

		$this->setLastDamageCause($source);

		$this->setHealth($this->getHealth() - $source->getFinalDamage());
	}

	/**
	 * @param float                   $amount
	 * @param EntityRegainHealthEvent $source
	 *
	 */
	public function heal($amount, EntityRegainHealthEvent $source){
		$this->server->getPluginManager()->callEvent($source);
		if($source->isCancelled()){
			return;
		}
		
		$this->setHealth($this->getHealth() + $source->getAmount());
	}

	/**
	 * @return int
	 */
	public function getHealth(){
		return $this->health;
	}

	public function isAlive(){
		return $this->health > 0;
	}
	
	public function isDead(){
		return $this->health <= 0;
	}
	
	/**
	 * @param int $amount
	 */
	public function setHealth($amount){
		$amount = (int) round($amount);
		if($amount === $this->health){
			return;
		}
		if($amount <= 0){
			$this->health = 0;
			if(!$this->dead){
				$this->kill();
			}
		}elseif($amount <= $this->getMaxHealth() || $amount < $this->health){
			$this->health = (int) $amount;
		}else{
			$this->health = $this->getMaxHealth();
		}
	}

	/**
	 * @param EntityDamageEvent $type
	 */
	public function setLastDamageCause(EntityDamageEvent $type){
		$this->lastDamageCause = $type;
	}

	/**
	 * @return EntityDamageEvent|null
	 */
	public function getLastDamageCause(){
		return $this->lastDamageCause;
	}
	
	public function getAttributeMap(){
		return $this->attributeMap;
	}
	
	/**
	 * @return int
	 */
	public function getMaxHealth(){
		$effect = $this->getEffect(Effect::HEALTH_BOOST);
		return $this->maxHealth + ($effect !== null ? 4 * $effect->getAmplifier() + 1 : 0);
	}

	/**
	 * @param int $amount
	 */
	public function setMaxHealth($amount){
		$this->maxHealth = (int) $amount;
		
		if(!$this->isAlive()){
			$this->health = $this->maxHealth;
		}
	}

	public function canCollideWith(Entity $entity){
		return !$this->justCreated && $entity !== $this;
	}

	protected function checkObstruction($x, $y, $z){
		$i = Math::floorFloat($x);
		$j = Math::floorFloat($y);
		$k = Math::floorFloat($z);

		if(Block::$solid[$this->level->getBlockIdAt($i, $j, $k)]){
			$direction = -1;
			$limit = 9999;
			$diffX = $x - $i;
			$diffY = $y - $j;
			$diffZ = $z - $k;

			if(!Block::$solid[$this->level->getBlockIdAt($i - 1, $j, $k)]){
				$limit = $diffX;
				$direction = 0;
			}
			if(1 - $diffX < $limit && !Block::$solid[$this->level->getBlockIdAt($i + 1, $j, $k)]){
				$limit = 1 - $diffX;
				$direction = 1;
			}
			if($diffY < $limit && !Block::$solid[$this->level->getBlockIdAt($i, $j - 1, $k)]){
				$limit = $diffY;
				$direction = 2;
			}
			if(1 - $diffY < $limit && !Block::$solid[$this->level->getBlockIdAt($i, $j + 1, $k)]){
				$limit = 1 - $diffY;
				$direction = 3;
			}
			if($diffZ < $limit && !Block::$solid[$this->level->getBlockIdAt($i, $j, $k - 1)]){
				$limit = $diffZ;
				$direction = 4;
			}
			if(1 - $diffZ < $limit && !Block::$solid[$this->level->getBlockIdAt($i, $j, $k + 1)]){
				$direction = 5;
			}

			$force = lcg_value() * 0.2 + 0.1;

			switch ($direction){
				case 0:
					$this->motionX = -$force;
					return true;
				case 1:
					$this->motionX = $force;
					return true;
				case 2:
					$this->motionY = -$force;
					return true;
				case 3:
					$this->motionY = $force;
					return true;
				case 4:
					$this->motionZ = -$force;
					return true;
				case 5:
					$this->motionZ= $force;
					return true;
			}
		}

		return false;
	}

	public function entityBaseTick($tickDiff = 1){
		$this->justCreated = false;
		$isPlayer = $this instanceof Player;
		if($this->dead){
			$this->removeAllEffects();
			$this->despawnFromAll();
			if(!$isPlayer){
				$this->close(); 
			}
			
			return false;
		}
		
		foreach($this->effects as $effect){
			if($effect->canTick()){
				$effect->applyEffect($this);
			}
			
			$newDuration = $effect->getDuration() - $tickDiff;
			if($newDuration <= 0){
				$this->removeEffect($effect->getId());
			}else{
				$effect->setDuration($newDuration);
			}
		}

		$hasUpdate = false;
		$block = $this->isCollideWithLiquid();
		if($block !== false){
			$block->onEntityCollide($this);
		}
		
		$block = $this->isCollideWithTransparent();
		if($block !== false){
			$block->onEntityCollide($this);
		}
		
		if($this->y < 0 && !$this->dead){
			$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_VOID, 20);
			$this->attack($ev->getFinalDamage(), $ev);
			$hasUpdate = true;
		}

		if($this->fireTicks > 0){
			if($this->fireProof){
				$this->fireTicks -= 4 * $tickDiff;
			}else{
				if(!$this->hasEffect(Effect::FIRE_RESISTANCE) && ($this->fireTicks % 20) === 0 || $tickDiff > 20){
					$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_FIRE_TICK, $this->fireDamage);
					$this->attack($ev->getFinalDamage(), $ev);
				}
				
				$this->fireTicks -= $tickDiff;
			}

			if($this->fireTicks <= 0){
				$this->extinguish();
			}else{
				$this->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_ONFIRE, true);
				$hasUpdate = true;
			}
		}

		if($this->noDamageTicks > 0){
			$this->noDamageTicks -= $tickDiff;
			if($this->noDamageTicks < 0){
				$this->noDamageTicks = 0;
			}
		}

		$this->age += $tickDiff;
		$this->ticksLived += $tickDiff;
		return $hasUpdate;
	}
	
	protected function updateMovement(){
		$diffPosition = ($this->x - $this->lastX) ** 2 + ($this->y - $this->lastY) ** 2 + ($this->z - $this->lastZ) ** 2;
		$diffRotation = ($this->yaw - $this->lastYaw) ** 2 + ($this->pitch - $this->lastPitch) ** 2;

		$diffMotion = ($this->motionX - $this->lastMotionX) ** 2 + ($this->motionY - $this->lastMotionY) ** 2 + ($this->motionZ - $this->lastMotionZ) ** 2;

		if($diffPosition > 0.04 || $diffRotation > 2.25 && ($diffMotion > 0.0001 && $this->getMotion()->lengthSquared() <= 0.00001)){ //0.2 ** 2, 1.5 ** 2
			$this->lastX = $this->x;
			$this->lastY = $this->y;
			$this->lastZ = $this->z;

			$this->lastYaw = $this->yaw;
			$this->lastPitch = $this->pitch;

			$this->level->addEntityMovement($this->getViewers(), $this->id, $this->x, $this->y + $this->getEyeHeight(), $this->z, $this->yaw, $this->pitch, $this->yaw, ($this instanceof Player));
		}

		if($diffMotion > 0.0025 || ($diffMotion > 0.0001 && $this->getMotion()->lengthSquared() <= 0.0001)){ //0.05 ** 2
			$this->lastMotionX = $this->motionX;
			$this->lastMotionY = $this->motionY;
			$this->lastMotionZ = $this->motionZ;

			$this->level->addEntityMotion($this->getViewers(), $this->id, $this->motionX, $this->motionY, $this->motionZ);
		}
	}
	
	public function getDirectionVector(){
		$y = -sin(deg2rad($this->pitch));
		$xz = cos(deg2rad($this->pitch));
		$x = -$xz * sin(deg2rad($this->yaw));
		$z = $xz * cos(deg2rad($this->yaw));

		return new Vector3($x, $y, $z);
	}

	public function onUpdate($currentTick){
		if($this->closed){
			return false;
		}

		$tickDiff = max(1, $currentTick - $this->lastUpdate);
		
		$this->lastUpdate = $currentTick;
		
		$hasUpdate = $this->entityBaseTick($tickDiff);

		$this->updateMovement();
		
		return $hasUpdate;
	}

	public final function scheduleUpdate(){
		$this->level->updateEntities[$this->id] = $this;
	}

	public function isOnFire(){
		return $this->fireTicks > 0;
	}

	public function setOnFire($seconds, $damage = 1){
		$ticks = $seconds * 20;
		if($ticks > $this->fireTicks){
			$this->fireTicks = $ticks;
		}
		$this->fireDamage = $damage;
	}

	public function getDirection(){
		$rotation = ($this->yaw - 90) % 360;
		if($rotation < 0){
			$rotation += 360;
		}
		if($rotation < 45){
			return Entity::DIRECTION_NORTH;
		}elseif($rotation < 135){
			return Entity::DIRECTION_EAST;
		}elseif($rotation < 225){
			return Entity::DIRECTION_SOUTH;
		}elseif($rotation < 315){
			return Entity::DIRECTION_WEST;
		}
		return Entity::DIRECTION_NORTH;
	}

	public function extinguish(){
		$this->fireTicks = 0;
		$this->fireDamage = 1;
		$this->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_ONFIRE, false);
	}

	public function canTriggerWalking(){
		return true;
	}

	public function resetFallDistance(){
		$this->fallDistance = 0;
	}

	protected function updateFallState($distanceThisTick, $onGround){
        if($onGround){
            if($this->fallDistance > 0){
                $this->fall($this->fallDistance);
                $this->resetFallDistance();
            }
        }elseif($distanceThisTick < 0){
            $this->fallDistance -= $distanceThisTick;
        }
    }

	public function getBoundingBox(){
		return $this->boundingBox;
	}

	public function fall($distance){
		$damage = floor($distance - 3);
		if($damage > 0){
			$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_FALL, $damage);
			$this->attack($ev->getFinalDamage(), $ev);
		}
	}

	public function handleLavaMovement(){

	}

	public function getEyeHeight(){
		return $this->eyeHeight;
	}

	public function moveFlying(){

	}

	public function onCollideWithPlayer(Human $entityPlayer){
		
	}

	protected function switchLevel(Level $targetLevel){
		if($this->isValid()){
			$this->server->getPluginManager()->callEvent($ev = new EntityLevelChangeEvent($this, $this->level, $targetLevel));
			if($ev->isCancelled()){
				return false;
			}

			$this->level->removeEntity($this);
			if($this->chunk !== null){
				$this->chunk->removeEntity($this);
			}
			
			$this->despawnFromAll();
			if($this instanceof Player){
				$X = $Z = null;
				foreach ($this->usedChunks as $index => $d){
					Level::getXZ($index, $X, $Z);
					$this->unloadChunk($X, $Z);
				}
			}
		}
		
		$this->setLevel($targetLevel);
		$this->level->addEntity($this);
		
		if($this instanceof Player){
			$this->usedChunks = [];
			$pk = new SetTimePacket();
			$pk->time = $this->level->getTime();
			$pk->started = $this->level->stopTime == false;
			$this->dataPacket($pk);
		}
		
		$this->chunk = null;
		
		return true;
	}

	public function getPosition(){
		return new Position($this->x, $this->y, $this->z, $this->level);
	}

	public function getLocation(){
		return new Location($this->x, $this->y, $this->z, $this->yaw, $this->pitch, $this->level);
	}

	public function isInsideOfWater(){
		$y = $this->y + $this->eyeHeight;
		$block = $this->level->getBlock(new Vector3(floor($this->x), floor($y), floor($this->z)));
		if($block instanceof Water){
			$f = ($block->y + 1) - ($block->getFluidHeightPercent() - 0.1111111);
			return $y < $f;
		}
		
		return false;
	}

	public function isCollideWithWater(){
		$x = Math::floorFloat($this->x);
		$z = Math::floorFloat($this->z);
		$block = $this->level->getBlock(new Vector3($x, Math::floorFloat($y = $this->y), $z));
		if(!($block instanceof Water)){
			$block = $this->level->getBlock(new Vector3($x, Math::floorFloat($y = ($this->y + $this->eyeHeight)), $z));
		}
		
		if($block instanceof Water){
			$f = ($block->y + 1) - ($block->getFluidHeightPercent() - 0.1111111);
			return $y < $f;
		}
		
		return false;
	}
	
	public function isCollideWithLiquid(){
		$x = Math::floorFloat($this->x);
		$y = Math::floorFloat($this->y);
		$z = Math::floorFloat($this->z);
		
		$block = $this->level->getBlock(new Vector3($x, $y, $z));
		$isLiquid = $block instanceof Liquid;
		
		if(!$isLiquid){
			$y = Math::floorFloat($this->y + $this->eyeHeight);
			$block = $this->level->getBlock(new Vector3($x, $y, $z));
			$isLiquid = $block instanceof Liquid;
			
			if(!$isLiquid){
				$block = $this->level->getBlock(new Vector3(Math::floorFloat($this->x + $this->width), $y, $z));
				$isLiquid = $block instanceof Liquid;
				
				if(!$isLiquid){
					$block = $this->level->getBlock(new Vector3(Math::floorFloat($this->x - $this->width), $y, $z));
					$isLiquid = $block instanceof Liquid;
					
					if(!$isLiquid){
						$block = $this->level->getBlock(new Vector3($x, $y, Math::floorFloat($this->z + $this->width)));
						$isLiquid = $block instanceof Liquid;
						
						if(!$isLiquid){
							$block = $this->level->getBlock(new Vector3($x, $y, Math::floorFloat($this->z - $this->width)));
							$isLiquid = $block instanceof Liquid;
						}
					}
				}
			}
		}
		
		if($isLiquid){
			$f = ($block->y + 1) - ($block->getFluidHeightPercent() - 0.1111111);
			return $y < $f ? $block : false;
		}
		
		return false;
	}
	
	public function isCollideWithTransparent(){
		$x = Math::floorFloat($this->x);
		$z = Math::floorFloat($this->z);
	
		$block = $this->level->getBlock(new Vector3($x, Math::floorFloat($this->y), $z));
		$isTransparent = $block instanceof Ladder || $block instanceof Fire || $block instanceof Vine || $block instanceof Cobweb;
		
		if(!$isTransparent){
			$block = $this->level->getBlock(new Vector3($x, Math::floorFloat($this->y + $this->getEyeHeight()), $z));
			$isTransparent = $block instanceof Ladder || $block instanceof Fire || $block instanceof Vine || $block instanceof Cobweb;
		}
		
		if($isTransparent){
			return $block;
		}
		
		return false;
	}

	public function isInsideOfSolid(){
		$block = $this->level->getBlock(new Vector3(Math::floorFloat($this->x), Math::floorFloat($y = ($this->y + $this->getEyeHeight())), Math::floorFloat($this->z)));

		$bb = $block->getBoundingBox();

		if($bb !== null && $block->isSolid() && !$block->isTransparent() && $bb->intersectsWith($this->getBoundingBox())){
			return true;
		}
		
		return false;
	}
	
	protected function getBlocksAround(){
		$x = floor($this->x);
		$z = floor($this->z);
		$blocksAround = [];
		$blocksAround[] = $this->level->getBlock(new Vector3($x, floor($this->y), $z));
		$blocksAround[] = $this->level->getBlock(new Vector3($x, floor($this->y + $this->eyeHeight), $z));
		return $blocksAround;
	}
	
	protected function checkBlockCollision(){
		foreach($this->getBlocksAround() as $block){
			if($block->hasEntityCollision()){
				$block->onEntityCollide($this);
				$this->onGround = true;
			}
		}
	}
	
	public function fastMove($dx, $dy, $dz){
		if($dx == 0 && $dz == 0 && $dy == 0){
			return true;
		}
		
		$newBB = $this->boundingBox->getOffsetBoundingBox($dx, $dy, $dz);

		$list = $this->level->getCollisionCubes($this, $newBB, false);

		if(count($list) === 0){
			$this->boundingBox = $newBB;
		}

		$this->x = ($this->boundingBox->minX + $this->boundingBox->maxX) / 2;
		$this->y = $this->boundingBox->minY - $this->ySize;
		$this->z = ($this->boundingBox->minZ + $this->boundingBox->maxZ) / 2;

		if(!$this instanceof Player){
			$this->checkChunks();
		}

		if(!$this->onGround || $dy != 0){
			$bb = clone $this->boundingBox;
			$bb->minY -= 0.75;
			$this->onGround = false;

			if(count($this->level->getCollisionBlocks($bb)) > 0){
				$this->onGround = true;
			}
		}
		
		$this->isCollided = $this->onGround;

		$notInAir = $this->onGround || $this->isCollideWithWater();
		$this->updateFallState($dy, $notInAir);
		return true;
	}
	
	/*public function fastMove($dx, $dy, $dz){
		$this->blocksAround = null;

		if($dx == 0 && $dz == 0 && $dy == 0){
			return true;
		}
		
		$newBB = $this->boundingBox->getOffsetBoundingBox($dx, $dy, $dz);

		$list = $this->level->getCollisionCubes($this, $newBB, false);

		if(count($list) === 0){
			$this->boundingBox = $newBB;
		}

		$this->x = ($this->boundingBox->minX + $this->boundingBox->maxX) / 2;
		$this->y = $this->boundingBox->minY - $this->ySize;
		$this->z = ($this->boundingBox->minZ + $this->boundingBox->maxZ) / 2;

		$this->checkChunks();

		if(!$this->onGround || $dy != 0){
			$bb = clone $this->boundingBox;
			$bb->minY -= 0.75;
			$this->onGround = false;

			if(count($this->level->getCollisionBlocks($bb)) > 0){
				$this->onGround = true;
			}
		}
		
		$this->isCollided = $this->onGround;
		$this->updateFallState($dy, $this->onGround);
		
		return true;
	}*/
	
	public function move($dx, $dy, $dz){	
		if($dx == 0 && $dz == 0 && $dy == 0){
			return true;
		}

		if($this->keepMovement){
			$this->boundingBox->offset($dx, $dy, $dz);
			$this->setPosition(new Vector3(($this->boundingBox->minX + $this->boundingBox->maxX) / 2, $this->boundingBox->minY, ($this->boundingBox->minZ + $this->boundingBox->maxZ) / 2));
			return true;
		}else{
			$pos = new Vector3($this->x + $dx, $this->y + $dy, $this->z + $dz);
			if(!$this->setPosition($pos)){
				return false;
			}else{
				$bb = clone $this->boundingBox;
				$bb->maxY = $bb->minY + 0.5;
				$bb->minY -= 1;
				if(count($this->level->getCollisionBlocks($bb)) > 0){
					$this->onGround = true;
				}else{
					$this->onGround = false;
				}
				
				$this->isCollided = $this->onGround;
				$this->updateFallState($dy, $this->onGround);
			}
			
			return true;
		}
	}
	
	/*public function move($dx, $dy, $dz){
		$this->blocksAround = null;

		if($dx == 0 && $dz == 0 && $dy == 0){
			return true;
		}

		if($this->keepMovement){
			$this->boundingBox->offset($dx, $dy, $dz);
			$this->setPosition($this->temporalVector->setComponents(($this->boundingBox->minX + $this->boundingBox->maxX) / 2, $this->boundingBox->minY, ($this->boundingBox->minZ + $this->boundingBox->maxZ) / 2));
			$this->onGround = $this->isPlayer ? true : false;
			
			return true;
		}else{
			$this->ySize *= 0.4;
			
			$movX = $dx;
			$movY = $dy;
			$movZ = $dz;

			$axisalignedbb = clone $this->boundingBox;
			
			assert(abs($dx) <= 20 && abs($dy) <= 20 && abs($dz) <= 20, "Movement distance is excessive: dx=$dx, dy=$dy, dz=$dz");
			
			$tickRate = 1;
			
			$list = $this->level->getCollisionCubes($this, $tickRate > 1 ? $this->boundingBox->getOffsetBoundingBox($dx, $dy, $dz) : $this->boundingBox->addCoord($dx, $dy, $dz), false);

			foreach($list as $bb){
				$dy = $bb->calculateYOffset($this->boundingBox, $dy);
			}

			$this->boundingBox->offset(0, $dy, 0);

			$fallingFlag = ($this->onGround || ($dy != $movY && $movY < 0));

			foreach($list as $bb){
				$dx = $bb->calculateXOffset($this->boundingBox, $dx);
			}

			$this->boundingBox->offset($dx, 0, 0);

			foreach($list as $bb){
				$dz = $bb->calculateZOffset($this->boundingBox, $dz);
			}

			$this->boundingBox->offset(0, 0, $dz);
			
			if($this->stepHeight > 0 && $fallingFlag && $this->ySize < 0.05 && ($movX != $dx || $movZ != $dz)){
				$cx = $dx;
				$cy = $dy;
				$cz = $dz;
				$dx = $movX;
				$dy = $this->stepHeight;
				$dz = $movZ;

				$axisalignedbb1 = clone $this->boundingBox;

				$this->boundingBox->setBB($axisalignedbb);

				$list = $this->level->getCollisionCubes($this, $this->boundingBox->addCoord($dx, $dy, $dz), false);

				foreach($list as $bb){
					$dy = $bb->calculateYOffset($this->boundingBox, $dy);
				}

				$this->boundingBox->offset(0, $dy, 0);

				foreach($list as $bb){
					$dx = $bb->calculateXOffset($this->boundingBox, $dx);
				}

				$this->boundingBox->offset($dx, 0, 0);

				foreach($list as $bb){
					$dz = $bb->calculateZOffset($this->boundingBox, $dz);
				}

				$this->boundingBox->offset(0, 0, $dz);

				if(($cx ** 2 + $cz ** 2) >= ($dx ** 2 + $dz ** 2)){
					$dx = $cx;
					$dy = $cy;
					$dz = $cz;
					$this->boundingBox->setBB($axisalignedbb1);
				}else{
					$this->ySize += 0.5;
				}
			}

			$this->x = ($this->boundingBox->minX + $this->boundingBox->maxX) / 2;
			$this->y = $this->boundingBox->minY - $this->ySize;
			$this->z = ($this->boundingBox->minZ + $this->boundingBox->maxZ) / 2;

			$this->checkChunks();
			$this->checkBlockCollision();
			//$this->checkGroundState($movX, $movY, $movZ, $dx, $dy, $dz);
			$this->updateFallState($dy, $this->onGround);

			if($movX != $dx){
				$this->motionX = 0;
			}

			if($movY != $dy){
				$this->motionY = 0;
			}

			if($movZ != $dz){
				$this->motionZ = 0;
			}
			
			return true;
		}
	}*/
	
	public function setPositionAndRotation(Vector3 $pos, $yaw, $pitch){
		if($this->setPosition($pos) === true){
			$this->setRotation($yaw, $pitch);
			return true;
		}

		return false;
	}

	public function setRotation($yaw, $pitch){
		$this->yaw = $yaw;
		$this->pitch = $pitch;
		$this->scheduleUpdate();
	}

	protected function checkChunks(){
		if($this->chunk === null || ($this->chunk->getX() !== ($this->x >> 4) || $this->chunk->getZ() !== ($this->z >> 4))){
			if($this->chunk !== null){
				$this->chunk->removeEntity($this);
			}
			
			$this->chunk = $this->level->getChunk($this->x >> 4, $this->z >> 4, true);

			if(!$this->justCreated){
				$newChunk = $this->level->getUsingChunk($this->x >> 4, $this->z >> 4);
				foreach($this->hasSpawned as $player){
					if(!isset($newChunk[$player->getId()])){
						$this->despawnFrom($player);
					}else{
						unset($newChunk[$player->getId()]);
					}
				}
				
				foreach($newChunk as $player){
					if($player->canSeeEntity($this)){
						$this->spawnTo($player);
					}
				}
			}

			if($this->chunk === null){
				return;
			}

			$this->chunk->addEntity($this);
		}
	}

	public function setPosition(Vector3 $pos){
		if($this->closed){
			return false;
		}

		if($pos instanceof Position && $pos->level !== null && $pos->level !== $this->level){
			if($this->switchLevel($pos->getLevel()) === false){
				return false;
			}
		}

		$this->x = $pos->x;
		$this->y = $pos->y;
		$this->z = $pos->z;

		$radius = $this->width / 2;
		$this->boundingBox->setBounds($pos->x - $radius, $pos->y, $pos->z - $radius, $pos->x + $radius, $pos->y + $this->height, $pos->z + $radius);

		if(!($this instanceof Player)){
			$this->checkChunks();
		}

		return true;
	}

	public function getMotion(){
		return new Vector3($this->motionX, $this->motionY, $this->motionZ);
	}


    /**
     * @param Vector3 $motion
     * @return bool
     */
	public function setMotion(Vector3 $motion){
		if(!$this->justCreated){
			$this->server->getPluginManager()->callEvent($ev = new EntityMotionEvent($this, $motion));
			if($ev->isCancelled()){
				return true;
			}
		}

		$this->motionX = $motion->x;
		$this->motionY = $motion->y;
		$this->motionZ = $motion->z;

		if(!$this->justCreated){
			$this->updateMovement();
		}

		return true;
	}

	public function isOnGround(){
		return $this->onGround === true;
	}


	public function kill(){
		if($this->dead){
			return false;
		}
		
		$this->dead = true;
		$this->setHealth(0);
		$this->removeAllEffects();
		$this->scheduleUpdate();
	}

	/**
	 * @param Vector3|Position|Location $pos
	 * @param float                     $yaw
	 * @param float                     $pitch
	 *
	 * @return bool
	 */
	public function teleport(Vector3 $pos, $yaw = null, $pitch = null){
		if($pos instanceof Location){
			$yaw = $yaw === null ? $pos->yaw : $yaw;
			$pitch = $pitch === null ? $pos->pitch : $pitch;
		}
		
		$from = Position::fromObject($this, $this->level);
		$to = Position::fromObject($pos, $pos instanceof Position ? $pos->getLevel() : $this->level);
		$this->server->getPluginManager()->callEvent($ev = new EntityTeleportEvent($this, $from, $to));
		
		if($ev->isCancelled()){
			return true;
		}
		
		$this->ySize = 0;
		$pos = $ev->getTo();
		
		if($this->setPositionAndRotation($pos, $yaw === null ? $this->yaw : $yaw, $pitch === null ? $this->pitch : $pitch) !== false){
			$this->resetFallDistance();
			
			$this->onGround = true;

			$this->lastX = $this->x;
			$this->lastY = $this->y;
			$this->lastZ = $this->z;

			$this->lastYaw = $this->yaw;
			$this->lastPitch = $this->pitch;

			$this->updateMovement();
			
			return true;
		}

		return false;
	}

	public function getId(){
		return $this->id;
	}

	public function respawnToAll(){
		foreach($this->hasSpawned as $key => $player){
			unset($this->hasSpawned[$key]);
			if($player->canSeeEntity($this)){
				$this->spawnTo($player);
			}
		}
	}

	public function spawnToAll(){
		if($this->chunk === null || $this->closed){
			return false;
		}
		
		foreach($this->level->getUsingChunk($this->chunk->getX(), $this->chunk->getZ()) as $player){
			if($player->loggedIn && $player->canSeeEntity($this)){
				$this->spawnTo($player);
			}
		}
	}

	public function despawnFromAll(){
		foreach($this->hasSpawned as $player){
			$this->despawnFrom($player);
		}
	}

	public function close(){
		if(!$this->closed){
			$this->server->removeSpawnedEntity($this);
			$this->server->getPluginManager()->callEvent(new EntityDespawnEvent($this));
			$this->closed = true;
			$this->despawnFromAll();
			if($this->chunk !== null){
				$this->chunk->removeEntity($this);
			}
			
			if($this->level !== null){
				$this->level->removeEntity($this);
			}
			
			if($this->attributeMap != null){
				$this->attributeMap = null;
			}
		}
	}
	
	/**
	 * @param Entity $entity
	 *
	 * @return bool
	 */
	public function linkEntity(Entity $entity){
		return $this->setLinked(1, $entity);
	}

	public function sendLinkedData(){
		if($this->linkedEntity instanceof Entity){
			$this->setLinked($this->linkedType, $this->linkedEntity);
		}
	}

	/**
	 * @param int    $type
	 * @param Entity $entity
	 *
	 * @return bool
	 */
	public function setLinked($type = 0, Entity $entity){
		if($entity instanceof Boat || $entity instanceof Minecart){
			$this->setDataProperty(57, 8, [0, 1, 0]);
		}
		if($type != 0 && $entity === null){
			return false;
		}
		if($entity === $this){
			return false;
		}
		switch($type){
			case 0:
				if($this->linkedType == 0){
					return true;
				}
				$this->linkedType = 0;
				$pk = new SetEntityLinkPacket();
				$pk->from = $entity->getId();
				$pk->to = $this->getId();
				$pk->type = 3;
				$this->server->broadcastPacket($this->level->getPlayers(), $pk);
				if($this instanceof Player){
					$pk = new SetEntityLinkPacket();
					$pk->from = $entity->getId();
					$pk->to = 0;
					$pk->type = 3;
					$this->dataPacket($pk);
				}
				if($this->linkedEntity->getLinkedType()){
					$this->linkedEntity->setLinked(0, $this);
				}
				$this->linkedEntity = null;
				return true;
			case 1:
				if(!$entity->isAlive()){
					return false;
				}
				$this->linkedEntity = $entity;
				$this->linkedType = 1;
				$entity->linkedEntity = $this;
				$entity->linkedType = 1;
				$pk = new SetEntityLinkPacket();
				$pk->from = $entity->getId();
				$pk->to = $this->getId();
				$pk->type = 2;
				$this->server->broadcastPacket($this->level->getPlayers(), $pk);
				if($this instanceof Player){
					$pk = new SetEntityLinkPacket();
					$pk->from = $entity->getId();
					$pk->to = 0;
					$pk->type = 2;
					$this->dataPacket($pk);
				}
				return true;
			case 2:
				if(!$entity->isAlive()){
					return false;
				}
				if($entity->getLinkedEntity() !== $this){
					return $entity->linkEntity($this);
				}
				$this->linkedEntity = $entity;
				$this->linkedType = 2;
				return true;
				default;
				return false;
		}
	}

	/**
	 * @return Entity
	 */
	public function getLinkedEntity(){
		return $this->linkedEntity;
	}

	/**
	 * @return null
	 */
	public function getLinkedType(){
		return $this->linkedType;
	}

    /**
     * @param $id
     * @param $type
     * @param $value
     * @param bool $send
     */
	public function setDataProperty($id, $type, $value, $send = true){
		if($this->getDataProperty($id) !== $value){
			$this->dataProperties[$id] = [$type, $value];
			if(!$send){
				return;
			}
		
			$targets = $this->hasSpawned;
			if($this instanceof Player){
				if(!$this->spawned){
					return;
				}
				
				$targets[] = $this;
			}

			$this->sendData($targets, [$id => $this->dataProperties[$id]]);
		}
	}
	
	public function removeDataProperty($id, $send = true){
		unset($this->dataProperties[$id]);
		if($send){
			$this->sendData($this->hasSpawned);
		}
	}

	/**
	 * @param int $id
	 *
	 * @return mixed
	 */
	public function getDataProperty($id){
		return isset($this->dataProperties[$id]) ? $this->dataProperties[$id][1] : null;
	}

	/**
	 * @param int $id
	 *
	 * @return int
	 */
	public function getDataPropertyType($id){
		return isset($this->dataProperties[$id]) ? $this->dataProperties[$id][0] : null;
	}

    /**
     * @param $propertyId
     * @param $id
     * @param bool $value
     * @param int $type
     * @param bool $send
     */
	public function setDataFlag($propertyId, $id, $value = true, $type = Entity::DATA_TYPE_LONG, $send = true){
		if($this->getDataFlag($propertyId, $id) !== $value){
			$flags = (int) $this->getDataProperty($propertyId);
			$flags ^= 1 << $id;
			$this->setDataProperty($propertyId, $type, $flags, $send);
		}
	}

	/**
	 * @param int $propertyId
	 * @param int $id
	 *
	 * @return bool
	 */
	public function getDataFlag($propertyId, $id){
		return (((int) $this->getDataProperty($propertyId)) & (1 << $id)) > 0;
	}

	public function __destruct(){
		$this->close();
	}

	public function setMetadata($metadataKey, MetadataValue $metadataValue){
		$this->server->getEntityMetadata()->setMetadata($this, $metadataKey, $metadataValue);
	}

	public function getMetadata($metadataKey){
		return $this->server->getEntityMetadata()->getMetadata($this, $metadataKey);
	}

	public function hasMetadata($metadataKey){
		return $this->server->getEntityMetadata()->hasMetadata($this, $metadataKey);
	}

	public function removeMetadata($metadataKey, Plugin $plugin){
		$this->server->getEntityMetadata()->removeMetadata($this, $metadataKey, $plugin);
	}

	public function __toString(){
		return (new \ReflectionClass($this))->getShortName() . "(" . $this->getId() . ")";
	}
	
	public function setAirTick($val){
		$this->setDataProperty(Entity::DATA_AIR, Entity::DATA_TYPE_SHORT, $val, false);
	}
	
	public function isNeedSaveOnChunkUnload(){
		return true;
	}
	
}
