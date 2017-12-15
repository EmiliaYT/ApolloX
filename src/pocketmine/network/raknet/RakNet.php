<?php

#______           _    _____           _                  
#|  _  \         | |  /  ___|         | |                 
#| | | |__ _ _ __| | _\ `--. _   _ ___| |_ ___ _ __ ___   
#| | | / _` | '__| |/ /`--. \ | | / __| __/ _ \ '_ ` _ \  
#| |/ / (_| | |  |   </\__/ / |_| \__ \ ||  __/ | | | | | 
#|___/ \__,_|_|  |_|\_\____/ \__, |___/\__\___|_| |_| |_| 
#                             __/ |                       
#                            |___/

namespace pocketmine\network\raknet;

$errors = 0;

if(version_compare("7.0", PHP_VERSION) > 0){
    echo "[CRITICAL] Use PHP >= 7.0" . PHP_EOL;
    ++$errors;
}

if(!extension_loaded("sockets")){
    echo "[CRITICAL] Unable to find the Socket extension." . PHP_EOL;
    ++$errors;
}

if(!extension_loaded("pthreads")){
    echo "[CRITICAL] Unable to find the pthreads extension." . PHP_EOL;
    ++$errors;
}else{
    $pthreads_version = phpversion("pthreads");
    if(substr_count($pthreads_version, ".") < 2){
        $pthreads_version = "0.$pthreads_version";
    }

    if(version_compare($pthreads_version, "3.0.0") < 0){
        echo "[CRITICAL] pthreads >= 3.0.0 is required, while you have $pthreads_version.";
        ++$errors;
    }
}

if($errors > 0){
    exit(1);
}

unset($errors);

abstract class RakNet{
	
    const VERSION = "2.1.0";
    const PROTOCOL = 6;
    const MAGIC = "\x00\xff\xff\x00\xfe\xfe\xfe\xfe\xfd\xfd\xfd\xfd\x12\x34\x56\x78";

    const PRIORITY_NORMAL = 0;
    const PRIORITY_IMMEDIATE = 1;

    const FLAG_NEED_ZLIB = 0b00000001;
    
    const PACKET_ENCAPSULATED = 0x01;
    
    const PACKET_OPEN_SESSION = 0x02;
    
    const PACKET_CLOSE_SESSION = 0x03;
    
    const PACKET_INVALID_SESSION = 0x04;
    
    const PACKET_SEND_QUEUE = 0x05;
    
    const PACKET_ACK_NOTIFICATION = 0x06;
    
    const PACKET_SET_OPTION = 0x07;
    
    const PACKET_RAW = 0x08;
    
    const PACKET_BLOCK_ADDRESS = 0x09;
	
	const PACKET_PING = 0x0a;
	
    const PACKET_SHUTDOWN = 0x7e;
    
    const PACKET_EMERGENCY_SHUTDOWN = 0x7f;
    
    function unlink(){
    	return true;
    }
    
    public static function bootstrap(\ClassLoader $loader){
        $loader->addPath(dirname(__FILE__) . DIRECTORY_SEPARATOR . "..");
    }
    
}
