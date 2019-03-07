<?php
error_reporting(E_ALL^E_NOTICE);

require_once __DIR__."/../ActPHP/ActServer.php";
use ActPHP\ActServer;

$act = new ActServer("0.0.0.0:82","http");

$act->conn = function($server,$fd){
	echo "连接成功\n";
};

$act->recv = function($server,$fd,$data){
	$response_str = "你好 ，http jhfhgf";
	$server->send($fd,$response_str);
	$server->close($fd);
};

$act->run();
