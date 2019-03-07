<?php

//操作系统检查
if(stristr(PHP_OS, 'LINUX') === false){
	exit("Only support Linux");
}

//运行模式检查
if(strtoupper(php_sapi_name()) != "CLI"){
	exit("Only support Cli mode");
}

//php模块检查
if(!extension_loaded("curl")){
	exit("curl extension is required.");
}

if(!extension_loaded("openssl ")){
	exit("openssl  extension is required.");
}

if(!extension_loaded("pcntl")){
	exit("pcntl  extension is required.");
}

if(!extension_loaded("event")){
	exit("event extension is required.");
}





