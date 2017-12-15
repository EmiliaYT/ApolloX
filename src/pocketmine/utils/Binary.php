<?php

#______           _    _____           _                  
#|  _  \         | |  /  ___|         | |                 
#| | | |__ _ _ __| | _\ `--. _   _ ___| |_ ___ _ __ ___   
#| | | / _` | '__| |/ /`--. \ | | / __| __/ _ \ '_ ` _ \  
#| |/ / (_| | |  |   </\__/ / |_| \__ \ ||  __/ | | | | | 
#|___/ \__,_|_|  |_|\_\____/ \__, |___/\__\___|_| |_| |_| 
#                             __/ |                       
#                            |___/

namespace pocketmine\utils;

use pocketmine\entity\Entity;
use pocketmine\utils\MetadataConvertor;

class Binary{
	
	const BIG_ENDIAN = 0x00;
	const LITTLE_ENDIAN = 0x01;

	private static function checkLength($str, $expect){
		
	}

	/**
	 * @param $str
	 *
	 * @return mixed
	 */
	public static function readTriad($str){
		Binary::checkLength($str, 3);
		return unpack("N", "\x00" . $str)[1];
	}

	/**
	 * @param $value
	 *
	 * @return string
	 */
	public static function writeTriad($value){
		return substr(pack("N", $value), 1);
	}

	/**
	 * @param $str
	 *
	 * @return mixed
	 */
	public static function readLTriad($str){
		Binary::checkLength($str, 3);
		return unpack("V", $str . "\x00")[1];
	}

	/**
	 * @param $value
	 *
	 * @return string
	 */
	public static function writeLTriad($value){
		return substr(pack("V", $value), 0, -1);
	}

	/**
	 * @param array $data
	 *
	 * @return string
	 */
	public static function writeMetadata(array $data, $playerProtocol){
		$data = MetadataConvertor::updateMeta($data, $playerProtocol);
		$m = "";
		$m .= Binary::writeVarInt(count($data));
		foreach($data as $bottom => $d){
			$m .= Binary::writeVarInt($bottom);
			$m .= Binary::writeVarInt($d[0]);
			switch($d[0]){
				case Entity::DATA_TYPE_BYTE:
					$m .= Binary::writeByte($d[1]);
					break;
				case Entity::DATA_TYPE_SHORT:
					$m .= Binary::writeLShort($d[1]);
					break;
				case Entity::DATA_TYPE_INT:					
					$m .= Binary::writeSignedVarInt($d[1]);
					break;
				case Entity::DATA_TYPE_FLOAT:
					$m .= Binary::writeLFloat($d[1]);
					break;
				case Entity::DATA_TYPE_STRING:
					$m .= Binary::writeVarInt(strlen($d[1])) . $d[1];
					break;
				case Entity::DATA_TYPE_SLOT:
					$m .= "\x7f";
					break;
				case Entity::DATA_TYPE_POS:
					$m .= Binary::writeSignedVarInt($d[1][0]);
					$m .= Binary::writeSignedVarInt($d[1][1]);
					$m .= Binary::writeSignedVarInt($d[1][2]);
					break;
				case Entity::DATA_TYPE_LONG:
					$m .= Binary::writeSignedVarInt($d[1]);
					break;
				case Entity::DATA_TYPE_VECTOR3:
					$m .= Binary::writeLFloat($d[1][0]);
					$m .= Binary::writeLFloat($d[1][1]);
					$m .= Binary::writeLFloat($d[1][2]);
					break;
			}
		}
		
		return $m;
	}
	
	/**
	 * @param $b
	 *
	 * @return bool
	 */
	public static function readBool($b){
		return Binary::readByte($b, false) === 0 ? false : true;
	}

	/**
	 * @param $b
	 *
	 * @return bool|string
	 */
	public static function writeBool($b){
		return Binary::writeByte($b === true ? 1 : 0);
	}

	/**
	 * @param string $c
	 * @param bool   $signed
	 *
	 * @return int
	 */
	public static function readByte($c, $signed = true){
		Binary::checkLength($c, 1);
		$b = ord($c{0});
		if($signed){
			if(PHP_INT_SIZE === 8){
				return $b << 56 >> 56;
			}else{
				return $b << 24 >> 24;
			}
		}else{
			return $b;
		}
	}

	/**
	 * @param $c
	 *
	 * @return string
	 */
	public static function writeByte($c){
		return chr($c);
	}

	/**
	 * @param $str
	 *
	 * @return int
	 */
	public static function readShort($str){
		Binary::checkLength($str, 2);
		return unpack("n", $str)[1];
	}

	/**
	 * @param $str
	 *
	 * @return int
	 */
	public static function readSignedShort($str){
		Binary::checkLength($str, 2);
		if(PHP_INT_SIZE === 8){
			return @unpack("n", $str)[1] << 48 >> 48;
		}else{
			return unpack("n", $str)[1] << 16 >> 16;
		}
	}

	/**
	 * @param $value
	 *
	 * @return string
	 */
	public static function writeShort($value){
		return pack("n", $value);
	}

	/**
	 * @param      $str
	 *
	 * @return int
	 */
	public static function readLShort($str){
		Binary::checkLength($str, 2);
		return unpack("v", $str)[1];
	}

	/**
	 * @param      $str
	 *
	 * @return int
	 */
	public static function readSignedLShort($str){
		Binary::checkLength($str, 2);
		if(PHP_INT_SIZE === 8){
			return unpack("v", $str)[1] << 48 >> 48;
		}else{
			return unpack("v", $str)[1] << 16 >> 16;
		}
	}

	/**
	 * @param $value
	 *
	 * @return string
	 */
	public static function writeLShort($value){
		return pack("v", $value);
	}

	public static function readInt($str){
		Binary::checkLength($str, 4);
		if(PHP_INT_SIZE === 8){
			return unpack("N", $str)[1] << 32 >> 32;
		}else{
			return unpack("N", $str)[1];
		}
	}

	public static function writeInt($value){
		return pack("N", $value);
	}

	public static function readLInt($str){
		Binary::checkLength($str, 4);
		if(PHP_INT_SIZE === 8){
			return unpack("V", $str)[1] << 32 >> 32;
		}else{
			return unpack("V", $str)[1];
		}
	}

	public static function writeLInt($value){
		return pack("V", $value);
	}

	public static function readFloat($str){
		Binary::checkLength($str, 4);
		return ENDIANNESS === Binary::BIG_ENDIAN ? unpack("f", $str)[1] : unpack("f", strrev($str))[1];
	}

	public static function writeFloat($value){
		return ENDIANNESS === Binary::BIG_ENDIAN ? pack("f", $value) : strrev(pack("f", $value));
	}

	public static function readLFloat($str){
		Binary::checkLength($str, 4);
		return ENDIANNESS === Binary::BIG_ENDIAN ? unpack("f", strrev($str))[1] : unpack("f", $str)[1];
	}

	public static function writeLFloat($value){
		return ENDIANNESS === Binary::BIG_ENDIAN ? strrev(pack("f", $value)) : pack("f", $value);
	}

	public static function printFloat($value){
		return preg_replace("/(\\.\\d+?)0+$/", "$1", sprintf("%F", $value));
	}

	public static function readDouble($str){
		Binary::checkLength($str, 8);
		return ENDIANNESS === Binary::BIG_ENDIAN ? unpack("d", $str)[1] : unpack("d", strrev($str))[1];
	}

	public static function writeDouble($value){
		return ENDIANNESS === Binary::BIG_ENDIAN ? pack("d", $value) : strrev(pack("d", $value));
	}

	public static function readLDouble($str){
		Binary::checkLength($str, 8);
		return ENDIANNESS === Binary::BIG_ENDIAN ? unpack("d", strrev($str))[1] : unpack("d", $str)[1];
	}

	public static function writeLDouble($value){
		return ENDIANNESS === Binary::BIG_ENDIAN ? strrev(pack("d", $value)) : pack("d", $value);
	}

	public static function readLong($x){
		Binary::checkLength($x, 8);
		if(PHP_INT_SIZE === 8){
			$int = @unpack("N*", $x);
			return ($int[1] << 32) | $int[2];
		}else{
			$value = "0";
			for($i = 0; $i < 8; $i += 2){
				$value = bcmul($value, "65536", 0);
				$value = bcadd($value, Binary::readShort(substr($x, $i, 2)), 0);
			}

			if(bccomp($value, "9223372036854775807") == 1){
				$value = bcadd($value, "-18446744073709551616");
			}

			return $value;
		}
	}

	public static function writeLong($value){
		if(PHP_INT_SIZE === 8){
			return pack("NN", $value >> 32, $value & 0xFFFFFFFF);
		}else{
			$x = "";

			if(bccomp($value, "0") == -1){
				$value = bcadd($value, "18446744073709551616");
			}

			$x .= Binary::writeShort(bcmod(bcdiv($value, "281474976710656"), "65536"));
			$x .= Binary::writeShort(bcmod(bcdiv($value, "4294967296"), "65536"));
			$x .= Binary::writeShort(bcmod(bcdiv($value, "65536"), "65536"));
			$x .= Binary::writeShort(bcmod($value, "65536"));

			return $x;
		}
	}

	public static function readLLong($str){
		return Binary::readLong(strrev($str));
	}

	public static function writeLLong($value){
		return strrev(Binary::writeLong($value));
	}
	
	public static function writeSignedVarInt($v){
		if($v >= 0){
			$v = 2 * $v;
		}else{
			$v = 2 * abs($v) - 1;
		}
		
		return Binary::writeVarInt($v);
	}

	
	public static function writeVarInt($v){		
		if($v < 0x80){
			return chr($v);
		}else{
			$values = [];
			while($v > 0){
				$values[] = 0x80 | ($v & 0x7f);
				$v = $v >> 7;
			}
			
			$values[count($values) - 1] &= 0x7f;
			$bytes = call_user_func_array('pack', array_merge(array('C*'), $values));
			return $bytes;
		}
	}
}
