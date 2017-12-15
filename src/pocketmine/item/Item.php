<?php

#______           _    _____           _                  
#|  _  \         | |  /  ___|         | |                 
#| | | |__ _ _ __| | _\ `--. _   _ ___| |_ ___ _ __ ___   
#| | | / _` | '__| |/ /`--. \ | | / __| __/ _ \ '_ ` _ \  
#| |/ / (_| | |  |   </\__/ / |_| \__ \ ||  __/ | | | | | 
#|___/ \__,_|_|  |_|\_\____/ \__, |___/\__\___|_| |_| |_| 
#                             __/ |                       
#                            |___/

namespace pocketmine\item;

use pocketmine\entity\Entity;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\block\Block;
use pocketmine\inventory\Fuel;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\level\Level;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\NBT;
use pocketmine\utils\Config;

class Item{
	
	const MAX_STACK = 64;

	/** @var NBT|null */
	private static $cachedParser = null;

	/**
	 * @param $tag
	 * @return CompoundTag
	 */
	private static function parseCompoundTag($tag){
		if(Item::$cachedParser === null){
			Item::$cachedParser = new NBT(NBT::LITTLE_ENDIAN);
		}

		Item::$cachedParser->read($tag);
		return Item::$cachedParser->getData();
	}

	/**
	 * @param CompoundTag $tag
	 * @return string
	 */
	private static function writeCompoundTag(CompoundTag $tag){
		if(Item::$cachedParser === null){
			Item::$cachedParser = new NBT(NBT::LITTLE_ENDIAN);
		}

		Item::$cachedParser->setData($tag);
		return Item::$cachedParser->write(true);
	}
	
	const AIR = 0;
	const STONE = 1;
	const GRASS = 2;
	const DIRT = 3;
	const COBBLESTONE = 4;
	const COBBLE = 4;
	const PLANK = 5;
	const PLANKS = 5;
	const WOODEN_PLANK = 5;
	const WOODEN_PLANKS = 5;
	const SAPLING = 6;
	const SAPLINGS = 6;
	const BEDROCK = 7;
	const WATER = 8;
	const STILL_WATER = 9;
	const LAVA = 10;
	const STILL_LAVA = 11;
	const SAND = 12;
	const GRAVEL = 13;
	const GOLD_ORE = 14;
	const IRON_ORE = 15;
	const COAL_ORE = 16;
	const LOG = 17;
	const WOOD = 17;
	const TRUNK = 17;
	const LEAVES = 18;
	const LEAVE = 18;
	const SPONGE = 19;
	const GLASS = 20;
	const LAPIS_ORE = 21;
	const LAPIS_BLOCK = 22;
	const DISPENSER = 23;
	const SANDSTONE = 24;
	const NOTE_BLOCK = 25;
	const BED_BLOCK = 26;
	const POWERED_RAIL = 27;
	const DETECTOR_RAIL = 28;
	const STICKY_PISTON = 29;
	const COBWEB = 30;
	const TALL_GRASS = 31;
	const BUSH = 32;
	const DEAD_BUSH = 32;
	const PISTON = 33;
	const PISTON_HEAD = 34;
	const WOOL = 35;
	const PISTON_EXTENSION = 36;
	const DANDELION = 37;
	const POPPY = 38;
	const ROSE = 38;
	const RED_FLOWER = 38;
	const BROWN_MUSHROOM = 39;
	const RED_MUSHROOM = 40;
	const GOLD_BLOCK = 41;
	const IRON_BLOCK = 42;
	const DOUBLE_SLAB = 43;
	const DOUBLE_SLABS = 43;
	const SLAB = 44;
	const SLABS = 44;
	const BRICKS = 45;
	const BRICKS_BLOCK = 45;
	const TNT = 46;
	const BOOKSHELF = 47;
	const MOSS_STONE = 48;
	const MOSSY_STONE = 48;
	const OBSIDIAN = 49;
	const TORCH = 50;
	const FIRE = 51;
	const MONSTER_SPAWNER = 52;
	const WOOD_STAIRS = 53;
	const WOODEN_STAIRS = 53;
	const OAK_WOOD_STAIRS = 53;
	const OAK_WOODEN_STAIRS = 53;
	const CHEST = 54;
	const REDSTONE_WIRE = 55;
	const DIAMOND_ORE = 56;
	const DIAMOND_BLOCK = 57;
	const CRAFTING_TABLE = 58;
	const WORKBENCH = 58;
	const WHEAT_BLOCK = 59;
	const FARMLAND = 60;
	const FURNACE = 61;
	const BURNING_FURNACE = 62;
	const LIT_FURNACE = 62;
	const SIGN_POST = 63;
	const DOOR_BLOCK = 64;
	const WOODEN_DOOR_BLOCK = 64;
	const WOOD_DOOR_BLOCK = 64;
	const LADDER = 65;
	const RAIL = 66;
	const COBBLE_STAIRS = 67;
	const COBBLESTONE_STAIRS = 67;
	const WALL_SIGN = 68;
	const LEVER = 69;
	const STONE_PRESSURE_PLATE = 70;
	const IRON_DOOR_BLOCK = 71;
	const WOODEN_PRESSURE_PLATE = 72;
	const REDSTONE_ORE = 73;
	const GLOWING_REDSTONE_ORE = 74;
	const LIT_REDSTONE_ORE = 74;
	const REDSTONE_TORCH = 75;
	const REDSTONE_TORCH_ACTIVE = 76;
	const STONE_BUTTON = 77;
	const SNOW = 78;
	const SNOW_LAYER = 78;
	const ICE = 79;
	const SNOW_BLOCK = 80;
	const CACTUS = 81;
	const CLAY_BLOCK = 82;
	const REEDS = 83;
	const SUGARCANE_BLOCK = 83;
	const JUKEBOX = 84;
	const FENCE = 85;
	const PUMPKIN = 86;
	const NETHERRACK = 87;
	const SOUL_SAND = 88;
	const GLOWSTONE = 89;
	const GLOWSTONE_BLOCK = 89;
	const PORTAL = 90;
	const LIT_PUMPKIN = 91;
	const JACK_O_LANTERN = 91;
	const CAKE_BLOCK = 92;
	const REDSTONE_REPEATER_BLOCK = 93;
	const REDSTONE_REPEATER_BLOCK_ACTIVE = 94;
	const INVISIBLE_BEDROCK = 95;
	const TRAPDOOR = 96;
	const MONSTER_EGG = 97;
	const STONE_BRICKS = 98;
	const STONE_BRICK = 98;
	const BROWN_MUSHROOM_BLOCK = 99;
	const RED_MUSHROOM_BLOCK = 100;
	const IRON_BAR = 101;
	const IRON_BARS = 101;
	const GLASS_PANE = 102;
	const GLASS_PANEL = 102;
	const MELON_BLOCK = 103;
	const PUMPKIN_STEM = 104;
	const MELON_STEM = 105;
	const VINE = 106;
	const VINES = 106;
	const FENCE_GATE = 107;
	const BRICK_STAIRS = 108;
	const STONE_BRICK_STAIRS = 109;
	const MYCELIUM = 110;
	const WATER_LILY = 111;
	const LILY_PAD = 111;
	const NETHER_BRICKS = 112;
	const NETHER_BRICK_BLOCK = 112;
	const NETHER_BRICK_FENCE = 113;
	const NETHER_BRICKS_STAIRS = 114;
	const NETHER_WART_BLOCK = 115;
	const ENCHANTING_TABLE = 116;
	const ENCHANT_TABLE = 116;
	const ENCHANTMENT_TABLE = 116;
	const BREWING_STAND_BLOCK = 117;
	const CAULDRON_BLOCK = 118;
	const END_PORTAL = 120;
	const END_STONE = 121;
	const DRAGON_EGG = 122;
	const REDSTONE_LAMP = 123;
	const REDSTONE_LAMP_ACTIVE = 124;
	const DROPPER = 125;
	const ACTIVATOR_RAIL = 126;
	const COCOA = 127;
	const SANDSTONE_STAIRS = 128;
	const EMERALD_ORE = 129;
	const ENDER_CHEST = 130;
	const TRIPWIRE_HOOK = 131;
	const TRIPWIRE = 132;
	const EMERALD_BLOCK = 133;
	const SPRUCE_WOOD_STAIRS = 134;
	const SPRUCE_WOODEN_STAIRS = 134;
	const BIRCH_WOOD_STAIRS = 135;
	const BIRCH_WOODEN_STAIRS = 135;
	const JUNGLE_WOOD_STAIRS = 136;
	const JUNGLE_WOODEN_STAIRS = 136;
	const BEACON = 138;
	const COBBLE_WALL = 139;
	const STONE_WALL = 139;
	const COBBLESTONE_WALL = 139;
	const FLOWER_POT_BLOCK = 140;
	const CARROT_BLOCK = 141;
	const POTATO_BLOCK = 142;
	const WOODEN_BUTTON = 143;
	const MOB_HEAD_BLOCK = 144;
	const ANVIL = 145;
	const TRAPPED_CHEST = 146;
	const WEIGHTED_PRESSURE_PLATE_LIGHT = 147;
	const WEIGHTED_PRESSURE_PLATE_HEAVY = 148;
	const REDSTONE_COMPARATOR_BLOCK = 149;
	const REDSTONE_COMPARATOR_BLOCK_POWERED = 150;
	const DAYLIGHT_SENSOR = 151;
	const REDSTONE_BLOCK = 152;
	const NETHER_QUARTZ_ORE = 153;
	const HOPPER_BLOCK = 154;
	const QUARTZ_BLOCK = 155;
	const QUARTZ_STAIRS = 156;
	const DOUBLE_WOOD_SLAB = 157;
	const DOUBLE_WOODEN_SLAB = 157;
	const DOUBLE_WOOD_SLABS = 157;
	const DOUBLE_WOODEN_SLABS = 157;
	const WOOD_SLAB = 158;
	const WOODEN_SLAB = 158;
	const WOOD_SLABS = 158;
	const WOODEN_SLABS = 158;
	const STAINED_CLAY = 159;
	const STAINED_HARDENED_CLAY = 159;
	const STAINED_GLASS_PANE = 160;
	const LEAVES2 = 161;
	const LEAVE2 = 161;
	const WOOD2 = 162;
	const TRUNK2 = 162;
	const LOG2 = 162;
	const ACACIA_WOOD_STAIRS = 163;
	const ACACIA_WOODEN_STAIRS = 163;
	const DARK_OAK_WOOD_STAIRS = 164;
	const DARK_OAK_WOODEN_STAIRS = 164;
	const SLIME_BLOCK = 165;
	const IRON_TRAPDOOR = 167;
	const HAY_BALE = 170;
	const CARPET = 171;
	const HARDENED_CLAY = 172;
	const COAL_BLOCK = 173;
	const PACKED_ICE = 174;
	const DOUBLE_PLANT = 175;
	const INVERTED_DAYLIGHT_SENSOR = 178;
	const RED_SANDSTONE = 179;
	const RED_SANDSTONE_STAIRS = 180;
	const DOUBLE_RED_SANDSTONE_SLAB = 181;
	const RED_SANDSTONE_SLAB = 182;
	const FENCE_GATE_SPRUCE = 183;
	const FENCE_GATE_BIRCH = 184;
	const FENCE_GATE_JUNGLE = 185;
	const FENCE_GATE_DARK_OAK = 186;
	const FENCE_GATE_ACACIA = 187;
	const SPRUCE_DOOR_BLOCK = 193;
	const BIRCH_DOOR_BLOCK = 194;
	const JUNGLE_DOOR_BLOCK = 195;
	const ACACIA_DOOR_BLOCK = 196;
	const DARK_OAK_DOOR_BLOCK = 197;
	const GRASS_PATH = 198;
	const ITEM_FRAME_BLOCK = 199;
	const CHORUS_FLOWER = 200;
	const PURPUR_BLOCK = 201;
	const END_BRICKS = 206;
	const END_ROD = 208;
	const CHORUS_PLANT = 240;
	const STAINED_GLASS = 241;
	const PODZOL = 243;
	const BEETROOT_BLOCK = 244;
	const STONECUTTER = 245;
	const GLOWING_OBSIDIAN = 246;
	const NETHER_REACTOR = 247;
	const IRON_SHOVEL = 256; //
	const IRON_PICKAXE = 257; //
	const IRON_AXE = 258; //
	const FLINT_STEEL = 259; //
	const FLINT_AND_STEEL = 259; //
	const APPLE = 260; //
	const BOW = 261;
	const ARROW = 262;
	const COAL = 263; //
	const DIAMOND = 264; //
	const IRON_INGOT = 265; //
	const GOLD_INGOT = 266; //
	const IRON_SWORD = 267;
	const WOODEN_SWORD = 268; //
	const WOODEN_SHOVEL = 269; //
	const WOODEN_PICKAXE = 270; //
	const WOODEN_AXE = 271; //
	const STONE_SWORD = 272;
	const STONE_SHOVEL = 273;
	const STONE_PICKAXE = 274;
	const STONE_AXE = 275;
	const DIAMOND_SWORD = 276;
	const DIAMOND_SHOVEL = 277;
	const DIAMOND_PICKAXE = 278;
	const DIAMOND_AXE = 279;
	const STICK = 280; //
	const STICKS = 280;
	const BOWL = 281; //
	const MUSHROOM_STEW = 282;
	const GOLD_SWORD = 283;
	const GOLD_SHOVEL = 284;
	const GOLD_PICKAXE = 285;
	const GOLD_AXE = 286;
	const GOLDEN_SWORD = 283;
	const GOLDEN_SHOVEL = 284;
	const GOLDEN_PICKAXE = 285;
	const GOLDEN_AXE = 286;
	const STRING = 287;
	const FEATHER = 288; //
	const GUNPOWDER = 289;
	const WOODEN_HOE = 290;
	const STONE_HOE = 291;
	const IRON_HOE = 292; //
	const DIAMOND_HOE = 293;
	const GOLD_HOE = 294;
	const GOLDEN_HOE = 294;
	const SEEDS = 295;
	const WHEAT_SEEDS = 295;
	const WHEAT = 296;
	const BREAD = 297;
	const LEATHER_CAP = 298;
	const LEATHER_TUNIC = 299;
	const LEATHER_PANTS = 300;
	const LEATHER_BOOTS = 301;
	const CHAIN_HELMET = 302;
	const CHAIN_CHESTPLATE = 303;
	const CHAIN_LEGGINGS = 304;
	const CHAIN_BOOTS = 305;
	const IRON_HELMET = 306;
	const IRON_CHESTPLATE = 307;
	const IRON_LEGGINGS = 308;
	const IRON_BOOTS = 309;
	const DIAMOND_HELMET = 310;
	const DIAMOND_CHESTPLATE = 311;
	const DIAMOND_LEGGINGS = 312;
	const DIAMOND_BOOTS = 313;
	const GOLD_HELMET = 314;
	const GOLD_CHESTPLATE = 315;
	const GOLD_LEGGINGS = 316;
	const GOLD_BOOTS = 317;
	const FLINT = 318;
	const RAW_PORKCHOP = 319;
	const COOKED_PORKCHOP = 320;
	const PAINTING = 321;
	const GOLDEN_APPLE = 322;
	const SIGN = 323;
	const WOODEN_DOOR = 324;
	const BUCKET = 325;
	const MINECART = 328;
	const SADDLE = 329;
	const IRON_DOOR = 330;
	const REDSTONE = 331;
	const REDSTONE_DUST = 331;
	const SNOWBALL = 332;
	const BOAT = 333;
	const LEATHER = 334;
	const BRICK = 336;
	const CLAY = 337;
	const SUGARCANE = 338;
	const SUGAR_CANE = 338;
	const SUGAR_CANES = 338;
	const PAPER = 339;
	const BOOK = 340;
	const SLIMEBALL = 341;
	const MINECART_WITH_CHEST = 342;
	const EGG = 344;
	const COMPASS = 345;
	const FISHING_ROD = 346;
	const CLOCK = 347;
	const GLOWSTONE_DUST = 348;
	const RAW_FISH = 349;
	const COOKED_FISH = 350;
	const DYE = 351;
	const BONE = 352;
	const SUGAR = 353;
	const CAKE = 354;
	const BED = 355;
	const REDSTONE_REPEATER = 356;
	const COOKIE = 357;
	const FILLED_MAP = 358;
	const SHEARS = 359;
	const MELON = 360;
	const MELON_SLICE = 360;
	const PUMPKIN_SEEDS = 361;
	const MELON_SEEDS = 362;
	const RAW_BEEF = 363;
	const STEAK = 364;
	const COOKED_BEEF = 364;
	const RAW_CHICKEN = 365;
	const COOKED_CHICKEN = 366;
	const ROTTEN_FLESH = 367;
	const ENDER_PEARL = 368;
	const BLAZE_ROD = 369;
	const GHAST_TEAR = 370;
	const GOLD_NUGGET = 371;
	const GOLDEN_NUGGET = 371;
	const NETHER_WART = 372;
	const POTION = 373;
	const GLASS_BOTTLE = 374;
	const SPIDER_EYE = 375;
	const FERMENTED_SPIDER_EYE = 376;
	const BLAZE_POWDER = 377;
	const MAGMA_CREAM = 378;
	const BREWING_STAND = 379;
	const CAULDRON = 380;
	const EYE_OF_ENDER = 381;
	const GLISTERING_MELON = 382;
	const SPAWN_EGG = 383;
	const BOTTLE_ENCHANTING = 384;
	const BOTTLE_O_ENCHANTING = 384;
	const ENCHANTING_BOTTLE = 384;
	const ENCHANT_BOTTLE = 384;
	const FIRE_CHARGE = 385;
	const WRITABLE_BOOK = 386;
	const WRITTEN_BOOK = 387;
	const EMERALD = 388;
	const ITEM_FRAME = 389;
	const FLOWER_POT = 390;
	const CARROT = 391;
	const CARROTS = 391;
	const POTATO = 392;
	const POTATOES = 392;
	const BAKED_POTATO = 393;
	const BAKED_POTATOES = 393;
	const POISONOUS_POTATO = 394;
	const MAP = 395;
	const EMPTY_MAP = 395;
	const GOLDEN_CARROT = 396;
	const MOB_HEAD = 397;
	const CARROT_ON_STICK = 398;
	const NETHER_STAR = 399;
	const PUMPKIN_PIE = 400;
	const FIREWORK = 401;
	const FIREWORK_STAR = 402;
	const ENCHANTING_BOOK = 403;
	const REDSTONE_COMPARATOR = 404;
	const NETHER_BRICK = 405;
	const QUARTZ = 406;
	const NETHER_QUARTZ = 406;
	const MINECART_WITH_TNT = 407;
	const MINECART_WITH_HOPPER = 408;
	const HOPPER = 410;
	const RAW_RABBIT = 411;
	const COOKED_RABBIT = 412;
	const RABBIT_STEW = 413;
	const RABBIT_FOOT = 414;
	const RABBIT_HIDE = 415;
	const LEATHER_HORSE_ARMOR = 416;
	const IRON_HORSE_ARMOR = 417;
	const GOLDEN_HORSE_ARMOR = 418;
	const DIAMOND_HORSE_ARMOR = 419;
	const LEAD = 420;
	const NAME_TAG = 421;
	const PRISMARINE_CRYSTAL = 422;
	const RAW_MUTTON = 423;
	const COOKED_MUTTON = 424;
	const ARMOR_STAND = 425;
	const END_CRYSTAL = 426;
	const SPRUCE_DOOR = 427;
	const BIRCH_DOOR = 428;
	const JUNGLE_DOOR = 429;
	const ACACIA_DOOR = 430;
	const DARK_OAK_DOOR = 431;
	const CHORUS_FRUIT = 432;
	const POPPED_CHORUS_FRUIT = 433;
	const DRAGONS_BREATH = 437;
	const SPLASH_POTION = 438;
	const LINGERING_POTION = 441;
	const ELYTRA = 444;
	const SHULKER_SHELL = 445;
	const BANNER = 446;
	const TOTEM = 450;
	const BEETROOT = 457;
	const BEETROOT_SEEDS = 458;
	const BEETROOT_SEED = 458;
	const BEETROOT_SOUP = 459;
	const RAW_SALMON = 460;
	const CLOWNFISH = 461;
	const PUFFERFISH = 462;
	const COOKED_SALMON = 463;
	const ENCHANTED_GOLDEN_APPLE = 466;
	const END_PEARL = 468;
	const CAMERA = 498;
	const MUSIC_DISC_13 = 500;
	const MUSIC_DISC_CAT = 501;
	const MUSIC_DISC_BLOCKS = 502;
	const MUSIC_DISC_CHIRP = 503;
	const MUSIC_DISC_FAR = 504;
	const MUSIC_DISC_MALL = 505;
	const MUSIC_DISC_MELLOHI = 506;
	const MUSIC_DISC_STAL = 507;
	const MUSIC_DISC_STRAD = 508;
	const MUSIC_DISC_WARD = 509;
	const MUSIC_DISC_11 = 510;
	const MUSIC_DISC_WAIT = 511;
	
	protected static $names = [
		0 => "Air",
		1 => "Stone",
		2 => "Grass",
		3 => "Dirt",
		4 => "Cobblestone",
		5 => "Plank",
		6 => "Sapling",
		7 => "Bedrock",
		8 => "Water",
		9 => "Still Water",
		10 => "Lava",
		11 => "Still Lava",
		12 => "Sand",
		13 => "Gravel",
		14 => "Gold Ore",
		15 => "Iron Ore",
		16 => "Coal Ore",
		17 => "Wood",
		18 => "Leaves",
		19 => "Sponge",
		20 => "Glass",
		21 => "Lapis Ore",
		22 => "Lapis Block",
		24 => "Sandstone",
		26 => "Bed",
		27 => "Powered Rail",
		28 => "Detector Rail",
		29 => "Sticky Piston",
		30 => "Cobweb",
		31 => "Tall Grass",
		32 => "Bush",
		33 => "Piston",
		34 => "Piston Head",
		35 => "Wool",
		36 => "Piston Extension",
		37 => "Dandelion",
		38 => "Red Flower",
		39 => "Brown Mushroom",
		40 => "Red Mushroom",
		41 => "Gold Block",
		42 => "Iron Block",
		43 => "Double Slab",
		44 => "Slab",
		45 => "Bricks",
		46 => "TNT",
		47 => "Bookshelf",
		48 => "Moss Stone",
		49 => "Obsidian",
		50 => "Torch",
		51 => "Fire",
		52 => "Monster Spawner",
		53 => "Wooden Stairs",
		54 => "Chest",
		56 => "Diamond Ore",
		57 => "Diamond Block",
		58 => "Crafting Table",
		59 => "Wheat Block",
		60 => "Farmland",
		61 => "Furnace",
		62 => "Burning Furnace",
		63 => "Sign Post",
		64 => "Door",
		65 => "Ladder",
		66 => "Rail",
		67 => "Cobble Stairs",
		68 => "Wall Sign",
		71 => "Iron Door",
		73 => "Redstone Ore",
		74 => "Glowing Redstone Ore",
		75 => "Redstone Torch",
		76 => "Glowing Redstone Torch",
		78 => "Snow",
		79 => "Ice",
		80 => "Snow Block",
		81 => "Cactus",
		82 => "Clay Block",
		83 => "Sugarcane Block",
		84 => "Jukebox",
		85 => "Fence",
		86 => "Pumpkin Block",
		87 => "Netherrack",
		88 => "Soul Sand",
		89 => "Glowstone",
		90 => "Portal",
		91 => "Jack-O'-Lantern",
		92 => "Cake Block",
		//95 => "Invisible Bedrock",
		95 => "Stained Glass",
		96 => "Trapdoor",
		98 => "Stone Bricks",
		99 => "Brown Mushroom Block",
		100 => "Red Mushroom Block",
		101 => "Iron Bar",
		102 => "Glass Panel",
		103 => "Melon BLock",
		104 => "Pumpkin Stem",
		106 => "Vine",
		107 => "Fence Gate",
		108 => "Brick Stairs",
		109 => "Stone Brick Stairs",
		110 => "Mycelium",
		111 => "Water Lily",
		112 => "Nether Brick",
		113 => "Nether Brick Fence",
		114 => "Nether Brick Stairs",
		116 => "Enchantment Table",
		117 => "Brewing Stand",
		118 => "Cauldron Block",
		120 => "End Portal",
		121 => "End Stone",
        122 => "Dragon Egg",
		123 => "Redstone Lamp",
		124 => "Redstone Lamp Active",
		125 => "Dropper",
		126 => "Activator Rail",
		127 => "Cocoa",
		128 => "Sendstone Stairs",
		129 => "Emerald Ore",
        130 => "Ender Chest",
		131 => "Tripwire Hook",
		132 => "Tripwire",
		133 => "Emerald Block",
		134 => "Spruce Wood Stairs",
		135 => "Birch Wood Stairs",
		136 => "Jungle Wood Stairs",
		138 => "Beacon",
		139 => "Cobblestone Wall",
		140 => "Flower Pot",
		141 => "Carrot Block",
		142 => "Potato Block",
		143 => "Wooden Button",
		144 => "Mob Head",
		145 => "Anvil",
		146 => "Trapped Chest",
		147 => "Weighted Pressure Plate Light",
		148 => "Weighted Pressure Plate Heavy",
		149 => "Redstone Comparator",
		150 => "Redstone Comparator Powered",
		151 => "Daylight Sensor",
		152 => "Redstone Block",
		153 => "Nether Quartz Ore",
		154 => "Hopper",
		155 => "Quartz Block",
		156 => "Quartz Stairs",
		157 => "Double Wood Slab",
		158 => "Wooden Slab",
		159 => "Stained Clay",
        160 => 'Stained Glass Pane',
		161 => "Leaves2",
		162 => "Wood2",
		163 => "Acacia Wood Stairs",
		164 => "Dark Oak Wood Stairs",
		165 => "Slime Block",
		167 => "Iron Trapdoor",
		170 => "Hay Bale",
		171 => "Carpet",
		172 => "Hardened Clay",
		173 => "Coal BLock",
		175 => "Double Plant",
		178 => "Inverted Daylight Sensor",
		179 => "Red Sandstone",
		180 => "Red Sandstone Stairs",
		181 => "Double Red Sandstone Slab",
		182 => "Red Sandstone Slab",
		183 => "Fence Gate Spruce",
		184 => "Fence Gate Birch",
		185 => "Fence Gate Jungle",
		186 => "Fence Gate Dark Oak",
		187 => "Fence Gate Acacia",
		193 => "Wood Door Block",
		194 => "Birch Door",
		195 => "Jungle Door",
		196 => "Acacia Door",
		197 => "Dark Oak Door",
		198 => "End Rod",
		199 => "Chorus Plant",
		200 => "Chorus Flower",
		201 => "Purpur Block",
		202 => "Purpur Pillar",
		203 => "Purpur Stairs",
		204 => "Purpur Double Slab",
		205 => "Purpur Slab",
		206 => "End Stone Bricks",
		207 => "Beetroot Block", ///
		208 => "Grass Path",
		209 => "End Gateway",
		213 => "Magma",
		216 => "Bone Block",
		243 => "Podzol",
		244 => "Beetroot Block", ///
		245 => "Stonecutter",
		246 => "Glowing Obsidian",
		247 => "Nether Reactor",
		256 => "Iron Shovel",
		257 => "Iron Pickaxe",
		258 => "Iron Axe",
		259 => "Flint & Steel",
		260 => "Apple",
		261 => "Bow",
		262 => "Arrow",
		263 => "Coal",
		264 => "Diamond",
		265 => "Iron Ingot",
		266 => "Gold Ingot",
		267 => "Iron Sword",
		268 => "Wooden Sword",
		269 => "Wooden Shovel",
		270 => "Wooden Pickaxe",
		271 => "Wooden Axe",
		272 => "Stone Sword",
		273 => "Stone Shovel",
		274 => "Stone Pickaxe",
		275 => "Stone Axe",
		276 => "Diamond Sword",
		277 => "Diamond Shovel",
		278 => "Diamond Pickaxe",
		279 => "Diamond Axe",
		280 => "Stick",
		281 => "Bowl",
		282 => "Mushroom Stew",
		283 => "Gold Sword",
		284 => "Gold Shovel",
		285 => "Gold Pickaxe",
		286 => "Gold Axe",
		287 => "String",
		288 => "Feather",
		289 => "Gunpowder",
		290 => "Wooden Hoe",
		291 => "Stone Hoe",
		292 => "Iron Hoe",
		293 => "Diamond Hoe",
		294 => "Gold Hoe",
		295 => "Wheat Seed",
		296 => "Wheat",
		297 => "Bread",
		298 => "Leather Cap",
		299 => "Leather Tunic",
		300 => "Leather Pants",
		301 => "Leather Boots",
		302 => "Chain Helmet",
		303 => "Chain Chestplate",
		304 => "Chain Leggins",
		305 => "Chain Boots",
		306 => "Iron Helmet",
		307 => "Iron Chestplate",
		308 => "Iron Leggins",
		309 => "Iron Boots",
		310 => "Diamond Helmet",
		311 => "Diamond Chestplate",
		312 => "Diamond Leggins",
		313 => "Diamond Boots",
		314 => "Gold Helmet",
		315 => "Gold Chestplate",
		316 => "Gold Leggins",
		317 => "Gold Boots",
		318 => "Flint",
		319 => "Raw Porkchop",
		320 => "Cooked Porkchop",
		321 => "Painting",
		322 => "Golden Apple",
		323 => "Sign",
		324 => "Wooden Door",
		325 => "Bucket",
		328 => "Minecart",
		330 => "Iron Door",
		331 => "Redstone",
		332 => "Snowball",
		334 => "Leather",
		336 => "Bricks",
		337 => "Clay",
		338 => "Sugarcane",
		339 => "Paper",
		340 => "Book",
		341 => "Slimeball",
		344 => "Egg",
		345 => "Compass",
		346 => "Fishing Rod",
		347 => "Clock",
		348 => "Glowstone Dust",
		349 => "Raw Fish",
		350 => "Cooked Fish",
		351 => "Dye",
		352 => "Bone",
		353 => "Sugar",
		354 => "Cake",
		355 => "Bed",
		357 => "Cookie",
		359 => "Shears",
		360 => "Melon",
		361 => "Pumpkin Seed",
		362 => "Melon Seed",
		363 => "Raw Beef",
		364 => "Steak",
		365 => "Raw Chicken",
		366 => "Cooked Chicken",
		369 => "Blaze Rod",
		371 => "Gold Nugget",
		373 => "Potion",
		377 => "Blaze powder",
		378 => "Magma Cream",
		383 => "Spawn Egg",
		386 => "Writable Book",
		387 => "Written Book",
		388 => "Emerald",
		390 => "Flower Pot",
		391 => "Carrot",
		392 => "Potato",
		393 => "Baked Potato",
		394 => "Poisonous Potato",
		395 => "Empty Map",
		396 => "Golden Carrot",
		397 => "Mob Head",
		398 => "Carrot on a Stick",
		399 => "Nether Star",
		400 => "Pumpkin Pie",
		401 => "Firework",
		402 => "Firework Star",
		403 => "Enchanted Book",
		404 => "Redstone Comparator",
		405 => "Nether Bricks",
		406 => "Quartz",
		407 => "Minecart with TNT",
		408 => "Minecart with Hopper",
		410 => "Hopper",
		411 => "Raw Rabbit",
		412 => "Cooked Rabbit",
		414 => "Rabbit's Foot",
		415 => "Rabbit Hide",
		416 => "Leather Horse Armor",
		417 => "Iron Horse Armor",
		418 => "Golden Horse Armor",
		419 => "Diamond Horse Armor",
		420 => "Lead",
		421 => "Name Tag",
		422 => "Prismarine Crystals",
		423 => "Raw Mutton",
		424 => "Cooked Mutton",
		425 => "Armor Stand",
		426 => "End Crystal",
		427 => "Spruce Door",
		428 => "Birch Door",
		429 => "Jungle Door",
		430 => "Acacia Door",
		431 => "Dark Oak Door",
		432 => "Chorus Fruit",
		437 => "Dragon's Breath",
		438 => "Splash Potion",
		444 => "Elytra",
		446 => "Banner",
		457 => "Beetroot",
		458 => "Beetroot Seed",
		459 => "Beetroot Soup",
		460 => "Raw Salmon",
		461 => "Clownfish",
		462 => "Pufferfish",
		463 => "Cooked Salmon",
		466 => "Enchanted Golden Apple",
		498 => "Camera",
	];
	
	public static $list = null;
	public static $food = null;
	
	protected $block;
	protected $id;
	protected $meta;
	
	private $tags = "";
	private $cachedNBT = null;
	
	public $count;
	
	protected $durability = 0;
	protected $name;
	protected $obtainTime = 0;

	public function canBeActivated(){
		return false;
	}

	public static function init(){
		if(Item::$list === null){
			Item::$list = new \SplFixedArray(65536);
			Item::$list[Item::SUGARCANE] = Sugarcane::class;
			Item::$list[Item::ENDER_PEARL] = EnderPearl::class;
			Item::$list[Item::EYE_OF_ENDER] = EyeOfEnder::class;
			Item::$list[Item::DRAGONS_BREATH] = DragonsBreath::class;
			Item::$list[Item::SHULKER_SHELL] = ShulkerShell::class;
			Item::$list[Item::POPPED_CHORUS_FRUIT] = PoppedChorusFruit::class;
			Item::$list[Item::WHEAT_SEEDS] = WheatSeeds::class;
			Item::$list[Item::PUMPKIN_SEEDS] = PumpkinSeeds::class;
			Item::$list[Item::MELON_SEEDS] = MelonSeeds::class;
			Item::$list[Item::MUSHROOM_STEW] = MushroomStew::class;
			Item::$list[Item::BEETROOT_SOUP] = BeetrootSoup::class;
			Item::$list[Item::CARROT] = Carrot::class;
			Item::$list[Item::POTATO] = Potato::class;
			Item::$list[Item::BEETROOT_SEEDS] = BeetrootSeeds::class;
			Item::$list[Item::SIGN] = Sign::class;
			Item::$list[Item::WOODEN_DOOR] = WoodenDoor::class;
			Item::$list[Item::SPRUCE_DOOR] = SpruceDoor::class;
			Item::$list[Item::BIRCH_DOOR] = BirchDoor::class;
			Item::$list[Item::JUNGLE_DOOR] = JungleDoor::class;
			Item::$list[Item::ACACIA_DOOR] = AcaciaDoor::class;
			Item::$list[Item::DARK_OAK_DOOR] = DarkOakDoor::class;
			Item::$list[Item::BUCKET] = Bucket::class;
			Item::$list[Item::IRON_DOOR] = IronDoor::class;
			Item::$list[Item::CAKE] = Cake::class;
			Item::$list[Item::BED] = Bed::class;
			Item::$list[Item::PAINTING] = Painting::class;
			Item::$list[Item::COAL] = Coal::class;
			Item::$list[Item::APPLE] = Apple::class;
			Item::$list[Item::WRITABLE_BOOK] = WritableBook::class;
			Item::$list[Item::WRITTEN_BOOK] = WrittenBook::class;
			Item::$list[Item::SPAWN_EGG] = SpawnEgg::class;
			Item::$list[Item::DIAMOND] = Diamond::class;
			Item::$list[Item::STICK] = Stick::class;
			Item::$list[Item::SNOWBALL] = Snowball::class;
			Item::$list[Item::EGG] = Egg::class;
			Item::$list[Item::BOWL] = Bowl::class;
			Item::$list[Item::FEATHER] = Feather::class;
			Item::$list[Item::BRICK] = Brick::class;
			Item::$list[Item::LEATHER_CAP] = LeatherCap::class;
			Item::$list[Item::LEATHER_TUNIC] = LeatherTunic::class;
			Item::$list[Item::LEATHER_PANTS] = LeatherPants::class;
			Item::$list[Item::LEATHER_BOOTS] = LeatherBoots::class;
			Item::$list[Item::CHAIN_HELMET] = ChainHelmet::class;
			Item::$list[Item::CHAIN_CHESTPLATE] = ChainChestplate::class;
			Item::$list[Item::CHAIN_LEGGINGS] = ChainLeggings::class;
			Item::$list[Item::CHAIN_BOOTS] = ChainBoots::class;
			Item::$list[Item::IRON_HELMET] = IronHelmet::class;
			Item::$list[Item::IRON_CHESTPLATE] = IronChestplate::class;
			Item::$list[Item::IRON_LEGGINGS] = IronLeggings::class;
			Item::$list[Item::IRON_BOOTS] = IronBoots::class;
			Item::$list[Item::GOLD_HELMET] = GoldHelmet::class;
			Item::$list[Item::GOLD_CHESTPLATE] = GoldChestplate::class;
			Item::$list[Item::GOLD_LEGGINGS] = GoldLeggings::class;
			Item::$list[Item::GOLD_BOOTS] = GoldBoots::class;
			Item::$list[Item::DIAMOND_HELMET] = DiamondHelmet::class;
			Item::$list[Item::DIAMOND_CHESTPLATE] = DiamondChestplate::class;
			Item::$list[Item::DIAMOND_LEGGINGS] = DiamondLeggings::class;
			Item::$list[Item::DIAMOND_BOOTS] = DiamondBoots::class;
			Item::$list[Item::IRON_SWORD] = IronSword::class;
			Item::$list[Item::IRON_INGOT] = IronIngot::class;
			Item::$list[Item::GOLD_INGOT] = GoldIngot::class;
			Item::$list[Item::IRON_SHOVEL] = IronShovel::class;
			Item::$list[Item::IRON_PICKAXE] = IronPickaxe::class;
			Item::$list[Item::IRON_AXE] = IronAxe::class;
			Item::$list[Item::IRON_HOE] = IronHoe::class;
			Item::$list[Item::DIAMOND_SWORD] = DiamondSword::class;
			Item::$list[Item::DIAMOND_SHOVEL] = DiamondShovel::class;
			Item::$list[Item::DIAMOND_PICKAXE] = DiamondPickaxe::class;
			Item::$list[Item::DIAMOND_AXE] = DiamondAxe::class;
			Item::$list[Item::DIAMOND_HOE] = DiamondHoe::class;
			Item::$list[Item::GOLD_SWORD] = GoldSword::class;
			Item::$list[Item::GOLD_SHOVEL] = GoldShovel::class;
			Item::$list[Item::GOLD_PICKAXE] = GoldPickaxe::class;
			Item::$list[Item::GOLD_AXE] = GoldAxe::class;
			Item::$list[Item::GOLD_HOE] = GoldHoe::class;
			Item::$list[Item::STONE_SWORD] = StoneSword::class;
			Item::$list[Item::STONE_SHOVEL] = StoneShovel::class;
			Item::$list[Item::STONE_PICKAXE] = StonePickaxe::class;
			Item::$list[Item::STONE_AXE] = StoneAxe::class;
			Item::$list[Item::STONE_HOE] = StoneHoe::class;
			Item::$list[Item::WOODEN_SWORD] = WoodenSword::class;
			Item::$list[Item::WOODEN_SHOVEL] = WoodenShovel::class;
			Item::$list[Item::WOODEN_PICKAXE] = WoodenPickaxe::class;
			Item::$list[Item::WOODEN_AXE] = WoodenAxe::class;
			Item::$list[Item::WOODEN_HOE] = WoodenHoe::class;
			Item::$list[Item::FLINT_STEEL] = FlintSteel::class;
			Item::$list[Item::SHEARS] = Shears::class;
			Item::$list[Item::BOW] = Bow::class;
			Item::$list[Item::RAW_FISH] = Fish::class;
			Item::$list[Item::COOKED_FISH] = CookedFish::class;
			Item::$list[Item::EGG] = Egg::class;
			Item::$list[Item::MOB_HEAD] = MobHead::class;
			Item::$list[Item::GUNPOWDER] = Gunpowder::class;
			Item::$list[Item::BLAZE_POWDER] = BlazePowder::class;
			Item::$list[Item::NETHER_STAR] = NetherStar::class;
			Item::$list[Item::FLOWER_POT] = FlowerPot::class;
			Item::$list[Item::ITEM_FRAME] = ItemFrame::class;
			Item::$list[Item::ELYTRA] = Elytra::class;
			Item::$list[Item::PRISMARINE_CRYSTAL] = PrismarineCrystal::class;
			Item::$list[Item::CHORUS_FRUIT] = ChorusFruit::class;
			Item::$list[Item::NETHER_QUARTZ] = NetherQuartz::class;
			Item::$list[Item::POTION] = Potion::class;
			Item::$list[Item::GLASS_BOTTLE] = GlassBottle::class;
			Item::$list[Item::SPLASH_POTION] = SplashPotion::class;
			Item::$list[Item::ENCHANTING_BOTTLE] = EnchantingBottle::class;
			Item::$list[Item::BOAT] = Boat::class;
			Item::$list[Item::MINECART] = Minecart::class;
			Item::$list[Item::MUSIC_DISC_13] = MusicDisc13::class;
			Item::$list[Item::MUSIC_DISC_CAT] = MusicDiscCat::class;
			Item::$list[Item::MUSIC_DISC_BLOCKS] = MusicDiscBlocks::class;
			Item::$list[Item::MUSIC_DISC_STRAD] = MusicDiscStrad::class;
			Item::$list[Item::MUSIC_DISC_WARD] = MusicDiscWard::class;
			Item::$list[Item::MUSIC_DISC_STAL] = MusicDiscStal::class;
			Item::$list[Item::MUSIC_DISC_WAIT] = MusicDiscWait::class;
			Item::$list[Item::MUSIC_DISC_MALL] = MusicDiscMall::class;
			Item::$list[Item::MUSIC_DISC_CHIRP] = MusicDiscChirp::class;
			Item::$list[Item::MUSIC_DISC_MELLOHI] = MusicDiscMellohi::class;
			Item::$list[Item::MUSIC_DISC_FAR] = MusicDiscFar::class;
			Item::$list[Item::ARMOR_STAND] = ArmorStand::class;
			
			for($i = 0; $i < 256; ++$i){
				if(Block::$list[$i] !== null){
					Item::$list[$i] = Block::$list[$i];
				}
			}
		}

		Item::initCreativeItems();
		Item::initFoods();
	}

	private static $creative = [];
	
	private static function initCreativeItems(){
		Item::clearCreativeItems();
		$creativeItems = new Config(Server::getInstance()->getFilePath() . "src/darksystem/resources/creativeitems.json", Config::JSON, []);
		foreach($creativeItems->getAll() as $data){
			$item = Item::get($data["id"], $data["damage"], $data["count"], $data["nbt"]);
			Item::addCreativeItem($item);
		}
	}
	
	private static function initFoods(){
		Item::$food[] = Item::COOKIE;
		Item::$food[] = Item::MELON;
		Item::$food[] = Item::RAW_BEEF;
		Item::$food[] = Item::COOKED_BEEF;
		Item::$food[] = Item::RAW_CHICKEN;
		Item::$food[] = Item::COOKED_CHICKEN;
		Item::$food[] = Item::CARROT;
		Item::$food[] = Item::POTATO;
		Item::$food[] = Item::BAKED_POTATO;
		Item::$food[] = Item::PUMPKIN_PIE;
		Item::$food[] = Item::BREAD;
		Item::$food[] = Item::APPLE;
		Item::$food[] = Item::GOLDEN_APPLE;
		Item::$food[] = Item::RAW_FISH;
		Item::$food[] = Item::COOKED_FISH;
		Item::$food[] = Item::RAW_PORKCHOP;
		Item::$food[] = Item::COOKED_PORKCHOP;
		Item::$food[] = Item::RAW_MUTTON;
		Item::$food[] = Item::COOKED_MUTTON;
		Item::$food[] = Item::RAW_RABBIT;
		Item::$food[] = Item::COOKED_RABBIT;
		Item::$food[] = Item::RAW_SALMON;
		Item::$food[] = Item::COOKED_SALMON;
		Item::$food[] = Item::RABBIT_STEW;
		Item::$food[] = Item::CHORUS_FRUIT;
	}

	public static function clearCreativeItems(){
		Item::$creative = [];
	}

	public static function getCreativeItems(){
		return Item::$creative;
	}

	public static function addCreativeItem(Item $item){
		Item::$creative[] = Item::get($item->getId(), $item->getDamage());
	}

	public static function removeCreativeItem(Item $item){
		$index = Item::getCreativeItemIndex($item);
		if($index !== -1){
			unset(Item::$creative[$index]);
		}
	}

	public static function isCreativeItem(Item $item){
		foreach(Item::$creative as $i => $d){
			if($item->equals($d, !$item->isTool())){
				return true;
			}
		}

		return false;
	}

	/**
	 * @param $index
	 * @return Item
	 */
	public static function getCreativeItem($index){
		return isset(Item::$creative[$index]) ? Item::$creative[$index] : null;
	}

	/**
	 * @param Item $item
	 * @return int
	 */
	public static function getCreativeItemIndex(Item $item){
		foreach(Item::$creative as $i => $d){
			if($item->equals($d, !$item->isTool())){
				return $i;
			}
		}

		return -1;
	}

	public static function get($id, $meta = 0, $count = 1, $tags = ""){
		try{
			$class = Item::$list[$id];
			if($class === null){
				return (new Item($id, $meta, $count))->setCompoundTag($tags);
			}elseif($id < 256){
				return (new ItemBlock(new $class($meta), $meta, $count))->setCompoundTag($tags);
			}else{
				return (new $class($meta, $count))->setCompoundTag($tags);
			}
		}catch(\RuntimeException $e){
			return (new Item($id, $meta, $count))->setCompoundTag($tags);
		}
	}

	public static function fromString($str, $multiple = false){
		if($multiple === true){
			$blocks = [];
			foreach(explode(",", $str) as $b){
				$blocks[] = Item::fromString($b, false);
			}

			return $blocks;
		}else{
			$b = explode(":", str_replace([" ", "minecraft:"], ["_", ""], trim($str)));
			if(!isset($b[1])){
				$meta = 0;
			}else{
				$meta = $b[1] & 0x7FFF;
			}

			if(defined(Item::class . "::" . strtoupper($b[0]))){
				$item = Item::get(constant(Item::class . "::" . strtoupper($b[0])), $meta);
				if($item->getId() === Item::AIR && strtoupper($b[0]) !== "AIR"){
					$item = Item::get($b[0] & 0xFFFF, $meta);
				}
			}else{
				$item = Item::get($b[0] & 0xFFFF, $meta);
			}

			return $item;
		}
	}

	public function __construct($id, $meta = 0, $count = 1, $name = "Bilinmeyen", $obtainTime = null){
		$this->id = $id & 0xffff;
		$this->meta = $meta !== null ? $meta & 0x7fff : null;
		$this->count = (int) $count;
		$this->name = $name;
		if($obtainTime == null){
			$obtainTime = time();
		}
		if(!isset($this->block) && $this->id <= 0xff && isset(Block::$list[$this->id])){
			$this->block = Block::get($this->id, $this->meta);
			$this->name = $this->block->getName();
		}
		if($this->name == "Bilinmeyen" && isset(Item::$names[$this->id])){
			$this->name = Item::$names[$this->id];
		}
	}

	public function setCompoundTag($tags){
		if($tags instanceof CompoundTag){
			$this->setNamedTag($tags);
		}else{
			$this->tags = $tags;
			$this->cachedNBT = null;
		}

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCompoundTag(){
		return $this->tags;
	}
	
	public function hasCompoundTag(){
		return $this->tags !== "" && $this->tags !== null;
	}

	public function hasCustomBlockData(){
		if(!$this->hasCompoundTag()){
			return false;
		}

		$tag = $this->getNamedTag();
		if(isset($tag->BlockEntityTag) && $tag->BlockEntityTag instanceof CompoundTag){
			return true;
		}

		return false;
	}

	public function clearCustomBlockData(){
		if(!$this->hasCompoundTag()){
			return $this;
		}
		
		$tag = $this->getNamedTag();

		if(isset($tag->BlockEntityTag) && $tag->BlockEntityTag instanceof CompoundTag){
			unset($tag->display->BlockEntityTag);
			$this->setNamedTag($tag);
		}

		return $this;
	}

	public function setCustomBlockData(CompoundTag $CompoundTag){
		$tags = clone $CompoundTag;
		$tags->setName("BlockEntityTag");

		if(!$this->hasCompoundTag()){
			$tag = new CompoundTag("", []);
		}else{
			$tag = $this->getNamedTag();
		}

		$tag->BlockEntityTag = $tags;
		$this->setNamedTag($tag);

		return $this;
	}

	public function getCustomBlockData(){
		if(!$this->hasCompoundTag()){
			return null;
		}

		$tag = $this->getNamedTag();
		if(isset($tag->BlockEntityTag) && $tag->BlockEntityTag instanceof CompoundTag){
			return $tag->BlockEntityTag;
		}

		return null;
	}

	public function hasEnchantments(){
		if(!$this->hasCompoundTag()){
			return false;
		}

		$tag = $this->getNamedTag();
		if(isset($tag->ench)){
			$tag = $tag->ench;
			if($tag instanceof ListTag){
				return true;
			}
		}

		return false;
	}
	
	/**
	 * @param $id
	 * @return Enchantment|null
	 */
	public function getEnchantment($id){
		if(!$this->hasEnchantments()){
			return null;
		}

		foreach($this->getNamedTag()->ench as $entry){
			if($entry["id"] === $id){
				$e = Enchantment::getEnchantment($entry["id"]);
				$e->setLevel($entry["lvl"]);
				return $e;
			}
		}

		return null;
	}

	/**
	 * @param Enchantment $ench
	 */
	public function addEnchantment(Enchantment $ench){
		if(!$this->hasCompoundTag()){
			$tag = new CompoundTag("", []);
		}else{
			$tag = $this->getNamedTag();
		}

		if(!isset($tag->ench)){
			$tag->ench = new ListTag("ench", []);
			$tag->ench->setTagType(NBT::TAG_Compound);
		}

		$found = false;
		$maxIntIndex = -1;
		foreach($tag->ench as $k => $entry){
			if(is_numeric($k) && $k > $maxIntIndex){
				$maxIntIndex = $k;
			}
			
			if($entry["id"] === $ench->getId()){
				$tag->ench->{$k} = new CompoundTag("", [
					"id" => new ShortTag("id", $ench->getId()),
					"lvl" => new ShortTag("lvl", $ench->getLevel())
				]);
				$found = true;
				break;
			}
		}

		if(!$found){
			$tag->ench->{$maxIntIndex + 1} = new CompoundTag("", [
				"id" => new ShortTag("id", $ench->getId()),
				"lvl" => new ShortTag("lvl", $ench->getLevel())
			]);
		}

		$this->setNamedTag($tag);
	}

	/**
	 * @return Enchantment[]
	 */
	public function getEnchantments(){
		if(!$this->hasEnchantments()){
			return [];
		}

		$enchantments = [];
		
		foreach($this->getNamedTag()->ench as $entry){
			$e = Enchantment::getEnchantment($entry["id"]);
			$e->setLevel($entry["lvl"]);
			$enchantments[$e->getId()] = $e;
		}

		return $enchantments;
	}

	public function hasCustomName(){
		if(!$this->hasCompoundTag()){
			return false;
		}

		$tag = $this->getNamedTag();
		if(isset($tag->display)){
			$tag = $tag->display;
			if($tag instanceof CompoundTag && isset($tag->Name) && $tag->Name instanceof StringTag){
				return true;
			}
		}

		return false;
	}

	public function getCustomName(){
		if(!$this->hasCompoundTag()){
			return "";
		}

		$tag = $this->getNamedTag();
		if(isset($tag->display)){
			$tag = $tag->display;
			if($tag instanceof CompoundTag && isset($tag->Name) && $tag->Name instanceof StringTag){
				return $tag->Name->getValue();
			}
		}

		return "";
	}

	public function setCustomName($name){		
		if((string) $name === ""){
			$this->clearCustomName();
		}

		if(!$this->hasCompoundTag()){
			$tag = new CompoundTag("", []);
		}else{
			$tag = $this->getNamedTag();
		}

		if(isset($tag->display) && $tag->display instanceof CompoundTag){
			$tag->display->Name = new StringTag("Name", $name);
		}else{
			$tag->display = new CompoundTag("display", [
				"Name" => new StringTag("Name", $name)
			]);
		}
		
		$this->setCompoundTag($tag);

		return $this;
	}
	
	public function setCustomColor($colorCode){	
		if(!$this->hasCompoundTag()){
			if(!is_int($colorCode)){
				return $this;
			}
			
			$tag = new CompoundTag("", []);
		}else{
			$tag = $this->getNamedTag();
		}
		
		if(!is_int($colorCode)){
			unset($tag->customColor);
		}else{
			$tag->customColor = new IntTag("customColor", $colorCode);
		}
		
		$this->setCompoundTag($tag);

		return $this;
	}

	public function clearCustomName(){
		if(!$this->hasCompoundTag()){
			return $this;
		}
		
		$tag = $this->getNamedTag();

		if(isset($tag->display) && $tag->display instanceof CompoundTag){
			unset($tag->display->Name);
			if($tag->display->getCount() === 0){
				unset($tag->display);
			}

			$this->setNamedTag($tag);
		}

		return $this;
	}

	public function getNamedTagEntry($name){
		$tag = $this->getNamedTag();
		if($tag !== null){
			return isset($tag->{$name}) ? $tag->{$name} : null;
		}

		return null;
	}

	public function getNamedTag(){
		if(!$this->hasCompoundTag()){
			return null;
		}elseif($this->cachedNBT !== null){
			return $this->cachedNBT;
		}
		
		return $this->cachedNBT = Item::parseCompoundTag($this->tags);
	}

	public function setNamedTag(CompoundTag $tag){
		if($tag->getCount() === 0){
			return $this->clearNamedTag();
		}

		$this->cachedNBT = $tag;
		$this->tags = Item::writeCompoundTag($tag);

		return $this;
	}

	public function clearNamedTag(){
		return $this->setCompoundTag("");
	}

	public function getCount(){
		return $this->count;
	}

	public function setCount($count){
		$this->count = (int) $count;
	}

	final public function getName(){
		return $this->hasCustomName() ? $this->getCustomName() : $this->name;
	}

	final public function canBePlaced(){
		return $this->block !== null && $this->block->canBePlaced();
	}
	
	final public function isPlaceable(){
		return (($this->block instanceof Block) && $this->block->isPlaceable === true);
	}
	
	public function getBlock(){
		if($this->block instanceof Block){
			return clone $this->block;
		}else{
			return Block::get(Item::AIR);
		}
	}

	final public function getId(){
		return $this->id;
	}

	final public function getDamage(){
		return $this->meta;
	}

	public function setDamage($meta){
		$this->meta = $meta !== null ? $meta & 0x7FFF : null;
	}

	public function getMaxStackSize(){
		return Item::MAX_STACK;
	}

	final public function getFuelTime(){
		if(!isset(Fuel::$duration[$this->id])){
			return null;
		}
		
		if($this->id !== Item::BUCKET || $this->meta === 10){
			return Fuel::$duration[$this->id];
		}

		return null;
	}

	/**
	 * @param Entity|Block $object
	 *
	 * @return bool
	 */
	public function useOn($object){
		return false;
	}

	/**
	 * @return bool
	 */
	public function isTool(){
		return false;
	}

	/**
	 * @return int|bool
	 */
	public function getMaxDurability(){
		return false;
	}

	public function isPickaxe(){
		return false;
	}

	public function isAxe(){
		return false;
	}

	public function isSword(){
		return false;
	}

	public function isShovel(){
		return false;
	}

	public function isHoe(){
		return false;
	}

	public function isShears(){
		return false;
	}
	
	public function isBow(){
		return false;
	}
	
	final public function __toString(){
		return "Item " . $this->name . " (" . $this->id . ":" . ($this->meta === null ? "?" : $this->meta) . ")x" . $this->count . ($this->hasCompoundTag() ? " tags:0x".bin2hex($this->getCompoundTag()) : "");
	}

	public function getDestroySpeed(Block $block, Player $player){
		return 1;
	}

	public function onActivate(Level $level, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		return false;
	}

	public final function equals(Item $item, $checkDamage = true, $checkCompoundTag = true){
		return $this->id === $item->getId() && ($checkDamage === false || $this->getDamage() === $item->getDamage()) && ($checkCompoundTag === false || $this->getCompoundTag() === $item->getCompoundTag());
	}

	public final function deepEquals(Item $item, $checkDamage = true, $checkCompoundTag = true){
		if($this->equals($item, $checkDamage, $checkCompoundTag)){
			return true;
		}elseif($item->hasCompoundTag() && $this->hasCompoundTag()){
			return NBT::matchTree($this->getNamedTag(), $item->getNamedTag());
		}

		return false;
	}
	
	public function isFood(){
		return in_array($this->id, Item::$food);
	}
	
	public function setObtainTime($time){
		$this->obtainTime = $time;
	}
	
	public function getObtainTime(){
		return $this->obtainTime;
	}
	
	/**
	 * @param int    $slot optional, the inventory slot of the item
	 * @param string $tagName the name to assign to the CompoundTag object
	 *
	 * @return CompoundTag
	 */
	public function nbtSerialize($slot = -1, $tagName = ""){
		$tag = new CompoundTag($tagName, [
			"id" => new ShortTag("id", $this->id),
			"Count" => new ByteTag("Count", $this->count ?? -1),
			"Damage" => new ShortTag("Damage", $this->meta),
		]);

		if($this->hasCompoundTag()){
			$tag->tag = clone $this->getNamedTag();
			//$tag->tag->setName("tag");
		}
		
		if($slot !== -1){
			$tag->Slot = new ByteTag("Slot", $slot);
		}

		return $tag;
	}

	/**
	 * @param CompoundTag $tag
	 *
	 * @return Item
	 */
	public static function nbtDeserialize(CompoundTag $tag){
		if(!isset($tag->id) || !isset($tag->Count)){
			return Item::get(0);
		}

		if($tag->id instanceof ShortTag){
			$item = Item::get($tag->id->getValue(), !isset($tag->Damage) ? 0 : $tag->Damage->getValue(), $tag->Count->getValue());
		}elseif($tag->id instanceof StringTag){
			$item = Item::fromString($tag->id->getValue());
			$item->setDamage(!isset($tag->Damage) ? 0 : $tag->Damage->getValue());
			$item->setCount($tag->Count->getValue());
		}else{
			throw new \InvalidArgumentException("Item CompoundTag ID must be an instance of StringTag or ShortTag, " . get_class($tag->id) . " given");
		}

		if(isset($tag->tag) && $tag->tag instanceof CompoundTag){
			$item->setNamedTag($tag->tag);
		}

		return $item;
	}
	
}
